#!/usr/bin/env bash
# Verification script for Jeepney nVans setup
# Run this after installation to verify everything is working
# Usage: bash VERIFY_SETUP.sh

set -e

GREEN='\033[1;32m'
YELLOW='\033[1;33m'
RED='\033[1;31m'
CYAN='\033[1;36m'
RESET='\033[0m'

say()  { printf '%b %s\n' "${CYAN}==>${RESET}"  "$*"; }
ok()   { printf '%b %s\n' "${GREEN}✓${RESET}"   "$*"; }
warn() { printf '%b %s\n' "${YELLOW}⚠${RESET}"  "$*"; }
fail() { printf '%b %s\n' "${RED}✗${RESET}"    "$*" >&2; }

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo ""
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║  Jeepney nVans - Setup Verification                               ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""

# Detect environment
if grep -qiE 'microsoft|wsl' /proc/sys/kernel/osrelease 2>/dev/null; then
    say "Environment: Windows (WSL)"
    PLATFORM="WSL"
else
    say "Environment: Native Linux"
    PLATFORM="LINUX"
fi
echo ""

# 1. Check PHP
say "Checking PHP installation..."
if command -v php >/dev/null 2>&1; then
    PHP_VER="$(php -v | head -n1)"
    ok "PHP found: $PHP_VER"
else
    fail "PHP not found"
    exit 1
fi

# 2. Check Nginx
say "Checking Nginx..."
if command -v nginx >/dev/null 2>&1; then
    ok "Nginx installed"
else
    fail "Nginx not installed"
fi

if [ "$PLATFORM" = "WSL" ]; then
    if service nginx status >/dev/null 2>&1; then
        ok "Nginx is running (WSL service)"
    else
        warn "Nginx is not running (use start_system.bat to start)"
    fi
elif [ "$PLATFORM" = "LINUX" ]; then
    if systemctl is-active --quiet nginx; then
        ok "Nginx is running"
    else
        warn "Nginx is not running (run: sudo systemctl start nginx)"
    fi
fi

# 3. Check MariaDB
say "Checking MariaDB..."
if command -v mysqld >/dev/null 2>&1 || command -v mariadbd >/dev/null 2>&1; then
    ok "MariaDB installed"
else
    fail "MariaDB not installed"
fi

if mysql -u jeepney_user -p12345678 -e "SELECT 1;" >/dev/null 2>&1; then
    ok "Can connect to MariaDB as jeepney_user"
    
    # Check database
    if mysql -u jeepney_user -p12345678 jeepneynvans -e "SHOW TABLES LIKE 'users';" | grep -q users; then
        TABLE_COUNT=$(mysql -u jeepney_user -p12345678 jeepneynvans -e "SHOW TABLES;" | wc -l)
        ok "Database 'jeepneynvans' has $((TABLE_COUNT - 1)) tables"
    else
        fail "Database 'jeepneynvans' exists but 'users' table not found"
    fi
else
    fail "Cannot connect to MariaDB (user: jeepney_user, pass: 12345678)"
fi

# 4. Check .env
say "Checking .env configuration..."
if [ -f "$PROJECT_ROOT/.env" ]; then
    ok ".env file exists"
    
    # Verify key settings
    if grep -q "app.baseURL = 'http://localhost/'" "$PROJECT_ROOT/.env"; then
        ok "app.baseURL is configured correctly"
    else
        warn "app.baseURL may not be set correctly (check .env manually)"
    fi
    
    if grep -q "database.default.database = jeepneynvans" "$PROJECT_ROOT/.env"; then
        ok "Database configuration present"
    else
        fail "Database configuration missing in .env"
    fi
else
    fail ".env file not found in $PROJECT_ROOT"
fi

# 5. Check project structure
say "Checking project structure..."
REQUIRED_DIRS=("app" "public" "system" "writable" "vendor")
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$PROJECT_ROOT/$dir" ]; then
        ok "$dir/ directory exists"
    else
        fail "$dir/ directory missing"
    fi
done

# 6. Check composer dependencies
say "Checking Composer dependencies..."
if [ -d "$PROJECT_ROOT/vendor" ] && [ -f "$PROJECT_ROOT/vendor/autoload.php" ]; then
    PACKAGE_COUNT=$(find "$PROJECT_ROOT/vendor" -mindepth 1 -maxdepth 1 -type d | wc -l)
    ok "Composer dependencies installed ($PACKAGE_COUNT packages)"
else
    warn "Composer dependencies not found - run: composer install"
fi

# 7. Check writable permissions
say "Checking writable directory permissions..."
if [ -d "$PROJECT_ROOT/writable" ]; then
    if [ -w "$PROJECT_ROOT/writable" ]; then
        ok "writable/ directory is writable"
    else
        warn "writable/ directory is not writable (may need: chmod 775 writable)"
    fi
else
    fail "writable/ directory not found"
fi

# 8. Web connectivity test
say "Testing web server..."
if command -v curl >/dev/null 2>&1; then
    HTTP_CODE="$(curl -s -o /dev/null -w '%{http_code}' http://localhost/ 2>/dev/null || echo "000")"
    case "$HTTP_CODE" in
        200|301|302|404)
            ok "Web server is responding (HTTP $HTTP_CODE)"
            ;;
        *)
            warn "Web server response: HTTP $HTTP_CODE (expected 200, 301, or 404)"
            warn "Make sure Nginx and PHP-FPM are running"
            ;;
    esac
else
    warn "curl not installed - skipping web connectivity test"
fi

# 9. Platform-specific checks
echo ""
if [ "$PLATFORM" = "WSL" ]; then
    say "WSL-specific checks..."
    if pgrep -f 'spark ws:serve' >/dev/null 2>&1; then
        ok "WebSocket server is running"
    else
        warn "WebSocket server is not running"
    fi
elif [ "$PLATFORM" = "LINUX" ]; then
    say "Linux-specific checks..."
    if systemctl is-active --quiet jeepney-websocket; then
        ok "WebSocket systemd service is running"
    else
        warn "WebSocket systemd service is not running"
    fi
fi

echo ""
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║  Setup Verification Complete                                       ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""
say "Next steps:"
echo "  1. Open http://localhost/ in your browser"
echo "  2. Log in with your admin credentials"
echo "  3. Check the dashboard for any warnings or errors"
echo ""
