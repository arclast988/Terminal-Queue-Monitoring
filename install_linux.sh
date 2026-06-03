#!/usr/bin/env bash
#
# install_linux.sh — Palompon Transit Management System
# ─────────────────────────────────────────────────────────────────────────────
# One-shot installer for a NATIVE Ubuntu/Debian machine (no WSL, no XAMPP).
#
# This is the Linux equivalent of install_system.bat. install_system.bat only
# sets up WSL + Ubuntu on Windows and then runs the Linux installer inside it;
# on a real Linux box you don't need any of that, so this script does the actual
# install directly: it installs the stack, configures Nginx + PHP-FPM + the
# WebSocket server to run as YOUR login user, sets up the database, and enables
# everything via systemd so it keeps running and auto-starts on boot.
#
# The app is reachable at  http://localhost/  and at  http://<your-LAN-IP>/
# so classmates on the same Wi-Fi can open it too.
#
# USAGE — from inside the cloned project folder:
#     sudo ./install_linux.sh
#
# Safe to run again (idempotent). Targets Ubuntu/Debian (apt) + systemd.
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

# ── pretty logging ──────────────────────────────────────────────────────────
if [ -t 1 ]; then
    GREEN="\033[1;32m"; YELLOW="\033[1;33m"; RED="\033[1;31m"; CYAN="\033[1;36m"; RESET="\033[0m"
else
    GREEN=""; YELLOW=""; RED=""; CYAN=""; RESET=""
fi
say()  { printf '%b %s\n' "${CYAN}==>${RESET}"  "$*"; }
ok()   { printf '%b %s\n' "${GREEN}[OK]${RESET}"  "$*"; }
warn() { printf '%b %s\n' "${YELLOW}[WARN]${RESET}" "$*"; }
die()  { printf '%b %s\n' "${RED}[ERROR]${RESET}" "$*" >&2; exit 1; }

# ── 0. Preflight ────────────────────────────────────────────────────────────
command -v apt-get   >/dev/null 2>&1 || die "This installer targets Ubuntu/Debian (apt). For Fedora/Arch, install the stack manually."
command -v systemctl >/dev/null 2>&1 || die "systemd (systemctl) is required."

[ "$(id -u)" -eq 0 ] || die "Please run with sudo:  sudo ./install_linux.sh"

RUN_USER="${SUDO_USER:-}"
if [ -z "$RUN_USER" ] || [ "$RUN_USER" = "root" ]; then
    die "Run via 'sudo ./install_linux.sh' as your normal user (not as the root account directly) so the app runs as you."
fi
RUN_GROUP="$(id -gn "$RUN_USER")"

# Resolve this script's directory (follow symlinks) → project root.
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do
    DIR="$(cd -P "$(dirname "$SOURCE")" >/dev/null 2>&1 && pwd)"
    SOURCE="$(readlink "$SOURCE")"
    [ "${SOURCE#/}" = "$SOURCE" ] && SOURCE="$DIR/$SOURCE"
done
PROJECT_ROOT="$(cd -P "$(dirname "$SOURCE")" >/dev/null 2>&1 && pwd)"
[ -f "$PROJECT_ROOT/spark" ] || die "No 'spark' file in $PROJECT_ROOT — run this from the project root."

PHP_VER="8.2"
DB_NAME="jeepneynvans"
DB_USER="jeepney_user"
DB_PASS="12345678"
NGINX_SITE="jeepneynvans"

echo "====================================================================="
echo "  Palompon Transit Management System — Linux Installer"
echo "  Project : $PROJECT_ROOT"
echo "  Run as  : $RUN_USER:$RUN_GROUP"
echo "====================================================================="

# ── 1. System packages ──────────────────────────────────────────────────────
say "Installing system packages (this can take a few minutes)…"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y git curl unzip ca-certificates lsb-release gnupg software-properties-common

apt-get install -y mariadb-server mariadb-client

# PHP 8.2 — add the ondrej PPA only if 8.2 isn't already available.
if ! apt-cache show "php${PHP_VER}-cli" >/dev/null 2>&1; then
    say "PHP ${PHP_VER} not in the base repos; adding the ondrej/php PPA…"
    add-apt-repository -y ppa:ondrej/php 2>/dev/null \
        || die "Could not add a PHP ${PHP_VER} repository. On Debian add Sury's repo (https://deb.sury.org/), then re-run."
    apt-get update -y
