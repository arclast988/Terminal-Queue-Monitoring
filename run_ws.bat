@echo off
setlocal enabledelayedexpansion

echo ======================================================
echo   Palompon Transit Real-time System - Auto Launcher
echo ======================================================
echo.

echo [INFO] This local script is safe. If Windows SmartScreen marks it as dangerous, choose "Run anyway".

echo [INFO] Checking for zone identifier and unblocking local file metadata...
if exist "%~f0:Zone.Identifier" (
    powershell -NoProfile -Command "try { Unblock-File -Path '%~f0' -ErrorAction Stop; Write-Host '[INFO] Zone identifier removed.' } catch { Write-Host '[WARN] Unblock failed (may require admin):' $_.Exception.Message }"
) else (
    echo [INFO] No zone identifier present.
)

REM ==========================================
REM  STEP 1: Detect PHP Installation
REM ==========================================
echo.
echo [INFO] Detecting PHP installation...
set "PHP_PATH=C:\xampp2\php\php.exe"

if not exist "%PHP_PATH%" (
    set "PHP_PATH=C:\xampp\php\php.exe"
)

if not exist "%PHP_PATH%" (
    for /f "delims=" %%p in ('where php 2^>nul') do set "PHP_PATH=%%p"
)

if not exist "%PHP_PATH%" (
    echo [ERROR] PHP executable not found.
    echo Please install XAMPP or set PHP in PATH, then re-run this script.
    pause
    exit /b 1
)

rem Ensure this path is executable and local to prevent unexpected script execution.
if /i "%PHP_PATH:~-4%" neq ".exe" (
    echo [ERROR] PHP_PATH must point to an .exe file. Detected: %PHP_PATH%
    pause
    exit /b 1
)

echo [OK] Found PHP at: %PHP_PATH%

REM ==========================================
REM  STEP 2: Detect MySQL Installation
REM ==========================================
echo.
echo [INFO] Detecting MySQL installation...
set "MYSQL_PATH=C:\xampp2\mysql\bin\mysql.exe"

if not exist "%MYSQL_PATH%" (
    set "MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe"
)

if not exist "%MYSQL_PATH%" (
    for /f "delims=" %%m in ('where mysql 2^>nul') do set "MYSQL_PATH=%%m"
)

if not exist "%MYSQL_PATH%" (
    echo [WARN] MySQL executable not found. Skipping database check.
    echo Please ensure MySQL/MariaDB is running via XAMPP.
    goto :skip_db
)

echo [OK] Found MySQL at: %MYSQL_PATH%

REM ==========================================
REM  STEP 3: Check Database and Auto-Import
REM ==========================================
echo.
echo [INFO] Checking if database "jeepneynvans" exists...

REM Ensure the clean schema file is available.
set "SQL_FILE=%~dp0jeepneynvans_clean.sql"
if not exist "!SQL_FILE!" (
    echo [WARN] Clean schema file not found at !SQL_FILE!
    echo [INFO] Attempting to restore "jeepneynvans_clean.sql" from git...
    pushd "%~dp0"
    git checkout -- jeepneynvans_clean.sql 2>nul
    popd
    if not exist "!SQL_FILE!" (
        echo [ERROR] Could not restore "jeepneynvans_clean.sql" from git.
        echo   Likely causes:
        echo     - git is not installed or not on PATH
        echo     - This folder is not inside a git repository
        echo     - "jeepneynvans_clean.sql" was never committed
        echo [INFO] To restore manually: git checkout -- jeepneynvans_clean.sql
        pause
        exit /b 1
    )
    echo [OK] Restored "jeepneynvans_clean.sql" from git.
)

REM Probe a core table - fails if DB is missing OR DB exists but is empty/incomplete.
"%MYSQL_PATH%" -u root --skip-password -e "SELECT 1 FROM jeepneynvans.users LIMIT 1;" >nul 2>nul
if %errorlevel% neq 0 (
    echo [WARN] Database "jeepneynvans" is missing or has no tables. Importing clean schema...

    "%MYSQL_PATH%" -u root --skip-password -e "CREATE DATABASE IF NOT EXISTS jeepneynvans CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    if !errorlevel! neq 0 (
        echo [ERROR] Failed to create database. Is MySQL/MariaDB running?
        pause
        exit /b 1
    )

    "%MYSQL_PATH%" -u root --skip-password jeepneynvans < "!SQL_FILE!"
    if !errorlevel! neq 0 (
        echo [ERROR] Failed to import clean schema.
        pause
        exit /b 1
    )

    echo [OK] Database "jeepneynvans" created from clean schema -- empty data, default admin only.
) else (
    echo [OK] Database "jeepneynvans" already populated. Skipping import.
)

:skip_db

REM ==========================================
REM  STEP 4: Setup Project Paths
REM ==========================================
set "PROJECT_DIR=%~dp0"
set "SPARK_SCRIPT=%PROJECT_DIR%spark"

if not exist "%SPARK_SCRIPT%" (
    echo [ERROR] Spark launcher not found at %SPARK_SCRIPT%
    echo Ensure this file is in the project root.
    pause
    exit /b 1
)

pushd "%PROJECT_DIR%"

echo.
echo ========== STARTING SERVICES ==========
echo.

REM ==========================================
REM  STEP 5: Start WebSocket Server
REM ==========================================
echo [INFO] Starting WebSocket Server on port 8081...
start "WebSocket Server" /MIN "%PHP_PATH%" "%SPARK_SCRIPT%" ws:serve

REM Give the WebSocket server a moment to start
timeout /t 2 /nobreak >nul
echo [OK] WebSocket Server started.

REM ==========================================
REM  STEP 6: Auto-Open Browser
REM ==========================================
echo [INFO] Opening system in browser...
start "" "http://localhost/jeepneynvans/public/"

REM ==========================================
REM  STEP 7: Start CI4 Web Server (blocking)
REM ==========================================
echo [INFO] Starting Web Server on http://localhost:8000...
echo.
echo ========================================
echo   All Services Are Now Running:
echo   - Web App:        http://localhost/jeepneynvans/public/
echo   - WebSocket:      ws://localhost:8081
echo   - Broadcast Port: localhost:8082
echo ========================================
echo.
echo [INFO] Press CTRL+C to stop all services.
echo.

"%PHP_PATH%" "%SPARK_SCRIPT%" serve

REM Cleanup on exit
echo.
echo [INFO] Stopping all services...
taskkill /f /im php.exe 2>nul
popd
exit
