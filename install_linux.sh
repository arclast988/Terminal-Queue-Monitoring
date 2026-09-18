#!/usr/bin/env bash
#
# install_linux.sh — Terminal Queue Monitoring System
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
echo "  Terminal Queue Monitoring System — Linux Installer"
echo "  Project : $PROJECT_ROOT"
echo "  Run as  : $RUN_USER:$RUN_GROUP"
echo "====================================================================="

# ── 1. System packages ──────────────────────────────────────────────────────
say "Installing system packages (this can take a few minutes)…"
export DEBIAN_FRONTEND=noninteractive

# Clean up any broken ondrej/php PPA repository sources from previous failed runs
rm -f /etc/apt/sources.list.d/ondrej-ubuntu-php-*.sources 2>/dev/null
rm -f /etc/apt/sources.list.d/ondrej-ubuntu-php-*.list 2>/dev/null

apt-get update -y || warn "apt update finished with warnings."
apt-get install -y git curl unzip ca-certificates lsb-release gnupg software-properties-common

apt-get install -y postgresql postgresql-contrib

# PHP 8.2+ — add PPA for supported Ubuntu/Mint LTS codenames, or fallback to distro packages
. /etc/os-release 2>/dev/null || true
TARGET_CODENAME="${UBUNTU_CODENAME:-$(lsb_release -cs 2>/dev/null || echo "")}"

USE_PPA=false
case "$TARGET_CODENAME" in
    focal|jammy|noble)
        USE_PPA=true
        ;;
    *)
        warn "Distribution codename '${TARGET_CODENAME:-unknown}' is not a standard Ubuntu LTS base. Skipping ondrej/php PPA..."
        ;;
esac

if [ "$USE_PPA" = true ]; then
    say "Adding ondrej/php PPA (base: ${TARGET_CODENAME}) to access latest PHP versions…"
    if ! add-apt-repository -y ppa:ondrej/php >/dev/null 2>&1; then
        warn "Could not add ondrej/php PPA. Falling back to default repositories."
        rm -f /etc/apt/sources.list.d/ondrej-*.sources 2>/dev/null
        rm -f /etc/apt/sources.list.d/ondrej-*.list 2>/dev/null
    else
        # If add-apt-repository used Mint's codename (e.g. victoria/wilma), rewrite it to Ubuntu base (e.g. jammy/noble).
        if [ -n "${VERSION_CODENAME:-}" ] && [ -n "${UBUNTU_CODENAME:-}" ] && [ "${VERSION_CODENAME}" != "${UBUNTU_CODENAME}" ]; then
            sed -i "s/\b${VERSION_CODENAME}\b/${UBUNTU_CODENAME}/g" \
                /etc/apt/sources.list.d/ondrej-*.list \
                /etc/apt/sources.list.d/ondrej-*.sources 2>/dev/null || true
        fi
    fi
fi
apt-get update -y || warn "apt update finished with warnings."

# Detect PHP. Resolution order:
#   1. An already-installed PHP in the 8.2-8.9 range — reuse it.
#   2. Auto-install newest available PHP in apt-cache.
#   3. Fall back to distro default php-* packages.
EXISTING_PHP=""
for _v in $(ls /etc/php 2>/dev/null | grep -E '^8\.[2-9]$' | sort -V -r); do
    if dpkg -s "php${_v}-cli" >/dev/null 2>&1; then
        EXISTING_PHP="$_v"
        break
    fi
done

if [ -n "$EXISTING_PHP" ]; then
    PHP_VER="$EXISTING_PHP"
    ok "Using already-installed PHP ${PHP_VER} (no reinstall)."
else
    say "Searching for available PHP version..."
    LATEST_PHP="$(apt-cache pkgnames 2>/dev/null | grep -E '^php8\.[2-9]-cli$' | sed -E 's/^php([0-9]+\.[0-9]+)-cli$/\1/' | sort -V | tail -n1 || true)"
    if [ -n "$LATEST_PHP" ]; then
        PHP_VER="$LATEST_PHP"
        ok "Found PHP ${PHP_VER} as the latest available version."
    else
        PHP_VER=""
        say "Using distribution default PHP packages."
    fi
fi

if [ -n "$PHP_VER" ]; then
    WANTED_PKGS=(
        "php${PHP_VER}-cli" "php${PHP_VER}-fpm" "php${PHP_VER}-pgsql" "php${PHP_VER}-sqlite3" "php${PHP_VER}-intl"
        "php${PHP_VER}-mbstring" "php${PHP_VER}-curl" "php${PHP_VER}-xml" "php${PHP_VER}-zip"
        "php${PHP_VER}-gd" "php${PHP_VER}-opcache" "php${PHP_VER}-common"
    )
else
    WANTED_PKGS=(
        "php-cli" "php-fpm" "php-pgsql" "php-sqlite3" "php-intl"
        "php-mbstring" "php-curl" "php-xml" "php-zip"
        "php-gd" "php-opcache" "php-common"
    )
fi

PKGS_TO_INSTALL=()
for pkg in "${WANTED_PKGS[@]}"; do
    if apt-cache show "$pkg" >/dev/null 2>&1; then
        PKGS_TO_INSTALL+=("$pkg")
    else
        warn "Skipping package '$pkg' (not found in apt repositories)."
    fi
done
apt-get install -y "${PKGS_TO_INSTALL[@]}"

