#!/usr/bin/env bash

# Jeepney NVans - Ubuntu + Nginx Server Provisioning & Deployment Script
# ─────────────────────────────────────────────────────────────────────────────
# This script automates setting up a fresh Ubuntu server (22.04 LTS or 24.04 LTS)
# to host the Jeepney NVans Capstone Project without XAMPP.
# 
# Usage:
#   1. Copy this script to your Ubuntu server.
#   2. Make it executable: chmod +x deploy.sh
#   3. Run as root or with sudo: sudo ./deploy.sh
# ─────────────────────────────────────────────────────────────────────────────

# Exit immediately if a command exits with a non-zero status
set -e

echo "=== [1/9] Updating Ubuntu System Repositories ==="
apt update && apt upgrade -y

echo "=== [2/9] Installing Core Utilities ==="
apt install -y git curl unzip software-properties-common ca-certificates lsb-release gnupg

echo "=== [3/9] Installing PostgreSQL Database Server ==="
apt install -y postgresql postgresql-contrib
systemctl enable postgresql
systemctl start postgresql

echo "=== [4/9] Adding PHP 8.2+ Repository & Installing PHP ==="
# Add Ondrej Surý's PHP repository (the official gold standard for Debian/Ubuntu PHP versions)
add-apt-repository -y ppa:ondrej/php
apt update

# Detect PHP version (mirrors wsl_install.sh and install_linux.sh logic):
#   1. Reuse an already-installed PHP in the 8.2-8.9 range.
#   2. Otherwise auto-install the newest fully-packaged PHP in 8.2-8.4 range.
PHP_VER="8.2"
EXISTING_PHP=""
for _v in $(ls /etc/php 2>/dev/null | grep -E '^8\.[2-9]$' | sort -V -r); do
    if dpkg -s "php${_v}-cli" >/dev/null 2>&1; then
        EXISTING_PHP="$_v"
        break
    fi
done
if [ -n "$EXISTING_PHP" ]; then
    PHP_VER="$EXISTING_PHP"
    echo "[INFO] Using already-installed PHP ${PHP_VER}"
else
    LATEST_PHP="$(apt-cache pkgnames | grep -E '^php8\.[2-4]-cli$' | sed -E 's/^php([0-9]+\.[0-9]+)-cli$/\1/' | sort -V | tail -n1)"
    if [ -n "$LATEST_PHP" ]; then
        PHP_VER="$LATEST_PHP"
        echo "[INFO] Found PHP ${PHP_VER} as latest available version"
    fi
fi

# Install PHP extensions (skip packages not available in the repo).
WANTED_PKGS=(
    "php${PHP_VER}-cli" "php${PHP_VER}-fpm" "php${PHP_VER}-pgsql" "php${PHP_VER}-intl"
    "php${PHP_VER}-mbstring" "php${PHP_VER}-curl" "php${PHP_VER}-xml" "php${PHP_VER}-zip"
    "php${PHP_VER}-gd" "php${PHP_VER}-opcache" "php${PHP_VER}-common"
)
PKGS_TO_INSTALL=()
for pkg in "${WANTED_PKGS[@]}"; do
    if apt-cache show "$pkg" >/dev/null 2>&1; then
        PKGS_TO_INSTALL+=("$pkg")
    else
        echo "[INFO] Skipping package '$pkg' (not found in apt repositories, possibly built-in)."
    fi
done
apt install -y "${PKGS_TO_INSTALL[@]}"

# Verify PHP installation
php -v

echo "=== [5/9] Installing Composer (PHP Dependency Manager) ==="
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
HASH="$(curl -sS https://composer.github.io/installer.sig)"
php -r "if (hash_file('sha384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('/tmp/composer-setup.php'); exit(1); }"
php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm /tmp/composer-setup.php
composer -V

echo "=== [6/9] Installing Nginx Web Server ==="
apt install -y nginx
systemctl enable nginx
systemctl start nginx

