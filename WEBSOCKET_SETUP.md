# WebSocket Real-Time System Setup & Usage

## Overview
The Palompon Transit Management System now has **real-time WebSocket support** for instant queue updates across all connected staff members.

## What's Already Implemented

### 1. **WebSocket Server**
- Location: `app/Commands/WsServe.php`
- Runs on port 8081 (client connections; loopback `127.0.0.1` in production)
- Runs on port 8082 (broadcast trigger from application loopback)
- Full RFC 6455 WebSocket protocol support with client payload unmasking
- Clean socket descriptor cleanup on Opcode 0x8 (Close Frame) to eliminate leaks
- Binary control Ping/Pong frames every 30 seconds to maintain reverse proxy keepalives
- Cross-Site WebSocket Hijacking (CSWSH) Origin verification
- Auto-reconnection with exponential backoff on clients

### 2. **Broadcasting System**
- Method: `broadcastUpdate()` in `BaseController.php`
- Automatically called when queue operations happen
- Broadcasts to all connected WebSocket clients
- Sends sync tokens for data consistency

### 3. **Operations with Real-Time Updates**
- Staff Queue: `app/Controllers/Staff/Queue.php`
  - Adding vehicles to queue (broadcasts after transaction commit) ✓
  - Changing queue status (waiting → boarding → departed) ✓
  - Updating passenger counts with debouncing ✓
- Admin Actions: `app/Controllers/Admin/Announcements.php`, `VehicleTypes.php`, `DepartureRules.php`
  - Announcement and configuration updates broadcast immediately ✓
- All changes trigger real-time broadcasts automatically

### 4. **Client-Side Real-Time Handler**
- Location: `app/Views/staff/queue/index.php` (line 411+)
- Connects to WebSocket automatically using `public/js/queue-sync.js`
- Listens for `queue_update` messages
- **DOMPurify Sanitization**: Integrates DOMPurify to sanitize incoming real-time dynamic HTML payloads prior to injection in the DOM, preventing Cross-Site Scripting (XSS).
- Auto-reloads/updates page components when updates arrive safely
- Auto-reconnects with timeout backoff

---

## Quick Start (WSL + Nginx — no XAMPP)

### Option 1: Double-click `start_system.bat` (EASIEST)

Double-click **`start_system.bat`** in the project root. It runs the whole stack inside WSL (Ubuntu):

- Detects your installed PHP version and starts PostgreSQL, PHP-FPM, and Nginx
- Creates the PostgreSQL database/user and imports the PostgreSQL schema if tables are missing
- Starts the WebSocket server (`php spark ws:serve`) as a background process
- Waits for Nginx to respond, then opens the site

Then open: **http://localhost/**

---

### Option 2: Manual WSL Commands

Open a WSL (Ubuntu) shell and run:

```bash
cd /mnt/c/path/to/jeepneynvans     # wherever you cloned it
sudo service postgresql start
sudo service php<VERSION>-fpm start      # use whatever version is installed (e.g. 8.2, 8.3, 8.4)
sudo service nginx start
php spark ws:serve                 # WebSocket server — leave this running
```

Then open: **http://localhost/**

---

## How It Works

### When You Update Queue Operations:
1. Staff member updates queue status (e.g., marks vehicle as "boarding")
2. `broadcastUpdate()` sends message to port 8082
3. WebSocket server receives broadcast
4. Server sends `queue_update` to all connected clients
5. Connected staff pages auto-reload with fresh data
6. All staff see the update instantly

### Real-Time Flow:
```
Staff Action
     ↓
Controller Method (Staff/Queue.php)
     ↓
Database Update
     ↓
broadcastUpdate() → Port 8082
     ↓
WebSocket Server (Port 8081)
     ↓
Connected Clients (Staff Queue Pages)
     ↓
Auto-Reload with Fresh Data
```

---

## Verification

### Check if WebSocket is Running:
Open browser DevTools (F12) on Staff Queue page:
- Console tab
- Look for: `[Staff Queue] Connected to WS for real-time updates`
- If you see this, everything is working!

### Test Real-Time Updates:
1. Open Staff Queue in multiple browser tabs
2. In one tab: Add/update a vehicle in queue
3. Other tabs should auto-reload instantly

---

## Troubleshooting

### Error: "WebSocket Connection Failed"
- Make sure the WebSocket server is running (`php spark ws:serve`, or just run `start_system.bat`)
- Check that port 8081 is not blocked by firewall
- In WSL: `ss -ltn | grep :8081` should show the server listening

### No Auto-Reload When Queue Changes
- Check browser console for errors (F12)
- Make sure JavaScript is enabled
- Verify WebSocket connection in console (`[WS] Connected to ws://localhost/ws`)
- Verify WebSocket server is running (`ss -ltn | grep :8082` in WSL). `broadcastUpdate()` connects directly to local loopback port 8082 with automatic non-blocking timeout. If the service was stopped, restart it via `start_system.bat`.

### Port Already in Use
- If port 8081 or 8082 in use:
  ```powershell
  # Find and kill process using port 8081
  netstat -ano | findstr :8081
  taskkill /PID <PID> /F
  ```

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Commands/WsServe.php` | WebSocket server (writes `writable/ws_server.pid` on startup) |
| `app/Controllers/BaseController.php` | `broadcastUpdate()` method |
| `app/Controllers/Staff/Queue.php` | Queue operations with broadcasts |
| `public/js/ws-client.js` | Shared client-side WebSocket module |
| `app/Views/staff/queue/index.php` | WebSocket client handler |
| `start_system.bat` | One-click WSL launcher (Nginx + PHP-FPM + PostgreSQL + WS) |
| `ubuntu_migration/` | Production Ubuntu provisioning (`deploy.sh`, `nginx.conf`, systemd unit) |

---

## Production Architecture & Deployment

In production on Ubuntu Server:
1. **Nginx Reverse Proxy (`/ws`)**:
   - Web browsers connect over standard HTTP/HTTPS ports (`ws://your-domain/ws` or `wss://your-domain/ws`).
   - Nginx handles TLS termination and proxies traffic to loopback `127.0.0.1:8081` with HTTP/1.1 upgrade headers.
2. **Firewall Protection**:
   - Port 8081 is closed to the outside internet in UFW. Only standard web ports (80/443) and SSH (22) remain open.
   - The WebSocket server binds to `127.0.0.1` (`env('websocket.bindAddress', '127.0.0.1')`).
3. **Systemd Daemon with Auto-Restart**:
   - Managed by `jeepney-websocket.service` in `/etc/systemd/system/`.
   - Configured with `Restart=always` and `RestartSec=5s` for automatic recovery.
4. **Origin Validation (CSWSH)**:
   - Untrusted third-party sites cannot establish WebSocket connections or spoof requests. Allowed origins are configured via `websocket.allowedOrigins` in `.env`.

---

## Support & Diagnostics

If real-time updates are not reflecting:
1. Check that Nginx and the WebSocket daemon are running:
   ```bash
   sudo systemctl status nginx jeepney-websocket
   ```
2. Verify local listening ports:
   ```bash
   ss -tulpn | grep -E ':8081|:8082'
   ```
3. Check browser console (F12) for connection status (`[WS] Connected`).
4. Check CodeIgniter logs in `writable/logs/` and WebSocket server logs in systemd:
   ```bash
   sudo journalctl -u jeepney-websocket -n 50 -f
   ```
