#!/usr/bin/env bash
# Local WSL bring-up for the Palompon Transit Management System.
# Starts Nginx + PHP-FPM + MariaDB + the WebSocket server, adapting to whatever
# PHP version is installed. Invoked by start_system.bat (run as root inside WSL).

# Project root is passed by start_system.bat (auto-detected); fall back to the default path.
PROJECT_ROOT="${1:-/mnt/c/xampp2/htdocs/jeepneynvans}"
NGINX_SITE="jeepneynvans_local"
DB_NAME="jeepneynvans"
DB_USER="jeepney_user"
DB_PASS="12345678"

echo "====================================================================="
echo "  Palompon Transit Management System - WSL Launcher"
echo "====================================================================="

cd "$PROJECT_ROOT" || { echo "[ERROR] Project not found at $PROJECT_ROOT"; exit 1; }

# --- 1. Detect installed PHP version (highest under /etc/php) ---
PHPVER="$(ls /etc/php 2>/dev/null | grep -E '^[0-9]+\.[0-9]+$' | sort -V | tail -n1)"
[ -z "$PHPVER" ] && PHPVER="8.2"
PHP_BIN="php${PHPVER}"
command -v "$PHP_BIN" >/dev/null 2>&1 || PHP_BIN="php"
echo "[OK] Using PHP ${PHPVER} (service php${PHPVER}-fpm, CLI ${PHP_BIN})."

# --- 2. Sync + patch Nginx config to the detected FPM socket ---
cp "${PROJECT_ROOT}/ubuntu_migration/nginx_local.conf" "/etc/nginx/sites-available/${NGINX_SITE}"
sed -i "s#php[0-9.]*-fpm.sock#php${PHPVER}-fpm.sock#" "/etc/nginx/sites-available/${NGINX_SITE}"
sed -i "s#root [^;]*;#root ${PROJECT_ROOT}/public;#" "/etc/nginx/sites-available/${NGINX_SITE}"
ln -sf "/etc/nginx/sites-available/${NGINX_SITE}" "/etc/nginx/sites-enabled/"
rm -f /etc/nginx/sites-enabled/default
echo "[OK] Nginx configuration synchronized (php${PHPVER}-fpm.sock)."

# --- 3. Start core services ---
service mysql start 2>/dev/null || service mariadb start 2>/dev/null
service "php${PHPVER}-fpm" start 2>/dev/null
service nginx restart 2>/dev/null || service nginx start 2>/dev/null
echo "[OK] MariaDB, PHP-FPM, and Nginx started."

# --- 4. Database: ensure DB + user, import if empty ---
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>/dev/null
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';" 2>/dev/null
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null
if mysql -e "SELECT 1 FROM ${DB_NAME}.users LIMIT 1;" >/dev/null 2>&1; then
    echo "[OK] Database already populated."
else
    echo "[INFO] Database tables not found. Importing SQL dump..."
    mysql "${DB_NAME}" < "${PROJECT_ROOT}/jeepneynvans.sql" 2>/dev/null
    if mysql -e "SELECT 1 FROM ${DB_NAME}.users LIMIT 1;" >/dev/null 2>&1; then
        echo "[OK] Database imported successfully."
    else
        echo "[ERROR] Database import failed - verify jeepneynvans.sql exists in the project root."
    fi
fi

# --- 5. WebSocket server (background, no duplicate) ---
if pgrep -f 'spark ws:serve' >/dev/null 2>&1; then
    echo "[OK] WebSocket server already running."
else
    mkdir -p writable/logs
    setsid nohup "$PHP_BIN" spark ws:serve >> writable/logs/ws.log 2>&1 < /dev/null &
    echo "[OK] WebSocket server started (${PHP_BIN} spark ws:serve)."
fi

# --- 6. Wait for Nginx to actually serve (treat 5xx as not-ready) ---
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
        echo "  ALL SERVICES ARE UP (HTTP ${CODE}) - NO XAMPP NEEDED"
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
echo "  - WebSocket Server: ws://localhost:8081"
echo "  - Database User:    ${DB_USER} / ${DB_PASS}"
echo "====================================================================="
