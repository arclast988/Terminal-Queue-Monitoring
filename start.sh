#!/bin/bash

set -Eeuo pipefail

echo "=========================================="
echo "  Jeepney nVans - Railway Production"
echo "=========================================="

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PORT="${PORT:-8080}"
WEBSOCKET_PORT="${WEBSOCKET_PORT:-8081}"
WEBSOCKET_BROADCAST_PORT="${WEBSOCKET_BROADCAST_PORT:-8082}"

validate_port() {
    local name="$1"
    local value="${!name:-}"
    if ! [[ "$value" =~ ^[0-9]+$ ]] || [ "$value" -lt 1 ] || [ "$value" -gt 65535 ]; then
        echo "[FATAL] $name must be a number between 1 and 65535."
        exit 1
    fi
}

validate_port PORT
validate_port WEBSOCKET_PORT
validate_port WEBSOCKET_BROADCAST_PORT

require_env() {
    local name="$1"
    if [ -z "${!name:-}" ]; then
        echo "[FATAL] Required Railway variable $name is not configured."
        exit 1
    fi
}

escape_dotenv() {
    local value="$1"
    value="${value//$'\r'/}"
    value="${value//$'\n'/}"
    value="${value//\\/\\\\}"
    value="${value//\"/\\\"}"
    printf '%s' "$value"
}

require_env DATABASE_URL
require_env ENCRYPTION_KEY

# Parse Railway's PostgreSQL URL. Railway-generated credentials do not contain
# unescaped URL delimiters; keeping the parsed fields lets CodeIgniter use its
# normal Postgre driver configuration.
DB_CONN="${DATABASE_URL#*://}"
DB_USER="${DB_CONN%%:*}"
DB_CONN="${DB_CONN#*:}"
DB_PASS="${DB_CONN%%@*}"
DB_CONN="${DB_CONN#*@}"
DB_HOST="${DB_CONN%%:*}"
DB_CONN="${DB_CONN#*:}"
DB_PORT="${DB_CONN%%/*}"
DB_NAME="${DB_CONN#*/}"
DB_NAME="${DB_NAME%%\?*}"

if [ -z "$DB_HOST" ] || [ -z "$DB_PORT" ] || [ -z "$DB_NAME" ]; then
    echo "[FATAL] DATABASE_URL could not be parsed."
    exit 1
fi

