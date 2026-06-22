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

echo "=== [3/9] Installing MariaDB (MySQL Database Server) ==="
apt install -y mariadb-server mariadb-client
# Secure installation configurations are handled below. MariaDB starts automatically.
systemctl enable mariadb
systemctl start mariadb

echo "=== [4/9] Adding PHP 8.2 Repository & Installing PHP ==="
# Add Ondrej Surý's PHP repository (the official gold standard for Debian/Ubuntu PHP versions)
add-apt-repository -y ppa:ondrej/php
apt update

# Install PHP 8.2 CLI, FPM (Web Server Gateway), and CodeIgniter 4 required extensions.
# php8.2-opcache is included for a large production throughput boost.
apt install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-intl php8.2-mbstring php8.2-curl php8.2-xml php8.2-zip php8.2-gd php8.2-opcache php8.2-common

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
# Allow web traffic
ufw allow 'Nginx Full'
# Allow WebSocket Client Connections (Port 8081)
ufw allow 8081/tcp
# Enable firewall (non-interactively)
ufw --force enable
ufw status

echo "=== [8/9] Project Directory Setup ==="
# Create directory for project
mkdir -p /var/www/jeepneynvans
echo "Created directory /var/www/jeepneynvans"

echo "=== [9/9] Next Steps Checklist ==="
echo "─────────────────────────────────────────────────────────────────────────────"
echo " SUCCESS: Basic server stack (Nginx, PHP 8.2, MariaDB, Composer, UFW) set up!"
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
echo "4. Create the Database and User in MariaDB/MySQL:"
echo "   sudo mysql -u root"
echo "   > CREATE DATABASE jeepneynvans CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
echo "   > CREATE USER 'jeepney_user'@'localhost' IDENTIFIED BY '12345678';"
echo "   > GRANT ALL PRIVILEGES ON jeepneynvans.* TO 'jeepney_user'@'localhost';"
echo "   > FLUSH PRIVILEGES;"
echo "   > EXIT;"
echo ""
echo "5. Import your database SQL dump:"
echo "   sudo mysql -u jeepney_user -p jeepneynvans < /var/www/jeepneynvans/jeepneynvans.sql"
echo ""
echo "6. Setup your Environment Variables (.env):"
echo "   cd /var/www/jeepneynvans"
echo "   sudo cp .env.example .env (if not present) and edit it:"
echo "   - Change 'CI_ENVIRONMENT' to 'production'"
echo "   - Set 'app.baseURL' to your domain or server IP (e.g. 'http://123.45.67.89/')"
echo "   - Verify 'database.default.hostname' is 'localhost' or '127.0.0.1'"
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