fi
apt-get install -y \
    "php${PHP_VER}-cli" "php${PHP_VER}-fpm" "php${PHP_VER}-mysql" "php${PHP_VER}-intl" \
    "php${PHP_VER}-mbstring" "php${PHP_VER}-curl" "php${PHP_VER}-xml" "php${PHP_VER}-zip" \
    "php${PHP_VER}-gd" "php${PHP_VER}-opcache" "php${PHP_VER}-common"

apt-get install -y nginx
ok "Packages installed."

# ── 2. Composer ─────────────────────────────────────────────────────────────
if ! command -v composer >/dev/null 2>&1; then
    say "Installing Composer…"
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    HASH="$(curl -sS https://composer.github.io/installer.sig)"
    "php${PHP_VER}" -r "if (hash_file('sha384', '/tmp/composer-setup.php') === '${HASH}') { echo 'verified'.PHP_EOL; } else { fwrite(STDERR, 'Composer installer corrupt'.PHP_EOL); unlink('/tmp/composer-setup.php'); exit(1); }"
    "php${PHP_VER}" /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi
COMPOSER_BIN="$(command -v composer)"
ok "Composer: $(composer --version 2>/dev/null | head -n1)"

# ── 3. Project PHP dependencies (as the login user) ─────────────────────────
say "Installing PHP dependencies (composer install)…"
sudo -u "$RUN_USER" -H "$COMPOSER_BIN" install --no-interaction --prefer-dist --working-dir="$PROJECT_ROOT"
ok "vendor/ ready."

# ── 4. .env ─────────────────────────────────────────────────────────────────
say "Configuring .env…"
ENV_FILE="$PROJECT_ROOT/.env"
if [ ! -f "$ENV_FILE" ]; then
    if [ -f "$PROJECT_ROOT/env" ]; then
        cp "$PROJECT_ROOT/env" "$ENV_FILE"
        ok "Created .env from the env template."
    else
        : > "$ENV_FILE"
        warn "No env template found; created an empty .env."
    fi
fi
ENV_MARKER="# === install_linux.sh (auto-generated) ==="
if ! grep -qF "$ENV_MARKER" "$ENV_FILE"; then
    cat >> "$ENV_FILE" <<EOF

$ENV_MARKER
CI_ENVIRONMENT = development
# Explicit localhost baseURL: reliable for a single-machine setup (e.g. a
# classroom Linux Mint demo). For LAN/multi-device access, set this to the
# machine's LAN IP (or '' to auto-detect the request host) in .env on that box.
app.baseURL = 'http://localhost/'
database.default.hostname = localhost
database.default.database = ${DB_NAME}
database.default.username = ${DB_USER}
database.default.password = ${DB_PASS}
database.default.DBDriver = MySQLi
database.default.port = 3306
EOF
    ok "Wrote environment + database settings to .env."
else
    ok ".env already configured (left as-is)."
fi
chown "$RUN_USER":"$RUN_GROUP" "$ENV_FILE"

# ── 5. Run Nginx + PHP-FPM as the login user ────────────────────────────────
# (so the app can read the home-folder clone and write to writable/).
say "Setting Nginx + PHP-FPM to run as '${RUN_USER}'…"
if [ -f /etc/nginx/nginx.conf ]; then
    sed -i -E "s/^user[[:space:]]+[^;]+;/user ${RUN_USER};/" /etc/nginx/nginx.conf
fi
POOL="/etc/php/${PHP_VER}/fpm/pool.d/www.conf"
if [ -f "$POOL" ]; then
    sed -i -E "s/^;?[[:space:]]*user[[:space:]]*=.*/user = ${RUN_USER}/"          "$POOL"
    sed -i -E "s/^;?[[:space:]]*group[[:space:]]*=.*/group = ${RUN_USER}/"        "$POOL"
    sed -i -E "s/^;?[[:space:]]*listen.owner[[:space:]]*=.*/listen.owner = ${RUN_USER}/" "$POOL"
    sed -i -E "s/^;?[[:space:]]*listen.group[[:space:]]*=.*/listen.group = ${RUN_USER}/" "$POOL"
fi

# ── 6. Nginx site (reuse the project's local config; it has the /ws block) ──
say "Configuring the Nginx site…"
cp "$PROJECT_ROOT/ubuntu_migration/nginx_local.conf" "/etc/nginx/sites-available/${NGINX_SITE}"
sed -i -E "s#php[0-9]+\.[0-9]+-fpm\.sock#php${PHP_VER}-fpm.sock#g" "/etc/nginx/sites-available/${NGINX_SITE}"
sed -i -E "s#root[[:space:]]+[^;]+;#root ${PROJECT_ROOT}/public;#"  "/etc/nginx/sites-available/${NGINX_SITE}"
sed -i -E "s/server_name[[:space:]]+[^;]+;/server_name _;/"        "/etc/nginx/sites-available/${NGINX_SITE}"
ln -sf "/etc/nginx/sites-available/${NGINX_SITE}" "/etc/nginx/sites-enabled/${NGINX_SITE}"
rm -f /etc/nginx/sites-enabled/default
ok "Nginx root → ${PROJECT_ROOT}/public"

