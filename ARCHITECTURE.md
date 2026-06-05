# Jeepney nVans - Architecture & Cross-Platform Design

## Core Concept: One Codebase, Two Installation Paths

The project has **ONE** application codebase (`app/`, `system/`, `public/`, etc.) that runs identically on both platforms.

The **ONLY differences** are:
1. **How to install** (Windows uses .bat, Linux uses .sh)
2. **How to start/stop** (Windows uses .bat launchers, Linux uses systemd)
3. **Path handling** (Windows uses backslashes & WSL mounting, Linux uses forward slashes)

Everything else is **100% identical**.

---

## Installation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│ Project Files (Identical on both platforms)                     │
│ ├─ app/               (CodeIgniter application code)            │
│ ├─ public/            (Web root - served by Nginx)              │
│ ├─ system/            (CodeIgniter framework)                   │
│ ├─ vendor/            (Composer packages - installed once)      │
│ ├─ .env               (Configuration - auto-generated)          │
│ └─ jeepneynvans.sql   (Database schema - identical both)       │
└─────────────────────────────────────────────────────────────────┘
          ↓                                    ↓
    ┌──────────────────┐          ┌──────────────────────────┐
    │ WINDOWS SETUP    │          │ LINUX SETUP              │
    ├──────────────────┤          ├──────────────────────────┤
    │ install_system   │          │ install_linux.sh         │
    │    .bat          │          │ (runs as root)           │
    │ (requires admin) │          │ (installs system pkgs)   │
    │                  │          │                          │
    │ Installs:        │          │ Installs:                │
    │ • WSL 2 kernel   │          │ • apt packages           │
    │ • Ubuntu distro  │          │ • MariaDB                │
    │ • Inside Ubuntu: │          │ • PHP 8.x-FPM            │
    │   - MariaDB      │          │ • Nginx                  │
    │   - PHP 8.x-FPM  │          │ • Composer               │
    │   - Nginx        │          │ • WebSocket systemd      │
    │   - Composer     │          │                          │
    │ • PHP deps       │          │ Creates systemd services:│
    │                  │          │ • nginx                  │
    │ Creates .env     │          │ • php8.x-fpm            │
    │                  │          │ • mariadb                │
    │ Auto-starts via: │          │ • jeepney-websocket     │
    │ • start_system   │          │                          │
    │   .bat           │          │ Auto-starts on boot      │
    └──────────────────┘          └──────────────────────────┘
           ↓                              ↓
    ┌──────────────────┐          ┌──────────────────────────┐
    │ WINDOWS RUNTIME  │          │ LINUX RUNTIME            │
    ├──────────────────┤          ├──────────────────────────┤
    │ start_system.bat │          │ systemctl commands       │
    │ Starts:          │          │                          │
    │ • MariaDB        │          │ sudo systemctl start:    │
    │ • PHP-FPM        │          │ • nginx                  │
    │ • Nginx          │          │ • php8.x-fpm            │
    │ • WebSocket      │          │ • mariadb                │
    │                  │          │ • jeepney-websocket     │
    │ stop_system.bat  │          │                          │
    │ Stops all        │          │ sudo systemctl stop:     │
    │                  │          │ • jeepney-websocket     │
    │ Services run in  │          │ • nginx                  │
    │ WSL Ubuntu       │          │ • php8.x-fpm            │
    │                  │          │ • mariadb                │
    │ Browser: auto    │          │                          │
    │ opens localhost  │          │ Browser: manual          │
    │                  │          │ open http://localhost/   │
    └──────────────────┘          └──────────────────────────┘
           ↓                              ↓
    ┌──────────────────┐          ┌──────────────────────────┐
    │ Application      │          │ Application              │
    │ (Identical)      │          │ (Identical)              │
    │                  │          │                          │
    │ • Nginx serves   │          │ • Nginx serves           │
    │   public/        │          │   public/                │
    │ • PHP-FPM runs   │          │ • PHP-FPM runs           │
    │   app/           │          │   app/                   │
    │ • MariaDB at     │          │ • MariaDB at             │
    │   localhost      │          │   localhost              │
    │ • WebSocket at   │          │ • WebSocket at           │
    │   :8081          │          │   :8081                  │
    │                  │          │                          │
    │ http://localhost/            http://localhost/         │
    └──────────────────┘          └──────────────────────────┘
