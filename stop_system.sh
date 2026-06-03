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

# --- 1. Sanity-check: must be running inside WSL ---
if ! grep -qiE 'microsoft|wsl' /proc/sys/kernel/osrelease 2>/dev/null; then
    echo "[ERROR] This shutdown script is for WSL (Windows) only."
    echo "        On native Linux (e.g. Mint/Ubuntu), stop the services with:"
    echo "            sudo systemctl stop nginx php8.4-fpm mariadb jeepney-websocket"
    exit 1
fi

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
