# Jeepney nVans - Installation Guide (Windows 10/11 & Linux)

## Quick Start

### Windows 10/11 (Recommended: WSL 2 + Ubuntu)
1. Double-click **`install_system.bat`** (admin mode required)
   - Automatically installs WSL 2, Ubuntu, PHP 8.2–8.4 (auto-detected), Nginx, MariaDB, Composer
   - Takes 5-10 minutes on first run
2. After install, double-click **`start_system.bat`** to launch
3. Browser opens to `http://localhost/` automatically
4. To stop: double-click **`stop_system.bat`**

### Linux Mint / Ubuntu (native, no WSL)
```bash
cd ~/Downloads/jeepneynvans
sudo chmod +x install_linux.sh
sudo ./install_linux.sh
```
After install, manage services via (`8.x` = the PHP version the installer detected — check with `ls /run/php/`):
```bash
sudo systemctl restart nginx php8.x-fpm mariadb jeepney-websocket
sudo systemctl status nginx php8.x-fpm mariadb jeepney-websocket
```

---

## Verifying the Installation

After install, confirm everything is healthy:

- **Windows**: double-click **`VERIFY_SETUP.bat`** — every check should show ✓
- **Linux**: run **`bash VERIFY_SETUP.sh`** — every check should show ✓

The script checks PHP, Nginx, MariaDB (12 tables created), the `.env` configuration, Composer
packages, and the WebSocket service. Then open <http://localhost/> — you should see the live
terminal monitor. Log in with the seeded admin account (the username is in the `users` table).

Quick manual checks if a service looks down:

```bash
# Windows (WSL terminal)               # Linux (native)
wsl sudo service nginx status          sudo systemctl status nginx
wsl sudo service mariadb status        sudo systemctl status mariadb
wsl pgrep -f 'spark ws:serve'          systemctl status jeepney-websocket
```

---

## Cross-Platform Configuration

### Database Credentials (Windows & Linux)
```
Hostname: localhost (127.0.0.1)
Database: jeepneynvans
User:     jeepney_user
Password: 12345678
Port:     3306
```

### Application URL
- Windows (WSL):  `http://localhost/`
- Linux:          `http://localhost/` or `http://<your-LAN-IP>/`

### Environment File (.env)
Created automatically during install in the project root:
```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/'
database.default.hostname = 127.0.0.1
database.default.database = jeepneynvans
database.default.username = jeepney_user
database.default.password = 12345678
database.default.DBDriver = MySQLi
```

---

## Troubleshooting

### Windows (WSL)
- **WSL not available**: Windows 10 build 19041+ or Windows 11 required
- **502 Bad Gateway**: PHP-FPM socket mismatch. Re-run `start_system.bat`
- **Database connection error**: Check MariaDB is running: `wsl sudo service mariadb status`

### Linux
- **Permission denied**: Run `sudo chmod +x *.sh` first
- **Port 80 in use**: Another service is running on port 80. Stop it or change Nginx port
- **Database won't start**: Check MariaDB logs: `sudo journalctl -u mariadb -n 50`

### Both Platforms
- **"Unable to connect to database"**: Verify `.env` credentials match MariaDB setup
- **"baseURL is not a valid URL"**: Ensure `.env` has valid URL like `http://localhost/`

---

## Database Backup & Update

### Backup current database
```bash
# Windows (WSL terminal)
mysqldump -u jeepney_user -p12345678 jeepneynvans > ~/backup_$(date +%Y%m%d).sql

# Linux
mysqldump -u jeepney_user -p12345678 jeepneynvans > ~/backup_$(date +%Y%m%d).sql
```

### Restore from backup
```bash
mysql -u jeepney_user -p12345678 jeepneynvans < ~/backup_YYYYMMDD.sql
```

### Update with new SQL schema
```bash
mysql -u jeepney_user -p12345678 jeepneynvans < jeepneynvans.sql
```

---

## System Requirements

| Component | Windows | Linux |
|-----------|---------|-------|
| OS | Windows 10 (build 19041+) or 11 | Ubuntu 22.04+, Linux Mint 21+, Debian 11+ |
| RAM | 2GB minimum (WSL uses ~500MB) | 1GB minimum |
| Disk | 5GB free | 2GB free |
| Network | Port 80 available | Port 80 available |

---

## Starting the System

### Windows
- **Start**: Double-click `start_system.bat`
- **Stop**: Double-click `stop_system.bat`
- **Logs**: Check Windows Event Viewer or WSL `/var/log/nginx/`

### Linux
- **Start**: `sudo systemctl start nginx php8.x-fpm mariadb jeepney-websocket`
- **Stop**: `sudo systemctl stop nginx php8.x-fpm mariadb jeepney-websocket`
- **Status**: `systemctl status nginx` (shows 2 sec response time if healthy)
- **Logs**: `sudo tail -f /var/log/nginx/error.log`

---

## Email Configuration

Edit `.env` to enable contact form / feedback:
```ini
email.fromEmail = your.email@gmail.com
email.fromName = PTM System
email.recipients = support@example.com
email.SMTPUser = your.email@gmail.com
email.SMTPPass = your-app-password
```

For Gmail: generate an [App Password](https://myaccount.google.com/apppasswords) (requires 2FA).

---

## WebSocket Server (Real-time Queue Updates)

- **Windows (WSL)**: Starts automatically with `start_system.bat`
- **Linux**: Starts via systemd (`jeepney-websocket.service`)
- **Listen**: `ws://localhost:8081` (Nginx proxies to it)

Check status:
```bash
# Windows (WSL terminal)
wsl pgrep -f 'spark ws:serve'

# Linux
systemctl status jeepney-websocket
```

---

## Security Notes

- **Web root**: only the `public/` directory is exposed to the web server; `app/`, `system/`, and
  `.env` live outside the document root and are not reachable over HTTP.
- **Database user**: `jeepney_user` is bound to localhost — not exposed to the network.
- **Default admin password**: change the seeded admin credentials after first login.
- **Firewall (Linux)**: only port 80 (HTTP) needs to be open; the WebSocket ports (8081/8082) are
  used locally and proxied through Nginx.

---

## Development Notes

- **Project root**: Can live anywhere (no XAMPP directory requirement)
- **Nginx config**: Auto-detects and adapts to PHP version installed
- **Database**: Automatically created on first start with default credentials
- **Migrations**: Run via `php spark migrate` in project root
- **Logs**: `writable/logs/` (created automatically)