```

> **PHP version:** both installers auto-detect the newest packaged PHP in the 8.2–8.4 range, so
> the service unit is named `php8.x-fpm` where `8.x` is whatever was installed. Substitute the real
> version (find it with `ls /run/php/`) in any `systemctl` / `service` command below.

---

## Key Points: What's Different vs. What's Identical

### ✓ IDENTICAL (Same on both)
- **Application code** (`app/`, `system/`, `public/`)
- **Database schema** (`jeepneynvans.sql`)
- **Configuration structure** (`.env`)
- **Nginx route logic** (CodeIgniter URL rewriting)
- **PHP code** (all PHP files)
- **Database credentials** (same defaults)
- **WebSocket server** (same `php spark ws:serve`)

### ✗ DIFFERENT (Platform-specific)
- **Installer** (Windows: .bat, Linux: .sh)
- **Installation method** (Windows: WSL wrapper, Linux: apt-get)
- **Service management** (Windows: start_system.bat, Linux: systemctl)
- **File paths** (Windows: C:\Users\..., Linux: /home/...)
- **Path conversion** (Windows: wslpath conversion, Linux: direct paths)
- **Permission handling** (Windows: WSL user context, Linux: sudo)

---

## No Cross-Contamination

### Why Windows stuff doesn't affect Linux
- `.bat` files are Windows-only (Bash ignores them)
- `ubuntu_migration/wsl_install.sh` only runs inside WSL
- `install_linux.sh` only uses Linux apt-get commands
- Linux systemd is not installed in Windows WSL
- Windows paths (C:\) don't exist on Linux

### Why Linux stuff doesn't affect Windows
- `.sh` scripts need WSL to run (not part of Windows native)
- Linux systemd commands don't exist in Windows
- WSL is a separate environment - isolated from Windows file system
- Windows can't run `sudo` or `systemctl`

---

## Example: Installing on Both

### On Windows 10/11
```cmd
C:\Users\User\Downloads\jeepneynvans> install_system.bat
→ Detects Windows 10 build
→ Installs WSL 2 (if needed)
→ Installs Ubuntu (if needed)
→ Inside Ubuntu: apt-get install php nginx mariadb composer
→ Creates .env
→ Application ready
```

### On Linux
```bash
$ sudo ./install_linux.sh
→ Detects Linux (apt-get available)
→ apt-get install php nginx mariadb composer
→ systemctl enable nginx mariadb php8.x-fpm
→ Creates .env
→ Application ready
```

**Result**: Same application, different installation methods.

---

## Network Paths: Identical

Both platforms serve on:
- **Browser**: `http://localhost/`
- **Database**: `127.0.0.1:3306`
- **WebSocket**: `ws://localhost:8081` (proxied via Nginx)

No code changes needed between platforms.

---

## Example: A Single Request

**Request**: User opens `http://localhost/queue`

### Windows (WSL → Ubuntu → Nginx → PHP)
```
1. Browser makes HTTP request to localhost
2. Windows networking routes to WSL Ubuntu loopback
3. Nginx (running in Ubuntu) receives on 127.0.0.1:80
4. Nginx reads public/index.php
5. PHP-FPM (in Ubuntu) processes app/Controllers/Queue.php
6. Query runs against MariaDB (in Ubuntu)
7. Response sent back through same path
8. Browser displays queue UI
```

### Linux (Nginx → PHP → MariaDB)
```
1. Browser makes HTTP request to localhost
2. Native Linux networking routes to 127.0.0.1
3. Nginx (native) receives on 127.0.0.1:80
4. Nginx reads public/index.php
5. PHP-FPM (native) processes app/Controllers/Queue.php
6. Query runs against MariaDB (native)
7. Response sent back
8. Browser displays queue UI
```

**Code executed**: Identical. Path is different, but PHP code doesn't know or care.

---

## Configuration: Also Identical

The `.env` file is the same on both:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/'
database.default.hostname = 127.0.0.1
database.default.database = jeepneynvans
database.default.username = jeepney_user
database.default.password = 12345678
database.default.DBDriver = MySQLi
```

CodeIgniter reads these values the same way on both platforms.

---

## Summary

**One Application Codebase** + **Two Installation Methods** = **Works on Both**

- Windows users: Run `.bat` files → App works
- Linux users: Run `.sh` files → Same app works
- No code duplication
- No platform-specific PHP code
- No conditional logic ("if Windows then... if Linux then...")
- Just different installers for different operating systems

It's like how **Firefox** works on Windows, Mac, and Linux:
- Same browser application
- Different installer for each OS
- Identical user experience
- Identical feature set

