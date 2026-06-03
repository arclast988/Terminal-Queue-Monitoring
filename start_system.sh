#!/usr/bin/env bash
# Palompon Transit System - WSL Launcher (native bash)
#
# Mirror of start_system.bat for users working directly inside a WSL/Ubuntu
# terminal. Auto-detects its own location, self-elevates with sudo, then hands
# off to ubuntu_migration/wsl_local_up.sh (the same script the .bat invokes).
#
# Usage:
#     cd /mnt/c/path/to/jeepneynvans
#     ./start_system.sh

# Resolve this script's directory => project root (works wherever it lives).
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "====================================================================="
echo "  Palompon Transit Management System - WSL Launcher"
echo "====================================================================="
echo

# --- 1. Sanity-check: must be running inside WSL ---
if ! grep -qiE 'microsoft|wsl' /proc/sys/kernel/osrelease 2>/dev/null; then
    echo "[ERROR] This launcher is for WSL (Windows) only."
    echo "        On native Linux (e.g. Mint/Ubuntu), set up once with:"
    echo "            sudo ./install_linux.sh"
    echo "        After that the app runs as systemd services; start/stop with:"
    echo "            sudo systemctl start nginx php8.2-fpm mariadb jeepney-websocket"
    exit 1
fi

# --- 2. Self-elevate (the underlying script needs root for service control) ---
if [ "$(id -u)" -ne 0 ]; then
    echo "[INFO] Re-running with sudo (services require root)..."
    exec sudo -E bash "$0" "$@"
fi

echo "[OK] Project folder: ${PROJECT_ROOT}"
echo

# --- 3. Hand off to the existing bring-up script (single source of truth) ---
UP_SCRIPT="${PROJECT_ROOT}/ubuntu_migration/wsl_local_up.sh"
if [ ! -f "$UP_SCRIPT" ]; then
    echo "[ERROR] Missing ${UP_SCRIPT}. Is this the project root?"
    exit 1
fi
bash "$UP_SCRIPT" "$PROJECT_ROOT"

# --- 4. Try to open the browser (best-effort; fall back to printing the URL) ---
URL="http://localhost/"
echo
echo "[INFO] Opening ${URL} ..."
if command -v wslview >/dev/null 2>&1; then
    wslview "$URL" >/dev/null 2>&1 &
elif command -v xdg-open >/dev/null 2>&1; then
    xdg-open "$URL" >/dev/null 2>&1 &
elif command -v cmd.exe >/dev/null 2>&1; then
    cmd.exe /c start "" "$URL" >/dev/null 2>&1 &
elif [ -x /mnt/c/Windows/System32/cmd.exe ]; then
    /mnt/c/Windows/System32/cmd.exe /c start "" "$URL" >/dev/null 2>&1 &
else
    echo "       (No browser opener available - open ${URL} manually.)"
fi

echo
echo "  Services keep running in WSL. Run ./stop_system.sh to shut down."
echo
