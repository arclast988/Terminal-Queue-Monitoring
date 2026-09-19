#!/bin/bash
set -e

echo "=========================================="
echo "  Jeepney nVans - Railway Startup"
echo "=========================================="

# Use Railway's PORT or default to 8080
PORT="${PORT:-8080}"

# Generate .env file from Railway environment variables
echo "[ENV] Generating .env file from Railway variables..."
cat > .env << ENVFILE
CI_ENVIRONMENT = production

app.baseURL = '${RAILWAY_PUBLIC_DOMAIN:+https://$RAILWAY_PUBLIC_DOMAIN/}'

encryption.key = ${ENCRYPTION_KEY:-hex2bin:f6c5b4d3e2f1a09876543210fedcba9876543210fedcba9876543210fedcba98}

database.default.hostname = ${PGHOST:-127.0.0.1}
database.default.database = ${PGDATABASE:-jeepneynvans}
database.default.username = ${PGUSER:-jeepney_user}
database.default.password = ${PGPASSWORD:-12345678}
database.default.DBDriver = Postgre
database.default.DBPrefix =
database.default.port = ${PGPORT:-5432}

email.fromEmail  = "${EMAIL_FROM:-arclast988@gmail.com}"
email.fromName   = "${EMAIL_FROM_NAME:-System Feedback}"
email.recipients = "${EMAIL_RECIPIENTS:-arclast988@gmail.com}"
email.SMTPUser   = "${EMAIL_SMTP_USER:-arclast988@gmail.com}"
email.SMTPPass   = "${EMAIL_SMTP_PASS:-tcix agvf bark sjmg}"

websocket.bindAddress = ${WEBSOCKET_BIND:-0.0.0.0}
websocket.clientPort = ${WEBSOCKET_PORT:-8081}
websocket.broadcastPort = ${WEBSOCKET_BROADCAST_PORT:-8082}
ENVFILE
echo "[ENV] .env file generated!"

# Import PostgreSQL schema if DATABASE_URL is available
if [ -n "$DATABASE_URL" ]; then
    echo "[DB] Checking if database tables exist..."
    
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
