# WebSocket Real-Time System Setup & Usage

## Overview
The Palompon Transit Management System now has **real-time WebSocket support** for instant queue updates across all connected staff members.

## What's Already Implemented

### 1. **WebSocket Server**
- Location: `app/Commands/WsServe.php`
- Runs on port 8081 (client connections)
- Runs on port 8082 (broadcast trigger from your app)
- Full RFC 6455 WebSocket protocol support
- Auto-reconnection with exponential backoff
- Heartbeat pings every 30 seconds

### 2. **Broadcasting System**
- Method: `broadcastUpdate()` in `BaseController.php`
- Automatically called when queue operations happen
- Broadcasts to all connected WebSocket clients
- Sends sync tokens for data consistency

### 3. **Queue Operations with Real-Time Updates**
- Staff Queue: `app/Controllers/Staff/Queue.php`
  - Adding vehicles to queue ✓
  - Changing queue status (waiting → boarding → departed) ✓
  - Updating passenger counts ✓
- Admin Queue: `app/Controllers/Admin/Queue.php`
  - Same operations with broadcasts ✓
- All changes trigger `queue_update` broadcasts automatically

### 4. **Client-Side Real-Time Handler**
- Location: `app/Views/staff/queue/index.php` (line 411+)
- Connects to WebSocket automatically
- Listens for `queue_update` messages
- Auto-reloads page when updates arrive
- Auto-reconnects with timeout backoff

---

## Quick Start (WSL + Nginx — no XAMPP)

### Option 1: Double-click `start_system.bat` (EASIEST)

Double-click **`start_system.bat`** in the project root. It runs the whole stack inside WSL (Ubuntu):

- Detects your installed PHP version and starts MariaDB, PHP-FPM, and Nginx
- Creates the database/user and imports the SQL dump if the tables are missing
- Starts the WebSocket server (`php spark ws:serve`) as a background process
- Waits for Nginx to respond, then opens the site

Then open: **http://localhost/**

---

### Option 2: Manual WSL Commands

Open a WSL (Ubuntu) shell and run:

```bash
cd /mnt/c/path/to/jeepneynvans     # wherever you cloned it
sudo service mariadb start
sudo service php8.2-fpm start      # use whatever version is installed
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
- Verify WebSocket connection in console
- **Broadcasts are gated on `writable/ws_server.pid`** — `broadcastUpdate()` only sends to the server when that file exists. The WS server creates it on startup and removes it on shutdown. If it's missing, the server isn't running; start it via `start_system.bat`.

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
| `start_system.bat` | One-click WSL launcher (Nginx + PHP-FPM + MariaDB + WS) |
| `ubuntu_migration/` | Nginx configs + WebSocket systemd unit (`nginx.conf`, `nginx_local.conf`, `jeepney-websocket.service`) |

---

## Production Notes

Currently the WebSocket server is designed for **local development** (broadcast port 8082 only accepts localhost connections for security).

For production deployment, you would need to:
1. Run WebSocket server on separate machine/port
2. Update broadcast address in `BaseController.php`
3. Add authentication to WebSocket connections
4. Use SSL/TLS (WSS) instead of plain WS

For now, this works great for development and small deployments!

---

## Support

If something isn't working:
1. Check that the services are running (Nginx on port 80, WS on 8081)
2. Verify ports 80, 8081, 8082 are not blocked
3. Check browser console (F12) for errors
4. Check CodeIgniter logs in `writable/logs/` and the WS log at `writable/logs/ws.log`
