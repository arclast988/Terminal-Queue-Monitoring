@echo off
REM Verification script for Terminal Queue Monitoring System setup (Windows WSL)
REM Run this after install_system.bat and start_system.bat to verify everything works

setlocal enableextensions
set "WSL_UTF8=1"
set "UBUNTU_DISTRO="
for /f "usebackq delims=" %%i in (`powershell -Command "wsl -l -q | ForEach-Object { $_ -replace [char]0, '' } | Where-Object { $_ -match 'Ubuntu' } | Select-Object -First 1"`) do set "UBUNTU_DISTRO=%%i"

if not defined UBUNTU_DISTRO (
    set "UBUNTU_DISTRO=Ubuntu"
)

set "WSL=wsl -d %UBUNTU_DISTRO% -u root"

echo.
echo ======================================================================
echo   Terminal Queue Monitoring System - Setup Verification (Windows WSL)
echo ======================================================================
echo.

REM Test WSL connectivity
%WSL% bash -c "echo '[OK] WSL is accessible'" || (
    echo [ERROR] Cannot communicate with WSL. Is %UBUNTU_DISTRO% installed?
    pause
    exit /b 1
)

REM Get project path
for /f "usebackq delims=" %%p in (`%WSL% wslpath "%~dp0."`) do set "PROJECT_WSL=%%p"
echo [OK] Project path: %PROJECT_WSL%
echo.

REM Run Linux-side verification script
echo Running verification checks...
%WSL% bash -lc "sed 's/\r$//' '%PROJECT_WSL%/VERIFY_SETUP.sh' | bash"

echo.
echo ======================================================================
echo   Next steps:
echo   1. Open http://localhost/ in your browser
echo   2. Log in with your admin account
echo   3. Check the dashboard
echo ======================================================================
echo.
pause