APP_BASE_URL="${APP_BASE_URL:-}"
if [ -z "$APP_BASE_URL" ] && [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    APP_BASE_URL="https://${RAILWAY_PUBLIC_DOMAIN}/"
fi
if [ -n "$APP_BASE_URL" ] && [[ "$APP_BASE_URL" != */ ]]; then
    APP_BASE_URL="${APP_BASE_URL}/"
fi

umask 077
cat > "$APP_ROOT/.env" <<ENVFILE
CI_ENVIRONMENT = ${CI_ENVIRONMENT:-production}

app.baseURL = "$(escape_dotenv "$APP_BASE_URL")"
encryption.key = "$(escape_dotenv "$ENCRYPTION_KEY")"

database.default.hostname = "$(escape_dotenv "$DB_HOST")"
database.default.database = "$(escape_dotenv "$DB_NAME")"
database.default.username = "$(escape_dotenv "$DB_USER")"
database.default.password = "$(escape_dotenv "$DB_PASS")"
database.default.DBDriver = Postgre
database.default.DBPrefix =
database.default.port = "$(escape_dotenv "$DB_PORT")"

email.fromEmail = "$(escape_dotenv "${EMAIL_FROM:-}")"
email.fromName = "$(escape_dotenv "${EMAIL_FROM_NAME:-Jeepney nVans}")"
email.recipients = "$(escape_dotenv "${EMAIL_RECIPIENTS:-}")"
email.protocol = "$(escape_dotenv "${EMAIL_PROTOCOL:-smtp}")"
email.SMTPHost = "$(escape_dotenv "${EMAIL_SMTP_HOST:-smtp.gmail.com}")"
email.SMTPUser = "$(escape_dotenv "${EMAIL_SMTP_USER:-}")"
email.SMTPPass = "$(escape_dotenv "${EMAIL_SMTP_PASS:-}")"
email.SMTPPort = "$(escape_dotenv "${EMAIL_SMTP_PORT:-587}")"
email.SMTPCrypto = "$(escape_dotenv "${EMAIL_SMTP_CRYPTO:-tls}")"
email.SMTPTimeout = "$(escape_dotenv "${EMAIL_SMTP_TIMEOUT:-5}")"

websocket.bindAddress = 127.0.0.1
websocket.clientPort = ${WEBSOCKET_PORT}
websocket.broadcastPort = ${WEBSOCKET_BROADCAST_PORT}
websocket.maxClients = ${WEBSOCKET_MAX_CLIENTS:-500}
websocket.allowedOrigins = "$(escape_dotenv "${WEBSOCKET_ALLOWED_ORIGINS:-}")"
ENVFILE

chown www-data:www-data "$APP_ROOT/.env"
chmod 640 "$APP_ROOT/.env"

echo "[ENV] Production configuration generated."
if [ -z "${EMAIL_SMTP_USER:-}" ] || [ -z "${EMAIL_SMTP_PASS:-}" ]; then
    echo "[WARN] EMAIL_SMTP_USER or EMAIL_SMTP_PASS is empty; password-reset and contact email delivery will be unavailable."
fi

mkdir -p \
    "$APP_ROOT/writable/cache" \
    "$APP_ROOT/writable/logs" \
    "$APP_ROOT/writable/session" \
    "$APP_ROOT/writable/uploads" \
    "$APP_ROOT/writable/debugbar" \
    "$APP_ROOT/public/uploads/avatars" \
    "$APP_ROOT/public/uploads/settings" \
    "$APP_ROOT/public/uploads/vehicle_types" \
    "$APP_ROOT/public/uploads/vehicles" \
    /tmp/jeepneynvans-nginx/client \
    /tmp/jeepneynvans-nginx/proxy \
    /tmp/jeepneynvans-nginx/fastcgi

chown -R www-data:www-data "$APP_ROOT/writable" "$APP_ROOT/public/uploads" /tmp/jeepneynvans-nginx
chmod -R u=rwX,g=rwX,o=rX "$APP_ROOT/writable" "$APP_ROOT/public/uploads"

echo "[DB] Checking schema and migrations..."
php "$APP_ROOT/import_schema.php" || echo "[DB] Schema import check skipped or already complete."
php "$APP_ROOT/spark" migrate --all 2>&1 || echo "[DB] Migrations already complete or unavailable."
php "$APP_ROOT/spark" db:seed UserSeeder 2>&1 || echo "[DB] Initial seed not required."

PHP_FPM_BIN="$(command -v php-fpm || command -v php-fpm8.2 || command -v php-fpm82 || true)"
if [ -z "$PHP_FPM_BIN" ]; then
    echo "[FATAL] PHP-FPM is not installed in the Railway image."
    exit 1
fi
if ! command -v nginx >/dev/null 2>&1; then
    echo "[FATAL] Nginx is not installed in the Railway image."
    exit 1
fi

RUNTIME_NGINX_CONFIG="/tmp/jeepneynvans-nginx.conf"
sed \
    -e "s/__RAILWAY_PORT__/${PORT}/g" \
    -e "s/__WEBSOCKET_PORT__/${WEBSOCKET_PORT}/g" \
    "$APP_ROOT/deploy/nginx.railway.conf" > "$RUNTIME_NGINX_CONFIG"
nginx -t -c "$RUNTIME_NGINX_CONFIG"

FPM_PID=""
WS_PID=""
NGINX_PID=""
cleanup() {
    trap - EXIT INT TERM
    for pid in "$NGINX_PID" "$FPM_PID" "$WS_PID"; do
        if [ -n "$pid" ] && kill -0 "$pid" 2>/dev/null; then
            kill -TERM "$pid" 2>/dev/null || true
        fi
    done
    wait 2>/dev/null || true
}
trap cleanup EXIT INT TERM

echo "[PHP] Starting PHP-FPM..."
"$PHP_FPM_BIN" --fpm-config "$APP_ROOT/deploy/php-fpm.railway.conf" --nodaemonize &
FPM_PID=$!

echo "[WS] Starting WebSocket server on loopback:${WEBSOCKET_PORT}..."
php "$APP_ROOT/spark" ws:serve &
WS_PID=$!

echo "[WEB] Starting Nginx on Railway port ${PORT}..."
nginx -c "$RUNTIME_NGINX_CONFIG" -g 'daemon off;' &
NGINX_PID=$!

set +e
wait -n "$FPM_PID" "$WS_PID" "$NGINX_PID"
EXIT_CODE=$?
set -e
if [ "$EXIT_CODE" -eq 0 ]; then
    EXIT_CODE=1
fi
echo "[FATAL] A required service exited with status ${EXIT_CODE}; stopping the container."
exit "$EXIT_CODE"
