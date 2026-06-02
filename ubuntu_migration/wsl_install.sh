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
apt-get update -y
apt-get install -y git curl unzip software-properties-common ca-certificates lsb-release gnupg sed

# --- 2. MariaDB ---
echo ""
echo "[2/6] Installing MariaDB..."
apt-get install -y mariadb-server mariadb-client
service mariadb start 2>/dev/null || service mysql start 2>/dev/null || true
echo "[OK] MariaDB installed."

# --- 3. PHP 8.2 via ondrej PPA + CodeIgniter 4 extensions ---
echo ""
echo "[3/6] Installing PHP 8.2 and required extensions..."
if ! grep -rq "ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/ 2>/dev/null; then
    add-apt-repository -y ppa:ondrej/php
    apt-get update -y
fi
apt-get install -y \
    php8.2-cli php8.2-fpm php8.2-mysql php8.2-intl php8.2-mbstring \
    php8.2-curl php8.2-xml php8.2-zip php8.2-gd php8.2-opcache php8.2-common
echo "[OK] $(php8.2 -v | head -n1)"

# --- 4. Composer ---
echo ""
echo "[4/6] Installing Composer..."
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    HASH="$(curl -sS https://composer.github.io/installer.sig)"
    php8.2 -r "if (hash_file('sha384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('/tmp/composer-setup.php'); exit(1); }"
    echo ""
    php8.2 /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
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
