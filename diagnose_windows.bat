@echo off
REM Palompon Transit - Windows/WSL Diagnostic Tool
REM Run this to diagnose setup issues

setlocal enabledelayedexpansion
set "WSL_UTF8=1"

echo.
echo =====================================================================
echo   Palompon Transit Management System - WSL Diagnostic Tool
echo =====================================================================
echo.

REM Check if running as admin (recommended but not required for most checks)
net session >nul 2>nul
if errorlevel 1 (
    echo [WARNING] Not running as Administrator. Some checks may fail.
    echo           Right-click and "Run as Administrator" for full diagnostics.
    echo.
)

REM === Check 1: WSL Installation ===
echo [CHECK 1] WSL Installation...
wsl --version >nul 2>nul
if errorlevel 1 (
    echo   [FAIL] WSL is NOT installed!
    echo   FIX: Run install_system.bat as Administrator, or manually:
    echo        1. Open PowerShell as Admin
    echo        2. Run: wsl --install
    echo        3. Reboot your PC
    echo.
) else (
    for /f "delims=" %%a in ('wsl --version') do (
        echo   [OK] %%a
    )
    echo.
)

REM === Check 2: Distro Installation ===
echo [CHECK 2] Linux Distribution...
set "DISTRO_FOUND="
for /f "tokens=1-5" %%a in ('wsl -l -v 2^>nul ^| findstr /v "Windows"') do (
    if not "%%a"=="" (
        echo   [OK] Distro: %%a %%b %%c %%d %%e
        set "DISTRO_FOUND=1"
    )
)
if not defined DISTRO_FOUND (
    echo   [FAIL] No distro installed or WSL is not configured!
    echo   FIX: Run: wsl --install -d Ubuntu
    echo.
) else (
    echo.
)

REM === Check 3: Project Path ===
echo [CHECK 3] Project Path Detection...
set "HERE=%~dp0"
if "%HERE:~-1%"=="\" set "HERE=%HERE:~0,-1%"

for /f "usebackq delims=" %%p in (`wsl wslpath "%HERE%" 2^>nul`) do set "PROJECT_WSL=%%p"

if defined PROJECT_WSL (
    echo   [OK] Windows: %HERE%
    echo   [OK] WSL:     %PROJECT_WSL%
    echo.
) else (
    echo   [FAIL] Could not detect project path
    echo   Current folder: %HERE%
    echo.
)

REM === Check 4: Services Status ===
echo [CHECK 4] Services Status...
set "NGINX_STATE="
for /f "delims=" %%s in ('wsl -u root bash -lc "if systemctl is-active nginx >/dev/null 2>&1; then echo active; elif service nginx status >/dev/null 2>&1; then echo active; else echo inactive; fi"') do set "NGINX_STATE=%%s"
if "%NGINX_STATE%"=="active" (
    echo   [OK] Nginx is running
) else (
    echo   [FAIL] Nginx is NOT running
    echo   [INFO] Nginx status:
    wsl -u root bash -lc "systemctl status nginx --no-pager --lines=8 2>/dev/null || service nginx status 2>/dev/null || echo '   [INFO] nginx service not found or not running'"
)

echo.
set "PHPFPM_STATE="
for /f "delims=" %%s in ('wsl -u root bash -lc "PHPVER=\$(ls /etc/php 2>/dev/null | grep -E '^[0-9]+\.[0-9]+$' | sort -V | tail -n1); [ -z \"\$PHPVER\" ] && PHPVER=\"8.2\"; if systemctl is-active php\${PHPVER}-fpm >/dev/null 2>&1; then echo active; elif service php\${PHPVER}-fpm status >/dev/null 2>&1; then echo active; else echo inactive; fi"') do set "PHPFPM_STATE=%%s"
if "%PHPFPM_STATE%"=="active" (
    echo   [OK] PHP-FPM is running
) else (
    echo   [FAIL] PHP-FPM is NOT running (check ubuntu_migration/wsl_local_up.sh)
    echo   [INFO] PHP-FPM status:
    wsl -u root bash -lc "PHPVER=\$(ls /etc/php 2>/dev/null | grep -E '^[0-9]+\.[0-9]+$' | sort -V | tail -n1); [ -z \"\$PHPVER\" ] && PHPVER=\"8.2\"; systemctl status php\${PHPVER}-fpm --no-pager --lines=8 2>/dev/null || service php\${PHPVER}-fpm status 2>/dev/null || echo '   [INFO] php-fpm service not found or not running'"
)

