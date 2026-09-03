#!/usr/bin/env bash
# Local WSL stack installer for the Palompon Transit Management System.
# Installs PostgreSQL, PHP 8.2+ (auto-detected), Nginx, Composer, and the project's PHP
# dependencies inside WSL Ubuntu. Invoked by install_system.bat.
# Idempotent - safe to re-run. Invoked by install_system.bat as root inside WSL.

set -e

# Project root is passed by install_system.bat (auto-detected); fall back kept
# for parity with wsl_local_up.sh's signature.
PROJECT_ROOT="${1:-/mnt/c/xampp2/htdocs/jeepneynvans}"

echo "====================================================================="
echo "  Palompon Transit Management System - WSL Stack Installer"
echo "====================================================================="

# --- 1. Apt index + core utilities ---
echo ""
echo "[1/6] Updating apt index and installing core utilities..."
export DEBIAN_FRONTEND=noninteractive

# Clean up any broken ondrej/php PPA repository sources from previous failed runs
rm -f /etc/apt/sources.list.d/ondrej-*.sources 2>/dev/null
rm -f /etc/apt/sources.list.d/ondrej-*.list 2>/dev/null

apt-get update -y || echo "[WARN] apt-get update finished with warnings."
apt-get install -y git curl unzip software-properties-common ca-certificates lsb-release gnupg sed

# --- 2. PostgreSQL ---
echo ""
echo "[2/6] Installing PostgreSQL..."
apt-get install -y postgresql postgresql-contrib
service postgresql start 2>/dev/null || true
echo "[OK] PostgreSQL installed."

# --- 3. PHP version resolution + CodeIgniter 4 extensions ---
# Resolution order (mirrors install_linux.sh):
#   1. An already-installed PHP in the 8.2-8.9 range (incl. 8.5) — reuse it,
#      don't reinstall. We confirm via dpkg so a half-removed package is ignored.
#   2. Otherwise auto-install the newest fully-packaged PHP in the 8.2-8.4 range
#      (matches composer's ^8.2). The upper bound is capped: a brand-new release
#      may publish php8.x-cli before all its extensions land, which would break
#      the apt-get install below.
#   3. Fall back to the distro-default (unsuffixed) packages on non-LTS codenames.
echo ""
echo "[3/6] Resolving PHP version..."

CODENAME=$(lsb_release -cs)
USE_PPA=false
case "$CODENAME" in
    focal|jammy|noble)
        USE_PPA=true
        ;;
    *)
        echo "[INFO] Ubuntu codename '$CODENAME' is not a standard LTS release. Skipping PPA..."
        ;;
esac

if [ "$USE_PPA" = true ]; then
    if ! grep -rq "ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/ 2>/dev/null; then
        add-apt-repository -y ppa:ondrej/php
        apt-get update -y
    fi
fi

# (1) Reuse an already-installed PHP in range (e.g. 8.5 pre-installed on the box).
EXISTING_PHP=""
for _v in $(ls /etc/php 2>/dev/null | grep -E '^8\.[2-9]$' | sort -V -r); do
    if dpkg -s "php${_v}-cli" >/dev/null 2>&1; then
        EXISTING_PHP="$_v"
        break
    fi
done

if [ -n "$EXISTING_PHP" ]; then
    PHP_VER="$EXISTING_PHP"
    echo "[OK] Using already-installed PHP ${PHP_VER} (no reinstall)."
elif [ "$USE_PPA" = true ]; then
    # (2) Auto-install the newest fully-packaged PHP in 8.2-8.4.
    echo "Searching for the latest supported PHP version..."
    PHP_VER="$(apt-cache pkgnames | grep -E '^php8\.[2-4]-cli$' | sed -E 's/^php([0-9]+\.[0-9]+)-cli$/\1/' | sort -V | tail -n1)"
    if [ -n "$PHP_VER" ]; then
        echo "[OK] Found PHP ${PHP_VER} as the latest available version."
    else
        PHP_VER="8.2"
        echo "[WARN] Could not query apt-cache for newer PHP; defaulting to PHP ${PHP_VER}."
    fi
else
    PHP_VER=""
fi

# Install packages for the resolved version. apt-get install is idempotent, so
# passing already-installed packages for an existing PHP just fills in any
# missing extensions.
if [ -n "$PHP_VER" ]; then
    WANTED_PKGS=(
        "php${PHP_VER}-cli" "php${PHP_VER}-fpm" "php${PHP_VER}-pgsql" "php${PHP_VER}-sqlite3" "php${PHP_VER}-intl" "php${PHP_VER}-mbstring"
        "php${PHP_VER}-curl" "php${PHP_VER}-xml" "php${PHP_VER}-zip" "php${PHP_VER}-gd" "php${PHP_VER}-opcache" "php${PHP_VER}-common"
    )
    PKGS_TO_INSTALL=()
    for pkg in "${WANTED_PKGS[@]}"; do
        if apt-cache show "$pkg" >/dev/null 2>&1; then
            PKGS_TO_INSTALL+=("$pkg")
        else
            echo "[INFO] Skipping package '$pkg' (not found in apt repositories, possibly built-in)."
        fi
    done
    apt-get install -y "${PKGS_TO_INSTALL[@]}"
    PHP_BIN="php${PHP_VER}"
else
    WANTED_PKGS=(
        php-cli php-fpm php-pgsql php-sqlite3 php-intl php-mbstring
        php-curl php-xml php-zip php-gd php-common
    )
    PKGS_TO_INSTALL=()
    for pkg in "${WANTED_PKGS[@]}"; do
        if apt-cache show "$pkg" >/dev/null 2>&1; then
            PKGS_TO_INSTALL+=("$pkg")
        else
            echo "[INFO] Skipping package '$pkg' (not found in apt repositories)."
        fi
    done
    apt-get install -y "${PKGS_TO_INSTALL[@]}"
    PHP_BIN="php"
fi
echo "[OK] $($PHP_BIN -v | head -n1)"

# --- 4. Composer ---
echo ""
echo "[4/6] Installing Composer..."
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    HASH="$(curl -sS https://composer.github.io/installer.sig)"
    $PHP_BIN -r "if (hash_file('sha384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('/tmp/composer-setup.php'); exit(1); }"
    echo ""
    $PHP_BIN /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi
echo "[OK] $(composer --version 2>/dev/null | head -n1)"

# --- 5. Nginx (installed only; wsl_local_up.sh starts it on every launch) ---
echo ""
echo "[5/6] Installing Nginx..."
apt-get install -y nginx
echo "[OK] Nginx installed."

# --- 6. Project Composer dependencies ---
echo ""
echo "[6/6] Installing project Composer dependencies..."
cd "$PROJECT_ROOT" || { echo "[ERROR] Project not found at $PROJECT_ROOT"; exit 1; }
if [ -f composer.json ]; then
    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --prefer-dist
    echo "[OK] vendor/ ready at ${PROJECT_ROOT}/vendor."
else
    echo "[WARN] No composer.json at $PROJECT_ROOT - skipping composer install."
fi

echo ""
echo "====================================================================="
echo "  INSTALLATION COMPLETE."
echo "  - PostgreSQL, PHP, Nginx, Composer installed."
echo "  - Project dependencies installed in ${PROJECT_ROOT}/vendor."
echo ""
echo "  Next: from Windows, double-click start_system.bat."
echo "====================================================================="