echo "=== [7/9] Setting Up Firewall (UFW) ==="
# Ensure SSH remains allowed so you don't lock yourself out!
ufw allow OpenSSH
# Allow web traffic (HTTP & HTTPS) - WebSocket traffic is securely reverse-proxied via Nginx /ws
ufw allow 'Nginx Full'
# Note: Raw WebSocket port 8081 is bound to 127.0.0.1 and not exposed publicly for security
# Enable firewall (non-interactively)
ufw --force enable
ufw status

echo "=== [8/9] Project Directory Setup ==="
# Create directory for project
mkdir -p /var/www/jeepneynvans
echo "Created directory /var/www/jeepneynvans"

echo "=== [9/9] Next Steps Checklist ==="
echo "─────────────────────────────────────────────────────────────────────────────"
echo " SUCCESS: Basic server stack (Nginx, PHP 8.2, PostgreSQL, Composer, UFW) set up!"
echo "─────────────────────────────────────────────────────────────────────────────"
echo "To complete your migration, follow these remaining steps on the server:"
echo ""
echo "1. Clone your project code into /var/www/jeepneynvans:"
echo "   sudo git clone <YOUR_REPO_URL> /var/www/jeepneynvans"
echo "   (or copy your files directly into this directory using SFTP/rsync)"
echo ""
echo "2. Install Composer dependencies:"
echo "   cd /var/www/jeepneynvans"
echo "   sudo composer install --no-dev --optimize-autoloader"
echo ""
echo "3. Setup file permissions so the web server can read/write data:"
echo "   sudo chown -R www-data:www-data /var/www/jeepneynvans"
echo "   sudo chmod -R 775 /var/www/jeepneynvans/writable"
echo ""
echo "4. Create the Database and User in PostgreSQL:"
echo "   sudo -u postgres psql -c \"CREATE DATABASE jeepneynvans;\""
echo "   sudo -u postgres psql -c \"CREATE USER jeepney_user WITH ENCRYPTED PASSWORD '12345678';\""
echo "   sudo -u postgres psql -c \"GRANT ALL PRIVILEGES ON DATABASE jeepneynvans TO jeepney_user;\""
echo ""
echo "5. Import your PostgreSQL schema and initial seeds:"
echo "   sudo -u postgres psql -d jeepneynvans -f /var/www/jeepneynvans/app/Database/postgres_schema.sql"
echo "   sudo -u postgres psql -d jeepneynvans -c \"GRANT ALL ON ALL TABLES IN SCHEMA public TO jeepney_user; GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO jeepney_user;\""
echo ""
echo "6. Setup your Environment Variables (.env):"
echo "   cd /var/www/jeepneynvans"
echo "   sudo cp env .env (if not present) and edit it:"
echo "   - Change 'CI_ENVIRONMENT' to 'production'"
echo "   - Set 'app.baseURL' to your domain or server IP (e.g. 'http://123.45.67.89/')"
echo "   - Verify 'database.default.DBDriver' is 'Postgre'"
echo "   - Verify 'database.default.port' is 5432"
echo "   - Verify 'database.default.schema' is 'public'"
echo ""
echo "7. Copy the Nginx configuration file:"
echo "   sudo cp /var/www/jeepneynvans/ubuntu_migration/nginx.conf /etc/nginx/sites-available/jeepneynvans"
echo "   sudo ln -s /etc/nginx/sites-available/jeepneynvans /etc/nginx/sites-enabled/"
echo "   sudo rm -f /etc/nginx/sites-enabled/default"
echo "   sudo nginx -t   # Test configuration"
echo "   sudo systemctl restart nginx"
echo ""
echo "8. Configure the systemd WebSocket daemon service:"
echo "   sudo cp /var/www/jeepneynvans/ubuntu_migration/jeepney-websocket.service /etc/systemd/system/jeepney-websocket.service"
echo "   sudo systemctl daemon-reload"
echo "   sudo systemctl enable jeepney-websocket.service"
echo "   sudo systemctl start jeepney-websocket.service"
echo "   sudo systemctl status jeepney-websocket.service"
echo "─────────────────────────────────────────────────────────────────────────────"
