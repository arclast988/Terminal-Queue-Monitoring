@echo off
title Palompon Transit System - First-time Installer
setlocal enableextensions

REM Emit UTF-8 from wsl so FOR /F and findstr can parse output cleanly
set "WSL_UTF8=1"

REM --- Auto-elevate to admin (required for WSL feature install) ---
set "ELEVATED=0"
if "%1"=="--elevated" set "ELEVATED=1"
net session >nul 2>nul
if errorlevel 1 if "%ELEVATED%"=="0" (
    echo Requesting administrator privileges...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -ArgumentList '--elevated' -Verb RunAs"
    exit /b
)

echo =====================================================================
echo   Palompon Transit Management System - First-time Installer
echo =====================================================================
echo.
echo This will install:  WSL 2 (if missing), Ubuntu, MariaDB, PHP 8.2-FPM,
echo                     Nginx, Composer, and project PHP dependencies.
echo Disk usage:         approximately 3-4 GB.
echo Reboot may be required after WSL is installed.
echo.
echo Press Ctrl+C to abort, or
pause

REM --- Windows version sanity check (need build 19041+) ---
set "WINBUILD=0"
for /f "tokens=3" %%a in ('reg query "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion" /v CurrentBuild ^| findstr /i CurrentBuild') do set "WINBUILD=%%a"
if %WINBUILD% LSS 19041 (
    echo [ERROR] Windows 10 build 19041+ or Windows 11 is required.
    echo         Your build is %WINBUILD%. Please upgrade Windows or install
    echo         WSL manually: https://learn.microsoft.com/windows/wsl/install-manual
    pause
    exit /b 1
)
echo [OK] Windows build %WINBUILD% is supported.
echo.

REM ====================================================================
REM  STAGE A: WSL kernel
REM ====================================================================
echo [STAGE A] Checking WSL...
wsl --version >nul 2>nul
if errorlevel 1 (
    echo [INFO] WSL not detected. Installing the WSL kernel...
    wsl --install --no-distribution
    wsl --version >nul 2>nul
    if errorlevel 1 (
        echo.
        echo =====================================================================
        echo   WSL kernel installed, but a REBOOT is required to activate it.
        echo   Please reboot your computer, then re-run install_system.bat.
        echo =====================================================================
        pause
        exit /b 0
    )
)
echo [OK] WSL is ready.
echo.

REM ====================================================================
REM  STAGE B: Ubuntu distribution
REM ====================================================================
echo [STAGE B] Checking Ubuntu distribution...
set "UBUNTU_DISTRO="
for /f "usebackq delims=" %%i in (`powershell -Command "wsl -l -q | ForEach-Object { $_ -replace [char]0, '' } | Where-Object { $_ -match 'Ubuntu' } | Select-Object -First 1"`) do set "UBUNTU_DISTRO=%%i"

if not defined UBUNTU_DISTRO (
    set "UBUNTU_DISTRO=Ubuntu"
)

wsl -d %UBUNTU_DISTRO% -u root true >nul 2>nul
if errorlevel 1 (
    echo [INFO] Ubuntu distro "%UBUNTU_DISTRO%" not detected. Installing (this may take several minutes)...
    wsl --install -d %UBUNTU_DISTRO% --no-launch
    if errorlevel 1 (
        echo [ERROR] Failed to install %UBUNTU_DISTRO%. Check your internet connection.
        pause
        exit /b 1
    )
    REM Initialize the distro as root to skip the interactive username prompt
    wsl -d %UBUNTU_DISTRO% -u root -- echo ready >nul 2>nul
)
echo [OK] %UBUNTU_DISTRO% is installed.
echo.

REM ====================================================================
REM  STAGE C: Linux-side stack + composer install
REM ====================================================================
set "WSL=wsl -d %UBUNTU_DISTRO% -u root"

REM This .bat's own folder (strip trailing backslash so wslpath gets a clean path)
set "HERE=%~dp0"
if "%HERE:~-1%"=="\" set "HERE=%HERE:~0,-1%"

REM Convert the Windows path to a WSL path (works wherever the project lives)
set "PROJECT_WSL="
for /f "usebackq delims=" %%p in (`%WSL% wslpath "%HERE%"`) do set "PROJECT_WSL=%%p"
if not defined PROJECT_WSL (
    echo [ERROR] Could not resolve this folder's WSL path.
    pause
    exit /b 1
)
echo [OK] Project folder: %PROJECT_WSL%
echo.

echo [STAGE C] Installing Ubuntu stack and project dependencies...
echo           (apt + composer may take 5-10 minutes on first run)
echo.
%WSL% bash -lc "sed 's/\r$//' '%PROJECT_WSL%/ubuntu_migration/wsl_install.sh' | bash -s -- '%PROJECT_WSL%'"
if errorlevel 1 (
    echo.
    echo [ERROR] Stack installation failed inside Ubuntu. Scroll up for details.
    pause
    exit /b 1
)

REM ====================================================================
REM  STAGE D: Create .env if missing
REM ====================================================================
echo.
echo [STAGE D] Checking .env...
if not exist "%HERE%\.env" (
    if exist "%HERE%\env" (
        copy "%HERE%\env" "%HERE%\.env" >nul
        echo [OK] Created .env from the env template.
    ) else (
        echo [WARN] No env template found; you will need to create .env manually.
    )
) else (
    echo [OK] .env already exists.
)

echo.
echo =====================================================================
echo   SETUP COMPLETE.
echo   Double-click start_system.bat to launch the app at http://localhost/.
echo =====================================================================
echo.
pause