echo.
set "DB_STATE="
for /f "delims=" %%s in ('wsl -u root bash -lc "if systemctl is-active mariadb >/dev/null 2>&1; then echo active; elif systemctl is-active mysql >/dev/null 2>&1; then echo active; elif service mariadb status >/dev/null 2>&1; then echo active; elif service mysql status >/dev/null 2>&1; then echo active; else echo inactive; fi"') do set "DB_STATE=%%s"
if "%DB_STATE%"=="active" (
    echo   [OK] MariaDB/MySQL is running
) else (
    echo   [FAIL] MariaDB/MySQL is NOT running
    echo   [INFO] MariaDB/MySQL status:
    wsl -u root bash -lc "systemctl status mariadb --no-pager --lines=8 2>/dev/null || systemctl status mysql --no-pager --lines=8 2>/dev/null || service mariadb status 2>/dev/null || service mysql status 2>/dev/null || echo '   [INFO] mariadb/mysql service not found or not running'"
)

echo.

REM === Check 5: Port Accessibility ===
echo [CHECK 5] Port 80 Accessibility...
for /f "tokens=5" %%a in ('netstat -ano 2^>nul ^| findstr ":80 "') do (
    if not "%%a"=="0" (
        echo   [OK] Port 80 is listening (PID: %%a)
    )
)

REM Try to access localhost
powershell -NoProfile -Command "try { $r = Invoke-WebRequest -Uri 'http://localhost/' -TimeoutSec 2 -ErrorAction Stop; Write-Host '   [OK] http://localhost/ is accessible (Status: '$r.StatusCode')' } catch { Write-Host '   [FAIL] http://localhost/ is NOT accessible'; Write-Host '         Error: ' $_.Exception.Message }" 2>nul

echo.

REM === Check 6: Nginx Configuration ===
echo [CHECK 6] Nginx Configuration...
if defined PROJECT_WSL (
    for /f "delims=" %%a in ('wsl -u root bash -lc "grep \"root\" /etc/nginx/sites-enabled/jeepneynvans_local 2>/dev/null | head -n 1"') do (
        echo   [CONFIG] %%a
    )
    echo.
) else (
    echo   [SKIP] Could not determine project path
    echo.
)

REM === Check 7: SQL Files ===
echo [CHECK 7] SQL Database Files...
if defined PROJECT_WSL (
    for /f "delims=" %%f in ('wsl ls -la "%PROJECT_WSL%"/*.sql 2^>nul') do (
        echo   %%f
    )
    if errorlevel 1 (
        echo   [WARN] No .sql files found in project root
    )
    echo.
) else (
    echo   [SKIP] Could not determine project path
    echo.
)

REM === Check 8: WebSocket Server ===
echo [CHECK 8] WebSocket Server...
for /f "delims=" %%a in ('wsl pgrep -f "spark ws:serve" 2^>nul') do (
    echo   [OK] WebSocket process running (PID: %%a)
)
if errorlevel 1 (
    echo   [INFO] WebSocket server not currently running (will start on demand)
)

echo.
echo =====================================================================
echo   DIAGNOSTIC COMPLETE
echo =====================================================================
echo.
echo NEXT STEPS:
echo   - If all [OK]:     Run start_system.bat or open http://localhost/
echo   - If [FAIL]:       See the recommended fixes above
echo   - If [WARN]:       Your system may work, but database won't auto-import
echo.
pause
