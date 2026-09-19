#!/bin/bash
set -e

echo "=========================================="
echo "  Jeepney nVans - Railway Startup"
echo "=========================================="

# Use Railway's PORT or default to 8080
PORT="${PORT:-8080}"

# Parse DATABASE_URL into individual components
# Format: postgresql://user:password@host:port/database
if [ -n "$DATABASE_URL" ]; then
    echo "[DB] Parsing DATABASE_URL..."
    # Remove the protocol prefix
    DB_CONN="${DATABASE_URL#*://}"
    # Extract user
    DB_USER="${DB_CONN%%:*}"
    DB_CONN="${DB_CONN#*:}"
    # Extract password
    DB_PASS="${DB_CONN%%@*}"
    DB_CONN="${DB_CONN#*@}"
    # Extract host
    DB_HOST="${DB_CONN%%:*}"
    DB_CONN="${DB_CONN#*:}"
    # Extract port
    DB_PORT="${DB_CONN%%/*}"
    # Extract database name
    DB_NAME="${DB_CONN#*/}"
    
    echo "[DB] Host=$DB_HOST Port=$DB_PORT Database=$DB_NAME User=$DB_USER"
else
    echo "[DB] WARNING: No DATABASE_URL found, using defaults"
    DB_HOST="127.0.0.1"
    DB_PORT="5432"
    DB_NAME="jeepneynvans"
    DB_USER="jeepney_user"
    DB_PASS="12345678"
fi

# Generate .env file
echo "[ENV] Generating .env file..."
cat > .env << ENVFILE
CI_ENVIRONMENT = development

app.baseURL = '${RAILWAY_PUBLIC_DOMAIN:+https://$RAILWAY_PUBLIC_DOMAIN/}'

encryption.key = ${ENCRYPTION_KEY:-hex2bin:f6c5b4d3e2f1a09876543210fedcba9876543210fedcba9876543210fedcba98}

database.default.hostname = $DB_HOST
database.default.database = $DB_NAME
database.default.username = $DB_USER
database.default.password = $DB_PASS
database.default.DBDriver = Postgre
database.default.DBPrefix =
database.default.port = $DB_PORT

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
fi

# Create writable directories
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
