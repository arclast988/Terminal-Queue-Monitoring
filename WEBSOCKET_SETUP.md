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

## Quick Start

### Option 1: Run Both Services at Once (EASIEST)

Simply double-click **`run_ws.bat`** in the project root:

```
c:\xampp2\htdocs\jeepneynvans\run_ws.bat
```

This will automatically:
- Start WebSocket Server on port 8081
- Start Web Server on port 8000
- Keep both running together

Then open: **http://localhost:8000**

---

### Option 2: Manual Terminal Commands

**Terminal 1 - WebSocket Server:**
```powershell
cd c:\xampp2\htdocs\jeepneynvans
php spark ws:serve
```

**Terminal 2 - Web Server:**
```powershell
cd c:\xampp2\htdocs\jeepneynvans
php spark serve
```

Then open: **http://localhost:8000**

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
- Make sure `php spark ws:serve` is running in a separate terminal
- Check that port 8081 is not blocked by firewall
- Try using `run_ws.bat` instead (easier)

### No Auto-Reload When Queue Changes
- Check browser console for errors (F12)
- Make sure JavaScript is enabled
- Verify WebSocket connection in console

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
| `app/Commands/WsServe.php` | WebSocket server implementation |
| `app/Controllers/BaseController.php` | `broadcastUpdate()` method |
| `app/Controllers/Staff/Queue.php` | Queue operations with broadcasts |
| `app/Views/staff/queue/index.php` | WebSocket client handler |
| `run_ws.bat` | Easy startup script |

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
1. Check that both services are running
2. Verify ports 8000, 8081, 8082 are not blocked
3. Check browser console (F12) for errors
4. Check CodeIgniter logs in `writable/logs/`
