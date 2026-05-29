@echo off
title Palompon Transit System - WSL Launcher
setlocal

REM Emit UTF-8 from wsl so FOR /F captures the path cleanly
set "WSL_UTF8=1"

REM All Linux-side logic lives in ubuntu_migration/wsl_local_up.sh so we avoid
REM fragile batch<->bash quoting. This launcher auto-detects its own location,
REM so the project can live in ANY folder (no XAMPP required).
set "WSL_USER=root"
set "WSL=wsl -u %WSL_USER%"

echo =====================================================================
echo   Palompon Transit Management System - WSL Launcher
echo =====================================================================
echo.

REM Verify WSL is available
%WSL% true >nul 2>nul
if errorlevel 1 (
    echo [ERROR] Failed to communicate with WSL. Is WSL installed and running?
    pause
    exit /b 1
)

REM This .bat's own folder (strip trailing backslash so wslpath gets a clean path)
set "HERE=%~dp0"
if "%HERE:~-1%"=="\" set "HERE=%HERE:~0,-1%"

REM Convert the Windows path to a WSL path (works wherever the project lives)
set "PROJECT_WSL="
for /f "usebackq delims=" %%p in (`%WSL% wslpath "%HERE%"`) do set "PROJECT_WSL=%%p"
if not defined PROJECT_WSL (
    echo [ERROR] Could not resolve this folder's WSL path. Is the project on a drive WSL can see?
    pause
    exit /b 1
)
echo [OK] Project folder: %PROJECT_WSL%
echo.

REM Run the bring-up script (sed strips CR; pipe stays inside the quoted arg).
REM Pass the detected project root to the script as an argument.
%WSL% bash -lc "sed 's/\r$//' '%PROJECT_WSL%/ubuntu_migration/wsl_local_up.sh' | bash -s -- '%PROJECT_WSL%'"

echo.
echo [INFO] Opening http://localhost/ ...
start "" "http://localhost/"

echo.
echo   Press any key to close this window. Services keep running in WSL.
echo.
pause
