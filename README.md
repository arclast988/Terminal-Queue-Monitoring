# Jeepney NVans — Palompon Transit Management System

A CodeIgniter 4 web app for managing van/jeepney terminal queues, routes, vehicle dispatch, and admin audit logs.

## Setup (Windows + WSL Ubuntu, Nginx — no XAMPP)

This project runs on **Nginx + PHP-FPM + MariaDB** inside **WSL (Ubuntu)** — no XAMPP/Apache.

### Prerequisites
- **WSL 2** with Ubuntu, and inside it: **PHP 8.2+** (CLI + FPM), **Nginx**, and **MariaDB**.
  - Need to install them? Double-click `install_system.bat` — one-time Windows installer that sets up WSL, Ubuntu, the full LAMP stack, Composer, and the project's PHP dependencies. (For production Ubuntu servers, see `ubuntu_migration/deploy.sh` instead.)
- **Git**.

### Local quick start (Windows)
1. **Clone into any folder** (e.g. `C:\projects\jeepneynvans`) — keep the folder name `jeepneynvans`. The launcher auto-detects its own location, so it does **not** need to live in XAMPP's `htdocs`; you can move it out and uninstall XAMPP entirely.
2. **Create your `.env`** — copy the bundled template:
   ```
   copy env .env
   ```
   The launcher's defaults already match (DB user `jeepney_user` / `12345678`); only edit `.env` to change credentials or enable email.
3. **Double-click `start_system.bat`.** It runs everything inside WSL:
   - detects your installed PHP version and starts MariaDB, PHP-FPM, and Nginx,
   - auto-creates the database and `jeepney_user`, importing `jeepneynvans.sql` if the tables are missing,
   - starts the real-time WebSocket server,
   - waits for Nginx, then opens the app.
4. **Open the app:** <http://localhost/>
5. **Sign in** with a seeded account from the dump (usernames are in the `users` table; ask the project owner for the password).
6. **Stopping the system** — double-click `stop_system.bat` to shut down MariaDB, PHP-FPM, Nginx, and the WebSocket server. Database data is preserved.

### Local quick start (from inside a WSL terminal)
If you're already at a WSL bash prompt (e.g. `jaylo@DESKTOP-XXXX:~$`), use the bash wrappers instead of the `.bat` files:
```
cd /mnt/c/path/to/jeepneynvans
./start_system.sh     # bring everything up (auto-elevates with sudo)
./stop_system.sh      # shut everything down
```
Both scripts auto-detect the project root, sanity-check that they're running in WSL, and reuse the same Linux-side logic (`ubuntu_migration/wsl_local_up.sh` / `wsl_local_down.sh`) as the `.bat` launchers — so behavior and output are identical. First run may need `chmod +x start_system.sh stop_system.sh`.

### Database
The app's defaults live in `app/Config/Database.php` and are created automatically by `start_system.bat`:
```
hostname = 127.0.0.1
database = jeepneynvans
username = jeepney_user
password = 12345678
port     = 3306
```
To use different credentials, set the matching `database.default.*` keys in `.env`.

#### Empty database via migrations (no sample data)
From the project root inside WSL: `php spark migrate`, then create a first admin user (via a seeder or manually).

### Real-time WebSocket features
Queue auto-refresh uses a WebSocket server that `start_system.bat` starts for you. See **[WEBSOCKET_SETUP.md](WEBSOCKET_SETUP.md)** for details. The app still works without it — pages fall back to polling.

### Production (Ubuntu server)
Provision a server with `ubuntu_migration/deploy.sh`, then follow its printed checklist and **[WEBSOCKET_SETUP.md](WEBSOCKET_SETUP.md)** to deploy the app, the Nginx site, and the WebSocket systemd service.

### Troubleshooting
- **502 Bad Gateway** → PHP-FPM isn't running or its socket version doesn't match Nginx; re-run `start_system.bat`, or check `wsl ss -ltn` and `ls /run/php/`.
- **"Unable to connect to the database"** → MariaDB isn't started (`wsl sudo service mariadb start`), or `.env` credentials don't match.
- **Real-time not updating** → run `diagnose.ps1` (checks Nginx, the WebSocket server, and `ws_server.pid`).
- **Login fails** → confirm the import created the `users` table with at least one admin row.

---

# CodeIgniter 4 Framework

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds the distributable version of the framework.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Contributing

We welcome contributions from the community.

Please read the [*Contributing to CodeIgniter*](https://github.com/codeigniter4/CodeIgniter4/blob/develop/CONTRIBUTING.md) section in the development repository.

## Server Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - The end of life date for PHP 8.1 was December 31, 2025.
> - If you are still using below PHP 8.2, you should upgrade immediately.
> - The end of life date for PHP 8.2 will be December 31, 2026.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
