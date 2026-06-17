#!/usr/bin/env bash
# Local WSL stack installer for the Palompon Transit Management System.
# Installs MariaDB, PHP 8.2-FPM, Nginx, Composer, and the project's PHP
# dependencies so start_system.bat can launch the app.
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
rm -f /etc/apt/sources.list.d/ondrej-ubuntu-php-*.sources 2>/dev/null
rm -f /etc/apt/sources.list.d/ondrej-ubuntu-php-*.list 2>/dev/null

apt-get update -y
apt-get install -y git curl unzip software-properties-common ca-certificates lsb-release gnupg sed

# --- 2. MariaDB ---
echo ""
echo "[2/6] Installing MariaDB..."
apt-get install -y mariadb-server mariadb-client
service mariadb start 2>/dev/null || service mysql start 2>/dev/null || true
echo "[OK] MariaDB installed."

# --- 3. PHP via PPA or default repositories + CodeIgniter 4 extensions ---
echo ""
echo "[3/6] Installing PHP and required extensions..."

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

if [ "$USE_PPA" = true ]; then
    PHP_SUFFIX="8.2"
else
    PHP_SUFFIX=""
fi

if [ -n "$PHP_SUFFIX" ]; then
    apt-get install -y \
        php${PHP_SUFFIX}-cli php${PHP_SUFFIX}-fpm php${PHP_SUFFIX}-mysql php${PHP_SUFFIX}-intl php${PHP_SUFFIX}-mbstring \
        php${PHP_SUFFIX}-curl php${PHP_SUFFIX}-xml php${PHP_SUFFIX}-zip php${PHP_SUFFIX}-gd php${PHP_SUFFIX}-opcache php${PHP_SUFFIX}-common
    PHP_BIN="php${PHP_SUFFIX}"
else
    apt-get install -y \
        php-cli php-fpm php-mysql php-intl php-mbstring \
        php-curl php-xml php-zip php-gd php-common
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
echo "  - MariaDB, PHP 8.2-FPM, Nginx, Composer installed."
echo "  - Project dependencies installed in ${PROJECT_ROOT}/vendor."
echo ""
echo "  Next: from Windows, double-click start_system.bat."
echo "====================================================================="