apt-get install -y nginx

if [ -z "$PHP_VER" ]; then
    PHP_VER="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.2")"
fi
ok "Packages installed."

# ── 2. Composer ─────────────────────────────────────────────────────────────
PHP_BIN="$(command -v "php${PHP_VER}" || command -v php)"
if ! command -v composer >/dev/null 2>&1; then
    say "Installing Composer…"
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    HASH="$(curl -sS https://composer.github.io/installer.sig)"
    "$PHP_BIN" -r "if (hash_file('sha384', '/tmp/composer-setup.php') === '${HASH}') { echo 'verified'.PHP_EOL; } else { fwrite(STDERR, 'Composer installer corrupt'.PHP_EOL); unlink('/tmp/composer-setup.php'); exit(1); }"
    "$PHP_BIN" /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
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
CI_ENVIRONMENT = production
# Explicit localhost baseURL: reliable for a single-machine setup (e.g. a
# classroom Linux Mint demo). For LAN/multi-device access, set this to the
# machine's LAN IP (or '' to auto-detect the request host) in .env on that box.
app.baseURL = 'http://localhost/'
app.indexPage = ''
database.default.hostname = 127.0.0.1
database.default.database = ${DB_NAME}
database.default.username = ${DB_USER}
database.default.password = ${DB_PASS}
database.default.DBDriver = Postgre
database.default.port = 5432
database.default.schema = public
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

# Ensure /var/run/php directory exists (some minimal installs omit it)
mkdir -p /var/run/php

ok "Nginx root → ${PROJECT_ROOT}/public"

# ── 7. writable/ permissions ────────────────────────────────────────────────
mkdir -p "$PROJECT_ROOT/writable"
chown -R "$RUN_USER":"$RUN_GROUP" "$PROJECT_ROOT/writable"
chmod -R 775 "$PROJECT_ROOT/writable"

# ── 8. Database ─────────────────────────────────────────────────────────────
say "Setting up the PostgreSQL database…"
systemctl enable --now postgresql 2>/dev/null || die "Could not start PostgreSQL."

# Create PostgreSQL user if not exists
sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}';" 2>/dev/null | grep -q 1 || \
    sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';"

# Ensure password matches
sudo -u postgres psql -c "ALTER USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';"

# Create database if not exists
sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}';" 2>/dev/null | grep -q 1 || \
    sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME} OWNER ${DB_USER};"

sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"

if sudo -u postgres psql -d "${DB_NAME}" -tAc "SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='users';" 2>/dev/null | grep -q 1; then
    ok "PostgreSQL Database already populated."
elif [ -f "$PROJECT_ROOT/app/Database/postgres_schema.sql" ]; then
    sudo -u postgres psql -d "${DB_NAME}" -f "$PROJECT_ROOT/app/Database/postgres_schema.sql" || warn "Import reported errors."
    sudo -u postgres psql -d "${DB_NAME}" -c "GRANT ALL ON ALL TABLES IN SCHEMA public TO ${DB_USER}; GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO ${DB_USER};"
    ok "Imported app/Database/postgres_schema.sql"
else
    warn "No PostgreSQL schema found — the app will start with an empty database."
fi

# ── 9. WebSocket server (systemd, runs as the login user) ───────────────────
say "Installing the WebSocket systemd service…"
PHP_BIN="$(command -v "php${PHP_VER}" || command -v php)"
cat > /etc/systemd/system/jeepney-websocket.service <<EOF
[Unit]
Description=Terminal Queue Monitoring System WebSocket Server
After=network.target postgresql.service
Wants=postgresql.service

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
FPM_SVC="php${PHP_VER}-fpm"
if ! systemctl list-unit-files 2>/dev/null | grep -qE "^${FPM_SVC}\.service"; then
    if systemctl list-unit-files 2>/dev/null | grep -qE "^php-fpm\.service"; then
        FPM_SVC="php-fpm"
    fi
fi
systemctl enable "$FPM_SVC" >/dev/null 2>&1 || true
systemctl restart "$FPM_SVC"
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
echo "     sudo systemctl status nginx php${PHP_VER}-fpm postgresql jeepney-websocket"
echo "====================================================================="

# ── 11. Launch browser ──────────────────────────────────────────────────────
URL="http://localhost/"
say "Opening ${URL} in browser…"
if [ -n "${RUN_USER:-}" ] && [ "$RUN_USER" != "root" ] && command -v xdg-open >/dev/null 2>&1; then
    sudo -u "$RUN_USER" DISPLAY="${DISPLAY:-:0}" xdg-open "$URL" >/dev/null 2>&1 &
elif command -v xdg-open >/dev/null 2>&1; then
    xdg-open "$URL" >/dev/null 2>&1 &
elif command -v wslview >/dev/null 2>&1; then
    wslview "$URL" >/dev/null 2>&1 &
elif command -v sensible-browser >/dev/null 2>&1; then
    sensible-browser "$URL" >/dev/null 2>&1 &
elif command -v cmd.exe >/dev/null 2>&1; then
    cmd.exe /c start "" "$URL" >/dev/null 2>&1 &
elif [ -x /mnt/c/Windows/System32/cmd.exe ]; then
    /mnt/c/Windows/System32/cmd.exe /c start "" "$URL" >/dev/null 2>&1 &
fi

