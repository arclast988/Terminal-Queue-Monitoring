#!/usr/bin/env bash
# Palompon Transit System - WSL Shutdown (native bash)
#
# Mirror of stop_system.bat for users working directly inside a WSL/Ubuntu
# terminal. Auto-detects its own location, self-elevates with sudo, then hands
# off to ubuntu_migration/wsl_local_down.sh (the same script the .bat invokes).
# Database data on disk is preserved.
#
# Usage:
#     cd /mnt/c/path/to/jeepneynvans
#     ./stop_system.sh

# Resolve this script's directory => project root.
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "====================================================================="
echo "  Palompon Transit Management System - WSL Shutdown"
echo "====================================================================="
echo

# --- 1. Detect environment (WSL vs native Linux) ---
if grep -qiE 'microsoft|wsl' /proc/sys/kernel/osrelease 2>/dev/null; then
    WSL_MODE=1
else
    WSL_MODE=0
fi

if [ "$WSL_MODE" -eq 1 ]; then
    # --- 2. Self-elevate (stopping services requires root) ---
    if [ "$(id -u)" -ne 0 ]; then
        echo "[INFO] Re-running with sudo (service shutdown requires root)..."
        exec sudo -E bash "$0" "$@"
    fi

    echo "[OK] Project folder: ${PROJECT_ROOT}"
    echo

    # --- 3. Hand off to the existing teardown script (single source of truth) ---
    DOWN_SCRIPT="${PROJECT_ROOT}/ubuntu_migration/wsl_local_down.sh"
    if [ ! -f "$DOWN_SCRIPT" ]; then
        echo "[ERROR] Missing ${DOWN_SCRIPT}. Is this the project root?"
        exit 1
    fi
    bash "$DOWN_SCRIPT" "$PROJECT_ROOT"

    echo
    echo "  To bring services back up, run ./start_system.sh."
    echo
    exit 0
fi

# --- Native Linux mode ---
if [ "$(id -u)" -ne 0 ]; then
    echo "[INFO] Re-running with sudo (service shutdown requires root)..."
    exec sudo -E bash "$0" "$@"
fi

echo "[OK] Native Linux mode detected. Stopping systemd services..."
PHPVER="$(ls /etc/php 2>/dev/null | grep -E '^[0-9]+\.[0-9]+$' | sort -V | tail -n1)"
[ -z "$PHPVER" ] && PHPVER="8.2"

systemctl stop jeepney-websocket >/dev/null 2>&1 || true
systemctl stop nginx >/dev/null 2>&1 || true
systemctl stop "php${PHPVER}-fpm" >/dev/null 2>&1 || systemctl stop php-fpm >/dev/null 2>&1 || true
systemctl stop postgresql >/dev/null 2>&1 || true

CODE="$(curl -s -o /dev/null -w '%{http_code}' http://localhost/ 2>/dev/null || echo 000)"

echo "====================================================================="
case "$CODE" in
    000)
        echo "  ALL SERVICES ARE DOWN - http://localhost/ no longer responding."
        ;;
    *)
        echo "  [WARN] http://localhost/ still returned HTTP ${CODE}."
        echo "         Something may still be listening on port 80."
        ;;
esac
printf '  To bring services back up, run:\n    sudo systemctl start nginx php%s-fpm postgresql jeepney-websocket\n' "$PHPVER"
echo
