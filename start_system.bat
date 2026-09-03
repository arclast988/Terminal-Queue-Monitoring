@echo off
title Palompon Transit System - WSL Launcher
setlocal enabledelayedexpansion

REM Emit UTF-8 from wsl so FOR /F captures the path cleanly
set "WSL_UTF8=1"

REM All Linux-side logic lives in ubuntu_migration/wsl_local_up.sh so we avoid
REM fragile batch<->bash quoting. This launcher auto-detects its own location,
REM so the project can live in ANY folder (no XAMPP required).
set "UBUNTU_DISTRO="
for /f "usebackq delims=" %%i in (`powershell -Command "wsl -l -q | ForEach-Object { $_ -replace [char]0, '' } | Where-Object { $_ -match 'Ubuntu' } | Select-Object -First 1"`) do set "UBUNTU_DISTRO=%%i"

if not defined UBUNTU_DISTRO (
    set "UBUNTU_DISTRO=Ubuntu"
)

set "WSL_USER=root"
set "WSL=wsl -d %UBUNTU_DISTRO% -u %WSL_USER%"

echo =====================================================================
echo   Palompon Transit Management System - WSL Launcher
echo =====================================================================
echo.
echo Starting services: Nginx, PHP-FPM, PostgreSQL, WebSocket Server...
echo This may take 10-15 seconds on first run.
echo.

REM Verify WSL is available
echo [DEBUG] WSL command: %WSL% true
%WSL% true >nul 2>nul
echo [DEBUG] WSL check errorlevel: !ERRORLEVEL!
if not "!ERRORLEVEL!"=="0" goto WSL_FAIL

goto WSL_OK

:WSL_FAIL
    echo [ERROR] Failed to communicate with WSL. Is %UBUNTU_DISTRO% installed and running?
    echo.
    echo SOLUTIONS:
    echo   1. Run 'install_system.bat' as Administrator (will install WSL)
    echo   2. Or manually install: Open PowerShell as Admin and run:
    echo        wsl --install
    echo   3. Then reboot your PC
    echo   4. Come back and run this file again
    echo.
    pause
    exit /b 1

:WSL_OK
REM This .bat's own folder (strip trailing backslash so wslpath gets a clean path)
set "HERE=%~dp0"
if "%HERE:~-1%"=="\" set "HERE=%HERE:~0,-1%"

echo [OK] Project location: %HERE%
echo.

REM Convert the Windows path to a WSL path (works wherever the project lives)
set "PROJECT_WSL="
echo [DEBUG] Running: %WSL% wslpath "%HERE%"
for /f "usebackq delims=" %%p in (`%WSL% wslpath "%HERE%" 2^>nul`) do (
    echo [DEBUG] WSLPATH=%%p
    set "PROJECT_WSL=%%p"
)

echo [DEBUG] PROJECT_WSL after loop: !PROJECT_WSL!

if not defined PROJECT_WSL goto WSLPATH_FAIL

echo [OK] WSL project path: !PROJECT_WSL!
echo.
echo [STARTING] Services...
echo.

REM Run the bring-up script (sed strips CR; pipe stays inside the quoted arg).
REM Pass the detected project root to the script as an argument.
%WSL% bash -lc "sed 's/\r$//' '%PROJECT_WSL%/ubuntu_migration/wsl_local_up.sh' | bash -s -- '%PROJECT_WSL%'" 2>&1

echo.
echo =====================================================================
echo [COMPLETE] Services started successfully
echo =====================================================================
echo.
echo [INFO] Opening http://localhost/ in your browser...
echo        (Wait a few seconds for the page to load)
echo.

timeout /t 2 >nul
start "" "http://localhost/"

echo.
echo NEXT STEPS:
echo   - If the page loads: Use credentials from INSTALLATION_GUIDE.md
echo   - If it doesn't load: Run 'diagnose_windows.bat' for troubleshooting
echo.
echo Your services are running in the background (even if you close this window).
echo To STOP the services, run: stop_system.bat
echo.
pause

goto END

:WSLPATH_FAIL
    echo [ERROR] Could not convert Windows path to WSL path!
    echo.
    echo POSSIBLE CAUSES:
    echo   - Project is on a network drive (WSL can't access it)
    echo   - Project is in a VirtualBox shared folder
    echo   - Windows path has special characters
    echo.
    echo SOLUTION: Move your project to C:\Users\YourName\Downloads or similar
    echo.
    pause
    exit /b 1

:END