# ── 7. writable/ permissions ────────────────────────────────────────────────
mkdir -p "$PROJECT_ROOT/writable"
chown -R "$RUN_USER":"$RUN_GROUP" "$PROJECT_ROOT/writable"
chmod -R 775 "$PROJECT_ROOT/writable"

# ── 8. Database ─────────────────────────────────────────────────────────────
say "Setting up the database…"
systemctl enable --now mariadb 2>/dev/null || systemctl enable --now mysql 2>/dev/null || die "Could not start MariaDB."
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"
if mysql -e "SELECT 1 FROM ${DB_NAME}.users LIMIT 1;" >/dev/null 2>&1; then
    ok "Database already populated."
elif [ -f "$PROJECT_ROOT/jeepneynvans.sql" ]; then
    mysql "${DB_NAME}" < "$PROJECT_ROOT/jeepneynvans.sql"      || warn "Import reported errors."
    ok "Imported jeepneynvans.sql"
elif [ -f "$PROJECT_ROOT/jeepneynvans_clean.sql" ]; then
    mysql "${DB_NAME}" < "$PROJECT_ROOT/jeepneynvans_clean.sql" || warn "Import reported errors."
    ok "Imported jeepneynvans_clean.sql"
else
    warn "No SQL dump found — the app will start with an empty database."
fi

# ── 9. WebSocket server (systemd, runs as the login user) ───────────────────
say "Installing the WebSocket systemd service…"
PHP_BIN="$(command -v "php${PHP_VER}" || command -v php)"
cat > /etc/systemd/system/jeepney-websocket.service <<EOF
[Unit]
Description=Jeepney nVans WebSocket Server
After=network.target mariadb.service
Wants=mariadb.service

[Service]
Type=simple
User=${RUN_USER}
Group=${RUN_GROUP}
WorkingDirectory=${PROJECT_ROOT}
ExecStart=${PHP_BIN} spark ws:serve
Restart=on-failure
RestartSec=5s
StandardOutput=journal
StandardError=journal
SyslogIdentifier=jeepney-websocket

[Install]
WantedBy=multi-user.target
EOF
systemctl daemon-reload
systemctl enable --now jeepney-websocket.service
ok "WebSocket service enabled."

# ── 10. Start web services + open the firewall ──────────────────────────────
say "Starting Nginx + PHP-FPM…"
systemctl enable "php${PHP_VER}-fpm" >/dev/null 2>&1 || true
systemctl restart "php${PHP_VER}-fpm"
nginx -t
systemctl enable nginx >/dev/null 2>&1 || true
systemctl restart nginx

if command -v ufw >/dev/null 2>&1 && ufw status 2>/dev/null | grep -qi "Status: active"; then
    ufw allow 80/tcp >/dev/null 2>&1 || true
    ok "Firewall: allowed inbound port 80."
fi

# Wait for the app to actually respond.
say "Waiting for the app to respond…"
CODE="000"
for _ in $(seq 1 30); do
    CODE="$(curl -s -o /dev/null -w '%{http_code}' http://localhost/ 2>/dev/null || echo 000)"
    case "$CODE" in 2*|3*|4*) break ;; esac
    sleep 1
done
LAN_IP="$(hostname -I 2>/dev/null | awk '{print $1}')"

echo
echo "====================================================================="
case "$CODE" in
    2*|3*|4*) ok "App is UP (HTTP ${CODE})." ;;
    5*) warn "Nginx returned HTTP ${CODE} (often a PHP-FPM socket issue). Check: sudo systemctl status php${PHP_VER}-fpm" ;;
    *)  warn "No response yet from http://localhost/. Check: sudo systemctl status nginx" ;;
esac
echo "  Local:    http://localhost/"
[ -n "$LAN_IP" ] && echo "  Network:  http://${LAN_IP}/   (open from another device on the same Wi-Fi)"
echo "  Database: ${DB_USER} / ${DB_PASS}   (db: ${DB_NAME})"
echo
echo "  Everything auto-starts on boot. Manage the services with:"
echo "     sudo systemctl status nginx php${PHP_VER}-fpm mariadb jeepney-websocket"
echo "====================================================================="
