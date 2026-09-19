#!/bin/bash
set -e

echo "=========================================="
echo "  Jeepney nVans - Railway Startup"
echo "=========================================="

# Use Railway's PORT or default to 8080
PORT="${PORT:-8080}"

# Import PostgreSQL schema if DATABASE_URL is available
if [ -n "$DATABASE_URL" ]; then
    echo "[DB] Checking if database tables exist..."
    
    # Check if the 'users' table exists (indicates schema is loaded)
    TABLE_EXISTS=$(psql "$DATABASE_URL" -tAc "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'users');" 2>/dev/null || echo "false")
    
    if [ "$TABLE_EXISTS" != "t" ]; then
        echo "[DB] Importing PostgreSQL schema..."
        psql "$DATABASE_URL" -f app/Database/postgres_schema.sql
        echo "[DB] Schema imported successfully!"
    else
        echo "[DB] Tables already exist, skipping import."
    fi
else
    echo "[DB] WARNING: No DATABASE_URL found, skipping schema import."
fi

# Create writable directories if they don't exist
mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar
chmod -R 777 writable/

# Start the WebSocket server in the background
echo "[WS] Starting WebSocket server on port 8081..."
php spark ws:serve &
WS_PID=$!
echo "[WS] WebSocket server started (PID: $WS_PID)"

# Start PHP built-in server
echo "[WEB] Starting PHP server on port $PORT..."
php -S 0.0.0.0:$PORT -t public/ public/index.php
