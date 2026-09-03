# ⚡ Palompon Transit - Windows 10/11 Quick Start

**Your project is in**: `c:\jeepneynvans` (or wherever your workspace folder is located)

---

## 🚀 FASTEST WAY TO GET IT RUNNING

### Step 1: Double-click or Run
Double-click **`start_system.bat`** (Run as Administrator is recommended if services need configuring)

### Step 2: Wait 10-15 seconds
Let it detect your PHP version and start services. You'll see messages like:
```
[OK] Using PHP 8.2 (or 8.3 / 8.4)
[OK] Project folder: /mnt/c/jeepneynvans
```

### Step 3: Browser opens automatically
If browser **doesn't** open, manually go to: **`http://localhost/`**

✅ **You should see the Palompon Transit login page**

---

## ❌ If You See "This page can't be reached"

Run the **diagnostic tool** to see what's wrong:
```
Double-click: diagnose_windows.bat
```

This will check:
- ✓ WSL is installed
- ✓ Services are running (Nginx, PHP-FPM, PostgreSQL)
- ✓ Your project path is correct
- ✓ Port 80 is accessible
- ✓ WebSockets process status

---

## 🔴 Common Issues

| Issue | Fix |
|-------|-----|
| **WSL not installed** | Run `install_system.bat` as Admin, then **reboot** |
| **"wsl: command not found"** | Windows build too old. Upgrade to Windows 10 Build 19041+ |
| **Nginx won't start** | Another app using port 80. Stop it: `netstat -ano \| findstr ":80"` |
| **Database not found** | Make sure `app/Database/postgres_schema.sql` exists |
| **PHP version error** | Delete WSL: `wsl --unregister Ubuntu`, then rerun `install_system.bat` |

---

## 📋 Login Credentials

Once the site loads:

```
Super Admin:
  Username: admin
  Password: admin123

Staff (Dispatcher):
  Username: staff
  Password: staff123
```

(See `INSTALLATION_GUIDE.md` for more details)

---

## 🎯 Common Commands

| Task | Command |
|------|---------|
| **Start** | Double-click `start_system.bat` |
| **Stop** | Double-click `stop_system.bat` |
| **Check logs** | Run `diagnose_windows.bat` |
| **Reset everything** | Run `install_system.bat` |

---

## 📚 Full Setup Guide

For advanced topics, see: **`WINDOWS_SETUP_GUIDE.md`**

---

## 🆘 Still Stuck?

1. **Run the diagnostic**:
   ```
   Double-click: diagnose_windows.bat
   ```

2. **Check these logs in WSL**:
   ```powershell
   wsl -u root tail -f /var/log/nginx/jeepney_local_error.log
   ```

3. **See detailed guide**: Open `WINDOWS_SETUP_GUIDE.md`

---

**That's it!** You should be running in 2-3 clicks. 🎉
