#!/usr/bin/env bash
# Local WSL bring-up for the Terminal Queue Monitoring System.
# Starts Nginx + PHP-FPM + PostgreSQL + the WebSocket server, adapting to whatever
# PHP version is installed. Invoked by start_system.bat (run as root inside WSL).

# Project root is passed by start_system.bat (auto-detected); fall back to the default path.
PROJECT_ROOT="${1:-/mnt/c/xampp2/htdocs/jeepneynvans}"
NGINX_SITE="jeepneynvans_local"
DB_NAME="jeepneynvans"
DB_USER="jeepney_user"
DB_PASS="12345678"

echo "====================================================================="
echo "  Terminal Queue Monitoring System - WSL Launcher"
echo "====================================================================="

cd "$PROJECT_ROOT" || { echo "[ERROR] Project not found at $PROJECT_ROOT"; exit 1; }

# --- 1. Detect installed PHP version (highest under /etc/php) ---
PHPVER="$(ls /etc/php 2>/dev/null | grep -E '^[0-9]+\.[0-9]+$' | sort -V | tail -n1)"
[ -z "$PHPVER" ] && PHPVER="8.2"
PHP_BIN="php${PHPVER}"
command -v "$PHP_BIN" >/dev/null 2>&1 || PHP_BIN="php"
echo "[OK] Using PHP ${PHPVER} (service php${PHPVER}-fpm, CLI ${PHP_BIN})."

# --- 2. Sync + patch Nginx config to the detected FPM socket & WS Port ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

WS_PORT="8081"
if [ -f "${PROJECT_ROOT}/.env" ]; then
    ENV_WS_PORT=$(grep -E '^[[:space:]]*websocket\.clientPort[[:space:]]*=' "${PROJECT_ROOT}/.env" | cut -d'=' -f2 | tr -d '[:space:]"' | tr -d "'")
    if [ ! -z "$ENV_WS_PORT" ]; then
        WS_PORT="$ENV_WS_PORT"
    fi
fi

mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
NGINX_SRC_CONF="${PROJECT_ROOT}/ubuntu_migration/nginx_local.conf"
if [ -f "$NGINX_SRC_CONF" ]; then
    cp "$NGINX_SRC_CONF" "/etc/nginx/sites-available/${NGINX_SITE}"
    sed -i "s#php[0-9.]*-fpm.sock#php${PHPVER}-fpm.sock#" "/etc/nginx/sites-available/${NGINX_SITE}" || true
    sed -i "s#root [^;]*;#root ${PROJECT_ROOT}/public;#" "/etc/nginx/sites-available/${NGINX_SITE}" || true
    sed -i "s#proxy_pass http://127.0.0.1:8081;#proxy_pass http://127.0.0.1:${WS_PORT};#" "/etc/nginx/sites-available/${NGINX_SITE}" || true
    ln -sf "/etc/nginx/sites-available/${NGINX_SITE}" "/etc/nginx/sites-enabled/"
    rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-enabled/jeepneynvans
    echo "[OK] Nginx configuration synchronized (php${PHPVER}-fpm.sock, WS port ${WS_PORT})."
    echo "     Root path: ${PROJECT_ROOT}/public"
else
    echo -e "${YELLOW}[WARN] Nginx source config not found at ${NGINX_SRC_CONF}; skipping sync.${NC}"
    echo "       Ensure ubuntu_migration/nginx_local.conf exists in the project root."
fi

# Verify the nginx config was actually updated
ACTUAL_ROOT=$(grep "^[[:space:]]*root " /etc/nginx/sites-available/${NGINX_SITE} 2>/dev/null | sed 's/.*root \(.*\);.*/\1/' | head -1)
if [ -z "$ACTUAL_ROOT" ]; then
    echo -e "${RED}[ERROR] Nginx root path is empty! Config may not have been updated.${NC}"
elif [ "$ACTUAL_ROOT" != "${PROJECT_ROOT}/public" ]; then
    echo -e "${YELLOW}[WARN] Nginx root is: $ACTUAL_ROOT${NC}"
    echo "       Expected:     ${PROJECT_ROOT}/public"
fi

# --- 3. Ensure /var/run/php exists (WSL may not have the /var/run → /run symlink) ---
mkdir -p /var/run/php

# --- 4. Start core services ---
service postgresql start 2>/dev/null
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 20M/' "/etc/php/${PHPVER}/fpm/php.ini" 2>/dev/null || true
sed -i 's/^post_max_size = .*/post_max_size = 25M/' "/etc/php/${PHPVER}/fpm/php.ini" 2>/dev/null || true
service "php${PHPVER}-fpm" start 2>/dev/null

