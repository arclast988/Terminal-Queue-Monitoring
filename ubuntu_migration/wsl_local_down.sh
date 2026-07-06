#!/usr/bin/env bash
# Local WSL teardown for the Palompon Transit Management System.
# Stops the WebSocket server, Nginx, PHP-FPM, and MariaDB that were started by
# wsl_local_up.sh. Database data files on disk are preserved so a stop/start
# round-trip is non-destructive. Invoked by stop_system.bat (run as root in WSL).

# Project root is passed by stop_system.bat (auto-detected); not strictly needed
# for teardown, but kept symmetric with wsl_local_up.sh's signature.
PROJECT_ROOT="${1:-/mnt/c/xampp2/htdocs/jeepneynvans}"

echo "====================================================================="
echo "  Palompon Transit Management System - WSL Shutdown"
echo "====================================================================="

# --- 1. Detect installed PHP version so we stop the right php-fpm service ---
PHPVER="$(ls /etc/php 2>/dev/null | grep -E '^[0-9]+\.[0-9]+$' | sort -V | tail -n1)"
[ -z "$PHPVER" ] && PHPVER="8.2"
echo "[OK] Targeting PHP ${PHPVER} (service php${PHPVER}-fpm)."

# --- 2. Stop the background WebSocket server (same fingerprint as up script) ---
if pgrep -f 'spark ws:serve' >/dev/null 2>&1; then
    pkill -f 'spark ws:serve' 2>/dev/null
    sleep 1
    pgrep -f 'spark ws:serve' >/dev/null 2>&1 && pkill -9 -f 'spark ws:serve' 2>/dev/null
    echo "[OK] WebSocket server stopped."
else
    echo "[INFO] WebSocket server was not running."
fi

# --- 3. Stop services in reverse order (edge first, DB last) ---
service nginx stop 2>/dev/null && echo "[OK] Nginx stopped." \
    || echo "[INFO] Nginx was not running."

service "php${PHPVER}-fpm" stop 2>/dev/null && echo "[OK] PHP-FPM (php${PHPVER}-fpm) stopped." \
    || echo "[INFO] PHP-FPM (php${PHPVER}-fpm) was not running."

if service mysql stop 2>/dev/null || service mariadb stop 2>/dev/null; then
    echo "[OK] MariaDB stopped (data preserved on disk)."
else
    echo "[INFO] MariaDB was not running."
fi

# --- 4. Confirm the web stack is actually down ---
CODE="$(curl -s -o /dev/null -w '%{http_code}' http://localhost/ 2>/dev/null)"

echo "====================================================================="
case "$CODE" in
    000)
        echo "  ALL SERVICES ARE DOWN - http://localhost/ no longer responding."
        ;;
    *)
        echo "  [WARN] http://localhost/ still returned HTTP ${CODE}."
        echo "         Something is still listening on port 80."
        ;;
esac
echo "  - Database data preserved (next start_system.bat will reuse it)."
echo "  - To bring services back up, run start_system.bat."
echo "====================================================================="
