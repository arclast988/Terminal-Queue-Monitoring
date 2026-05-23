# Jeepney NVans — Palompon Transit Management System

A CodeIgniter 4 web app for managing van/jeepney terminal queues, routes, vehicle dispatch, and admin audit logs.

## Quick Setup (Windows + XAMPP)

### Prerequisites
- XAMPP with **PHP 8.2+** (the project is tested on PHP 8.2.12) and **MySQL / MariaDB**.
- **Git**.

### Steps
1. **Clone into your XAMPP `htdocs` folder** (usually `C:\xampp\htdocs` or `C:\xampp2\htdocs`):
   ```
   cd C:\xampp\htdocs
   git clone <repo-url> jeepneynvans
   ```
   The folder name must be `jeepneynvans` so URLs match `.env`'s `app.baseURL`.

2. **Start Apache and MySQL** in the XAMPP Control Panel.

3. **Create the database**:
   - Open phpMyAdmin: <http://localhost/phpmyadmin>.
   - Click *New* and create a database named exactly **`jeepneynvans`** (collation `utf8mb4_general_ci`).

4. **Import the SQL dump**:
   - Select the new `jeepneynvans` database.
   - Click *Import* → *Choose File* → pick **`jeepneynvans.sql`** from the project root.
     - Don't use `app/Database/announcements_table.sql` — that's a helper for one table only.
     - The `jeepneynvans (5).sql` file is a local backup and is not in GitHub.
   - Click *Go*. This creates all tables — including `audit_logs` — and loads sample users, routes, vehicles, and queue history.

5. **Open the app**: <http://localhost/jeepneynvans/public/>

6. **Sign in** using the seeded admin account from the dump. Usernames live in the `users` table; ask the project owner for the password if it isn't already shared.

### Alternative: empty database via migrations
If you want a blank database (no sample data) instead of importing the SQL dump:
1. Create the empty `jeepneynvans` database in phpMyAdmin.
2. From the project root, run:
   ```
   C:\xampp\php\php.exe spark migrate
   ```
   Adjust the path to wherever XAMPP put PHP (e.g., `C:\xampp2\php\php.exe`).
3. Create a first admin user manually via phpMyAdmin or by editing a seeder.

### Database config
The repo ships an `.env` with XAMPP defaults:
```
database.default.hostname = localhost
database.default.database = jeepneynvans
database.default.username = root
database.default.password =
database.default.port     = 3306
```
If your MySQL has a root password, edit `.env` accordingly.

### Real-time WebSocket features (optional)
The queue auto-refresh uses a WebSocket server. See **[WEBSOCKET_SETUP.md](WEBSOCKET_SETUP.md)** for how to run it (double-click `run_ws.bat` or use `php spark ws:serve`). The app still works without WebSocket — pages just fall back to polling.

### Troubleshooting
- **"Unable to connect to the database"** → MySQL isn't started in XAMPP Control Panel.
- **Blank page / 404** → the project folder must be named `jeepneynvans` and live under `htdocs`, and you must visit `/jeepneynvans/public/` (not `/jeepneynvans/`).
- **Login fails** → confirm the import created the `users` table with at least one admin row (phpMyAdmin → `users` → *Browse*).

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