# After starting FPM, wait briefly for the socket file to appear and symlink
# it if it landed under /run/php/ (where PHP-FPM actually writes it).
FPM_SOCK_REAL="/run/php/php${PHPVER}-fpm.sock"
FPM_SOCK_VAR="/var/run/php/php${PHPVER}-fpm.sock"
for _ in $(seq 1 5); do
    if [ -S "$FPM_SOCK_REAL" ]; then
        [ ! -e "$FPM_SOCK_VAR" ] && ln -sf "$FPM_SOCK_REAL" "$FPM_SOCK_VAR"
        break
    fi
    sleep 0.5
done

# Verify the socket Nginx will use actually exists
if [ ! -S "$FPM_SOCK_VAR" ] && [ ! -S "$FPM_SOCK_REAL" ]; then
    echo -e "${RED}[ERROR] PHP-FPM socket not found at either ${FPM_SOCK_VAR} or ${FPM_SOCK_REAL}!${NC}"
    echo "       PHP-FPM may have failed to start. Check: service php${PHPVER}-fpm status"
fi

service nginx restart 2>/dev/null || service nginx start 2>/dev/null
echo "[OK] PostgreSQL, PHP-FPM, and Nginx started."

# --- 5. Database: ensure PostgreSQL DB + user, import if empty ---
su - postgres -c "psql -tAc \"SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}';\"" 2>/dev/null | grep -q 1 || \
    su - postgres -c "psql -c \"CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';\"" 2>/dev/null
su - postgres -c "psql -c \"ALTER USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';\"" 2>/dev/null

su - postgres -c "psql -tAc \"SELECT 1 FROM pg_database WHERE datname='${DB_NAME}';\"" 2>/dev/null | grep -q 1 || \
    su - postgres -c "psql -c \"CREATE DATABASE ${DB_NAME} OWNER ${DB_USER};\"" 2>/dev/null
su - postgres -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};\"" 2>/dev/null

if su - postgres -c "psql -d ${DB_NAME} -tAc \"SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='users';\"" 2>/dev/null | grep -q 1; then
    echo "[OK] PostgreSQL Database already populated."
elif [ -f "${PROJECT_ROOT}/app/Database/postgres_schema.sql" ]; then
    echo "[INFO] Importing PostgreSQL schema (postgres_schema.sql)..."
    su - postgres -c "psql -d ${DB_NAME} -f '${PROJECT_ROOT}/app/Database/postgres_schema.sql'" >/dev/null 2>&1
    su - postgres -c "psql -d ${DB_NAME} -c \"GRANT ALL ON ALL TABLES IN SCHEMA public TO ${DB_USER}; GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO ${DB_USER};\"" >/dev/null 2>&1
    if su - postgres -c "psql -d ${DB_NAME} -tAc \"SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='users';\"" 2>/dev/null | grep -q 1; then
        echo "[OK] PostgreSQL database imported successfully."
    else
        echo -e "${RED}[ERROR] PostgreSQL database import failed.${NC}"
    fi
fi

# --- 6. WebSocket server (background, no duplicate) ---
if pgrep -f 'spark ws:serve' >/dev/null 2>&1; then
    echo "[OK] WebSocket server already running."
else
    mkdir -p writable/logs
    setsid nohup "$PHP_BIN" spark ws:serve >> writable/logs/ws.log 2>&1 < /dev/null &
    echo "[OK] WebSocket server started (${PHP_BIN} spark ws:serve)."
fi

# --- 7. Wait for Nginx to actually serve (treat 5xx as not-ready) ---
CODE="000"
for _ in $(seq 1 30); do
    CODE="$(curl -s -o /dev/null -w '%{http_code}' http://localhost/ 2>/dev/null)"
    case "$CODE" in
        2*|3*|4*) break ;;
    esac
    sleep 1
done

echo "====================================================================="
case "$CODE" in
    2*|3*|4*)
        echo "  ALL SERVICES ARE UP (HTTP ${CODE})"
        ;;
    5*)
        echo "  [WARN] Nginx returned HTTP ${CODE} (likely 502: PHP-FPM socket mismatch)."
        echo "         Confirm the socket exists: ls /run/php/php${PHPVER}-fpm.sock"
        ;;
    *)
        echo "  [WARN] No HTTP response from http://localhost/ (is Nginx running?)."
        ;;
esac
echo "  - Web URL:          http://localhost/"
echo "  - WebSocket Server: ws://localhost:${WS_PORT}"
echo "  - PostgreSQL User:  ${DB_USER} / ${DB_PASS} (Port 5432)"
echo "====================================================================="
