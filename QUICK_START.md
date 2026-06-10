# 🎯 Jeepney nVans - Quick Start Checklist

## ✅ Pre-Installation Checklist

### Windows 10/11
- [ ] Windows 10 build 19041+ or Windows 11 (check: Settings → System → About → Build)
- [ ] Administrator account (needed for WSL installation)
- [ ] Internet connection (for downloads)
- [ ] 5GB free disk space
- [ ] Port 80 not in use (no other web server)

### Linux (Mint, Ubuntu, Debian)
- [ ] Linux kernel 4.19+ (modern versions all have this)
- [ ] `sudo` access
- [ ] Internet connection
- [ ] 2GB free disk space
- [ ] Port 80 not in use

---

## 🚀 Installation Steps

### Windows
1. [ ] Extract project folder anywhere
2. [ ] Right-click `install_system.bat` → Run as Administrator
3. [ ] Wait for automatic WSL, Ubuntu, PHP, Nginx, MariaDB installation (5-10 min)
4. [ ] At end, press any key to continue
5. [ ] Double-click `start_system.bat`
6. [ ] Browser should auto-open to `http://localhost/`

**Result**: Everything should be running. Continue to "Post-Installation".

### Linux
1. [ ] Open terminal in project folder
2. [ ] Run: `sudo chmod +x install_linux.sh`
3. [ ] Run: `sudo ./install_linux.sh`
4. [ ] Wait for packages and setup (2-5 min depending on internet)
5. [ ] At end, press Enter to continue

**Result**: Services installed and auto-started. Continue to "Post-Installation".

---

## ✅ Post-Installation Checklist

### Both Platforms
1. [ ] Open browser and go to `http://localhost/`
2. [ ] You should see the Jeepney nVans login page
3. [ ] Dashboard appears (may have loading delays on first access)

### Windows Only
- [ ] `start_system.bat` is running (command window visible)
- [ ] To stop: Double-click `stop_system.bat`
- [ ] To start again: Double-click `start_system.bat`

### Linux Only
- [ ] Check services are running: `sudo systemctl status nginx php8.2-fpm mariadb jeepney-websocket`
- [ ] To stop services: `sudo systemctl stop nginx php8.2-fpm mariadb jeepney-websocket`
- [ ] To start services: `sudo systemctl start nginx php8.2-fpm mariadb jeepney-websocket`

---

## 🧪 Verification

Run this to verify everything is working:

### Windows
Double-click `VERIFY_SETUP.bat` and check all items show ✓

### Linux
```bash
bash VERIFY_SETUP.sh
```

Expected output: All checks should show ✓

---

## 📧 Optional: Email Setup (Contact Form)

Edit `.env` in the project root and add:

```ini
email.fromEmail = your.gmail@gmail.com
email.fromName = PTM System
email.recipients = support@yourcompany.com
email.SMTPUser = your.gmail@gmail.com
email.SMTPPass = your-app-password
```

For Gmail users:
1. Go to [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
2. Enable 2-Step Verification first
3. Generate an App Password
4. Copy-paste that password into `.env` as `email.SMTPPass`
5. Restart web services (or just reload the app)

---

## 🔄 Updating the Database

If you have a newer SQL file:

```bash
# Backup current database
mysqldump -u jeepney_user -p12345678 jeepneynvans > ~/backup_$(date +%Y%m%d).sql

# Import new schema
mysql -u jeepney_user -p12345678 jeepneynvans < /path/to/new_file.sql
```

---

## 🛠️ Common Tasks

### Check if Nginx is running
```bash
# Windows (WSL terminal)
wsl sudo service nginx status

# Linux
sudo systemctl status nginx
```

### Restart everything
```bash
# Windows: Double-click stop_system.bat, then start_system.bat

# Linux
sudo systemctl restart nginx php8.2-fpm mariadb jeepney-websocket
```

### View error logs
```bash
# Windows (WSL terminal)
wsl sudo tail -f /var/log/nginx/error.log

# Linux
sudo tail -f /var/log/nginx/error.log
```

### Reset database (keep same structure)
```bash
mysql -u jeepney_user -p12345678 jeepneynvans -e "SET FOREIGN_KEY_CHECKS=0; TRUNCATE users; TRUNCATE terminals; TRUNCATE routes; SET FOREIGN_KEY_CHECKS=1;"
```

---

## ❌ Troubleshooting

| Problem | Solution |
|---------|----------|
| "Connection refused" | Check MariaDB is running |
| "502 Bad Gateway" | Restart PHP-FPM |
| "Port 80 already in use" | Stop other web servers (Apache, IIS, etc.) |
| "Cannot connect to database" | Verify username/password in `.env` |
| "White screen or error page" | Check logs in `writable/logs/` |
| "WebSocket not working" | Restart the WebSocket service (systemctl restart jeepney-websocket) |

---

## 📞 Need Help?

1. Check `INSTALLATION_GUIDE.md` for detailed steps
2. Run `VERIFY_SETUP.sh` or `VERIFY_SETUP.bat` to diagnose issues
3. Check logs in `writable/logs/` for error details
4. Look at `DEPLOYMENT_STATUS.md` for system requirements

---

**Status**: ✅ Ready to use on Windows 10/11 and Linux (Mint, Ubuntu, Debian)
