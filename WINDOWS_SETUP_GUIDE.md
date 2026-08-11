# Windows 10/11 Setup Guide - Palompon Transit System

This guide will help you get the system running on **Windows 10 (Build 19041+)** or **Windows 11**.

---

## ⚠️ REQUIREMENTS

Your project folder must be:
- **NOT** in a network drive or VirtualBox shared folder
- Ideally in `C:\jeepneynvans` (or similar direct local disk location)

Windows build:
- **Windows 10**: Build 19041 or newer (check Settings → System → About)
- **Windows 11**: Any version

---

## 🔧 STEP 1: Check/Install WSL 2

**Option A: Automatic (Recommended)**

1. Double-click or Right-click **`install_system.bat`** → **Run as Administrator**
2. Follow the prompts
3. If it asks to **reboot**, do so — WSL2 hypervisor needs it to load.
4. After reboot, run `install_system.bat` again to continue and complete the stack installation.

**Option B: Manual Check**

Open **PowerShell as Administrator** and run:

```powershell
wsl --version
```

- **If you see a version number**: WSL is installed ✓ (skip to Step 2)
- **If "command not found"**: Run:
  ```powershell
  wsl --install
  ```
  Then **reboot your PC** and come back.

---

## 🚀 STEP 2: Start the System

1. **Double-click `start_system.bat`** in your project folder
2. **Wait 10-15 seconds** (it's detecting your PHP version and starting services)
3. A **browser window opens automatically** to `http://localhost/`

✓ **If you see the Palompon Transit login page** → You're done! 🎉

### If the automatic browser didn't open:

Open your browser and go to: **`http://localhost/`**

---

## ❌ TROUBLESHOOTING

### Problem 1: "This page can't be reached" or Connection Refused

**Fix:**

1. Open **PowerShell** (NOT as admin)
2. Run this diagnostic check:
   ```powershell
   wsl ls -la /mnt/c/jeepneynvans/
   ```
   - Replace `/mnt/c/jeepneynvans/` with wherever your project folder is located
   - If you see files listed → Good ✓
   - If "not found" → Your project path is incorrect or WSL cannot access it.

3. Edit `start_system.bat` to verify the path
   - Verify the location in `wsl_local_up.sh` that says:
     ```bash
     PROJECT_ROOT="${1:-/mnt/c/xampp2/htdocs/jeepneynvans}"
     ```
   - If needed, change it to match your actual path (keep `/mnt/c/` prefix)
   - Example: `/mnt/c/Users/Anfel/Downloads/jeepneynvans`

---

### Problem 2: Nginx/PHP-FPM Not Starting

**Check services are running:**

Open **PowerShell** and run:

```powershell
wsl -u root systemctl status nginx
wsl -u root systemctl status php-fpm
```
*(You can query the versioned PHP FPM service status by replacing `php-fpm` with `php8.2-fpm`, `php8.3-fpm`, etc., depending on the installed PHP version).*

**If services failed to start:**

1. Run the diagnostic tool (`diagnose_windows.bat`)
2. Check for **port 80 conflicts** (another app like Skype, IIS, or Apache using localhost:80)

---

### Problem 3: Database Not Importing

Check if SQL dump exists:

```powershell
wsl ls -la /mnt/c/jeepneynvans/*.sql
```

Should show `jeepneynvans.sql` or `jeepneynvans_clean.sql`. If missing, the database won't import.

---

## 🔍 DIAGNOSTIC SCRIPT

**Run this PowerShell script to debug everything:**

```powershell
# Save as: check_wsl_setup.ps1, then run: powershell -ExecutionPolicy Bypass -File check_wsl_setup.ps1

Write-Host "=== Palompon Transit - Windows WSL Diagnostic ===" -ForegroundColor Cyan
Write-Host ""

# Check WSL version
Write-Host "[1] WSL Version:" -ForegroundColor Yellow
wsl --version 2>$null || Write-Host "WSL not installed!" -ForegroundColor Red
Write-Host ""

# Check Ubuntu/Distro
Write-Host "[2] Installed Distros:" -ForegroundColor Yellow
wsl -l -v 2>$null || Write-Host "No distros found!" -ForegroundColor Red
Write-Host ""

# Check services in WSL
Write-Host "[3] Services Status (in WSL):" -ForegroundColor Yellow
wsl -u root systemctl status nginx 2>&1 | findstr "active"
wsl -u root bash -lc "PHPVER=\$(ls /etc/php 2>/dev/null | grep -E '^[0-9]+\.[0-9]+$' | sort -V | tail -n1); [ -z \"\$PHPVER\" ] && PHPVER=\"8.2\"; systemctl status php\${PHPVER}-fpm" 2>&1 | findstr "active"
wsl -u root systemctl status mysql 2>&1 | findstr "active" || wsl -u root systemctl status mariadb 2>&1 | findstr "active"
Write-Host ""

# Check localhost connectivity
Write-Host "[4] Localhost Accessibility:" -ForegroundColor Yellow
$response = Invoke-WebRequest -Uri "http://localhost/" -ErrorAction SilentlyContinue
if ($response.StatusCode -eq 200) {
    Write-Host "✓ http://localhost/ is accessible" -ForegroundColor Green
} else {
    Write-Host "✗ http://localhost/ connection failed" -ForegroundColor Red
}
Write-Host ""

# Check ports
Write-Host "[5] Listening Ports (Windows):" -ForegroundColor Yellow
netstat -ano | findstr "LISTENING" | findstr ":80\|:443\|:3306\|:8081\|:8082"
```

---

## 📝 Project Path Examples

| Location | WSL Path |
|----------|----------|
| `C:\jeepneynvans` | `/mnt/c/jeepneynvans` |
| `C:\Users\Anfel\Downloads\jeepneynvans` | `/mnt/c/Users/Anfel/Downloads/jeepneynvans` |
| `C:\xampp\htdocs\jeepneynvans` | `/mnt/c/xampp/htdocs/jeepneynvans` |

**To find your actual path in PowerShell:**

```powershell
$projectPath = Get-Item (pwd) | Select-Object -ExpandProperty FullName
Write-Host "Your Windows path: $projectPath"
wsl wslpath "$projectPath"  # Shows the WSL equivalent
```

---

## 🎯 Quick Commands

| What you need | Command |
|--------------|---------|
| Start all services | Run `start_system.bat` |
| Stop all services | Run `stop_system.bat` |
| View Nginx errors | `wsl -u root tail -f /var/log/nginx/jeepney_local_error.log` |
| View PHP-FPM errors | `wsl -u root tail -f /var/log/php-fpm.log` |
| Restart everything | `wsl -u root service nginx restart` |
| Check MariaDB | `wsl -u root mysql -u root` |

---

## ✅ Next Steps After Setup

1. **Login**: Use credentials from `INSTALLATION_GUIDE.md` (`admin123` / `admin123`)
2. **WebSocket**: Queue updates should appear **in real-time** across all open pages.
3. **If something breaks**: Delete the distro using `wsl --unregister Ubuntu` and rerun `install_system.bat` to start clean.

---

## 🆘 Still Having Issues?

Check these files in the project:
- `WEBSOCKET_SETUP.md` - Real-time queue updates
- `INSTALLATION_GUIDE.md` - Default credentials
- `ARCHITECTURE.md` - System overview

Or run the diagnostic script above or `diagnose_windows.bat` and review the output!
