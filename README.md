# Jeepney NVans — Palompon Transit Management System

## System Documentation & User Manuals

Official system manuals and usability guides are maintained in the [`docs/`](file:///c:/jeepneynvans/docs) directory:

- 📘 [**Administrator & Super Admin User Manual**](file:///c:/jeepneynvans/docs/USER_MANUAL_ADMIN.md) — Comprehensive guide to fleet registration, route & fare matrix setup, departure headway rules, user permissions, announcements, and audit trails.
- 📋 [**Dispatcher & Terminal Staff User Manual**](file:///c:/jeepneynvans/docs/USER_MANUAL_DISPATCHER.md) — Step-by-step terminal queue operations, FIFO entry, 30-minute departure cooldown enforcement, boarding progression, passenger counting, and 1-click Undo Cancel.
- 🚏 [**Commuter & Passenger User Guide**](file:///c:/jeepneynvans/docs/USER_MANUAL_COMMUTER.md) — Commuter guide for live terminal monitoring, seat availability progress, 20% statutory discount rules, daily schedules, and departure search.
- 🛡️ [**Error Recognition, Diagnosis & Recovery Guide**](file:///c:/jeepneynvans/docs/ERROR_RECOGNITION_DIAGNOSIS_RECOVERY_GUIDE.md) — Deep-dive usability manual detailing plain-language diagnostics, error prevention guardrails, 1-click Undo Cancel, cooldown timers, `no-change-guard.js`, and WebSocket/HTTP failover.

Interactive, clickable versions of these manuals are also directly accessible inside the web application at `/admin/manual`, `/staff/manual`, and `/manual`.

## Setup (Ubuntu Server / Linux & Windows WSL — Nginx + PHP-FPM + PostgreSQL, no XAMPP)

This project runs on **Nginx + PHP-FPM + PostgreSQL** — pure enterprise-grade stack with zero XAMPP/Apache dependency.

### Prerequisites
- Ubuntu Server or WSL 2 with: **PHP 8.2+** (CLI + FPM with `php-pgsql`), **Nginx**, and **PostgreSQL**.
  - Need to install them? Run `sudo ./install_linux.sh` on native Linux or double-click `install_system.bat` on Windows — sets up the full stack, Composer, and PostgreSQL database.
- **Git**.

### Local quick start (Windows)
1. **Clone into any folder** (e.g. `C:\projects\jeepneynvans`) — keep the folder name `jeepneynvans`.
2. **Create your `.env`** — copy the bundled template:
   ```
   copy env .env
   ```
   The launcher's defaults already match (DB user `jeepney_user` / `12345678`, PostgreSQL port `5432`).
3. **Double-click `start_system.bat`.** It runs everything inside WSL:
   - detects your installed PHP version and starts PostgreSQL, PHP-FPM, and Nginx,
   - auto-creates the PostgreSQL database and `jeepney_user`, importing `app/Database/postgres_schema.sql` if tables are missing,
   - starts the real-time WebSocket server,
   - waits for Nginx, then opens the app.
4. **Open the app:** <http://localhost/>
5. **Sign in** with a seeded account (`admin` / `admin123`, `staff` / `staff123`), or with official seeder accounts (`admin@ttm.local` / `admin123`, `staff@ttm.local` / `staff123`).
6. **Stopping the system** — double-click `stop_system.bat` to shut down PostgreSQL, PHP-FPM, Nginx, and the WebSocket server. Database data is preserved.

### Local quick start (from inside a WSL terminal)
If you're already at a WSL bash prompt (e.g. `user@DESKTOP:~$`), use the bash wrappers instead of the `.bat` files:
```
cd /mnt/c/path/to/jeepneynvans     # wherever you cloned it
./start_system.sh     # bring everything up (auto-elevates with sudo)
./stop_system.sh      # shut everything down
```

### Linux quick start (Ubuntu Server / Debian / Mint — native, no WSL)
On a Linux server, one script installs **and** starts everything (as systemd services that auto-start on boot):
1. **Clone the project:**
   ```
   cd ~
   git clone https://github.com/jaylocano-stack/Capstone-Project.git jeepneynvans
   cd jeepneynvans
   ```
2. **Run the one-shot installer** — installs PHP + Nginx + PostgreSQL + Composer, creates and seeds the PostgreSQL database, sets up the WebSocket service, and starts it all:
   ```
   sudo ./install_linux.sh
   ```
   (If you get "permission denied", run `chmod +x install_linux.sh` first.)
3. **Open the app:** <http://localhost/>
4. **Manage it afterwards** (no launcher needed — it runs on boot):
   ```
   sudo systemctl status  nginx php<VERSION>-fpm postgresql jeepney-websocket
   sudo systemctl restart nginx php<VERSION>-fpm postgresql jeepney-websocket   # or stop / start
   ```

### Database (PostgreSQL)
The app's defaults live in `app/Config/Database.php` and `.env`:
```
hostname = 127.0.0.1
database = jeepneynvans
username = jeepney_user
password = 12345678
driver   = Postgre
port     = 5432
schema   = public
```

### Real-time WebSocket features
Queue auto-refresh uses a WebSocket server that `start_system.bat` starts for you. See **[WEBSOCKET_SETUP.md](WEBSOCKET_SETUP.md)** for details. The app still works without it — pages fall back to polling.

### Troubleshooting
- **502 Bad Gateway** → PHP-FPM isn't running or its socket version doesn't match Nginx; re-run `start_system.bat`, or check `sudo systemctl status php-fpm`.
- **"Unable to connect to the database"** → PostgreSQL isn't started (`sudo systemctl status postgresql`), or `.env` credentials don't match.
- **Real-time not updating** → run `VERIFY_SETUP.bat` or `diagnose_windows.bat` (checks Nginx proxy, ports 8081/8082, and WebSocket server daemon).
- **Login fails** → confirm the import created the `users` table with default accounts (`admin` / `admin123` or seeded `admin@ttm.local` / `admin123`).

### Security, Performance & Code Optimization Updates (June 2026)

We recently performed a system-wide audit and optimization:
- **Hierarchical Role Control & Super Admin Role**: Introduced a three-tier role hierarchy (`super_admin`, `admin`, and `staff`). Only a `super_admin` can manage other admin accounts and bypass all role gates. Demotion or transfer of the super admin role is restricted to a secure CLI command: `php spark admin:transfer-super-admin [user_id]`.
- **Hardened OTP Authentication**: Replaced `mt_rand()` with secure `random_int()`, added a 60-second rate limiter for OTP requests, and implemented a 5-attempt threshold limit that invalidates compromised OTP verification sessions.
- **Login Rate-Limiting & Lockouts**: Added per-user and per-IP login rate limiting that locks accounts and IP-based authentication for 15 minutes after 5 consecutive failed attempts.
- **Client-Side XSS Protection**: Integrated DOMPurify to sanitize all dynamic HTML updates pushed via WebSocket queue syncs to prevent cross-site scripting (XSS) attacks.
- **Dynamic PHP Version Support**: De-hardcoded specific PHP versions. The automated installers and services now dynamically auto-detect and support any installed version from PHP 8.2 up to PHP 8.4.
- **Enhanced Database Query Performance**:
  - Eliminated N+1 queries on the Admin Dashboard using aggregate joins.
  - Consolidated multiple sequential history counting queries into a single conditional SQL aggregation query.
  - Optimized discount fare calculation loading loops via batch-fetching mapping arrays.
  - Replaced high-overhead queries in audit logs with proper CodeIgniter pagination.
- **Cache Control & Real-Time Sync**: Implemented caching for local fares APIs (`rt_fares_api`) with automatic invalidate triggers and added a `sync_token` field to verify payload currency.
- **Code Redundancy Cleanup**: Introduced model-level scopes (`withOrigin()` and `withFullJoins()`) to consolidate database joins and standardized active announcement retrieval inside `BaseController.php`.
- **WSL-Safe File Logging**: Created a custom `WslFileHandler` to suppress chmod permission warnings when writing logs on WSL mounted NTFS drives (DrvFs).
- **Global Filter Optimization**: Removed redundant filters (`forcehttps`, `pagecache`) to eliminate per-request redirect and caching overhead.
- **Vehicle Image Centralization**: Eliminated duplicate vehicle type-to-image mapping blocks across views via a central `vehicle_type_image()` helper in `app/Common.php`.
- **Database Query Portability**: Refactored MySQL-specific date functions (`YEAR()`, `DATE()`, `CURDATE()`) using standard SQL comparison and `LIKE` queries to make the application fully compatible with SQLite3 (used for local unit testing) and MySQL.

### Real-Time, Performance & Health Audit Updates (September 2026)
- **Unblocked WebSocket Broadcast Delivery**: Removed brittle `ws_server.pid` file dependencies in `BaseController::broadcastUpdate()`, establishing reliable direct loopback broadcasts with non-blocking socket timeouts.
- **Race Condition Elimination**: Deferred queue broadcasts in `Staff/Queue.php` until after PostgreSQL transaction commits, preventing clients from fetching and caching stale queue states.
- **Polling Throttling & Bandwidth Reduction**: Paused 3-second HTTP polling in `queue-sync.js` while WebSocket connections are active, and reduced announcement marquee polling from 3s to 30s with a 300s cache TTL.
- **Database Caching & Midnight Rule Gap**: Cached `get_db_vehicle_types()` with 1-hour TTL to eliminate continuous `information_schema` queries on every request, and extended Departure Rule 1 to `00:00:00` to cover the midnight to 4:00 AM dispatch interval.
- **Form Accessibility & HTML5 Validation**: Replaced `display: none` select replacement in `autocomplete-search.js` with accessible off-screen styling to prevent browser validation crashes on required select controls.
- **Automated Verification**: Provided `VERIFY_SETUP.bat` and `VERIFY_SETUP.sh` to validate all services, database tables, PHP-FPM sockets, and WebSocket connectivity in one click.

### System Audit & Production Hardening Updates (September 2026)
- **RFC 6455 WebSocket Protocol Compliance & Socket Leak Fix**:
  - Implemented `decodeFrame()` in `app/Commands/WsServe.php` with XOR payload unmasking per RFC 6455 §5.3.
  - Catches Opcode `0x8` (Close Frame) on browser tab closures to cleanly release socket descriptors and prevent file descriptor and memory leaks.
  - Replaced text pings with RFC 6455 binary Ping control frames (`pack('CCN', 0x89, 0x04, time())`) to prevent reverse proxy/load balancer timeouts.
  - Added Cross-Site WebSocket Hijacking (CSWSH) Origin verification (`isAllowedOrigin()`) to block unauthorized third-party cross-site requests.
- **PostgreSQL Advisory Locking & Concurrency Batching**:
  - Protected `QueueModel::recalculateSchedule()` with PostgreSQL transaction-level advisory locks (`pg_advisory_xact_lock`), eliminating race conditions and duplicate position ranks during concurrent dispatcher actions.
  - Preloaded terminal departure rules into memory to eliminate N+1 database queries.
  - Batched database updates using `$this->updateBatch()`.
- **Infrastructure & Network Security Hardening**:
  - Closed direct public exposure of port 8081 in UFW (`deploy.sh`); all WebSocket connections route securely through Nginx (`/ws`) via standard ports 80/443 with TLS encryption.
  - Bound WebSocket server to loopback `127.0.0.1` by default.
  - Configured systemd service (`jeepney-websocket.service`) with `Restart=always` and `RestartSec=5s` for automatic crash recovery.
  - Added data retention CLI command `php spark maintenance:purge [--days=60]` for automated pruning of old departures and audit logs.
- **Frontend Scalability (100–200+ Concurrent Users)**:
  - Relaxed fallback guest polling from 5s to 15s (backing off to 30s on WebSocket connection) across `queue-sync.js`, `schedules.php`, and dashboard views.
  - Added modal state protection in `ajaxRefresh()` to prevent replacing the DOM while dispatchers or admins have modal dialogs open.
  - Removed `'onclick'` attribute bypass from DOMPurify configuration in `queue-sync.js`.
- **Session & Access Hardening**:
  - Hardened session directory permissions in `app/Config/Session.php` from `0777` to `0700`.
  - Standardized password minimum length to 8 characters across all controllers, forms, and views.
  - Scoped dispatcher history queries in `History.php` to assigned routes.
  - Synchronized vehicle status and seat capacity updates directly to active queue entries.

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
