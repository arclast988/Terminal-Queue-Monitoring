#!/usr/bin/env powershell
# Palompon Transit Real-Time System Startup Script
# Usage: .\start-system.ps1

param(
    [switch]$NoWebSocket = $false
)

$ErrorActionPreference = "Continue"
$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "Palompon Transit Real-Time System" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

# Find PHP
$phpPath = $null
$xamppPhp = "C:\xampp\php\php.exe"
$xampp2Php = "C:\xampp2\php\php.exe"

if (Test-Path $xamppPhp) {
    $phpPath = $xamppPhp
}
elseif (Test-Path $xampp2Php) {
    $phpPath = $xampp2Php
}
else {
    try {
        $phpPath = (Get-Command php -ErrorAction Stop).Source
    }
    catch {
        Write-Host "[ERROR] PHP not found!" -ForegroundColor Red
        Write-Host "Please install XAMPP or add PHP to PATH" -ForegroundColor Red
        exit 1
    }
}

Write-Host "[INFO] Found PHP at: $phpPath" -ForegroundColor Green

# Verify spark file exists
$sparkScript = Join-Path $projectRoot "spark"
if (-not (Test-Path $sparkScript)) {
    Write-Host "[ERROR] Spark script not found at $sparkScript" -ForegroundColor Red
    exit 1
}

Write-Host "[INFO] Project directory: $projectRoot" -ForegroundColor Green
Write-Host ""

# Start WebSocket Server (unless --NoWebSocket flag used)
if (-not $NoWebSocket) {
    Write-Host "[INFO] Starting WebSocket Server on port 8081..." -ForegroundColor Yellow
    Start-Process -FilePath $phpPath -ArgumentList @($sparkScript, "ws:serve") `
        -WorkingDirectory $projectRoot `
        -WindowStyle Minimized `
        -NoNewWindow:$false

    Start-Sleep -Seconds 2
    Write-Host "[OK] WebSocket Server started" -ForegroundColor Green
    Write-Host ""
}

# Start Web Server
Write-Host "[INFO] Starting Web Server on http://localhost:8000..." -ForegroundColor Yellow
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Services Running:" -ForegroundColor Cyan
Write-Host "  Web Server:      http://localhost:8000" -ForegroundColor Green
Write-Host "  WebSocket:       ws://localhost:8081" -ForegroundColor Green
Write-Host "  Broadcast Port:  localhost:8082" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press CTRL+C to stop all services" -ForegroundColor Yellow
Write-Host ""

# Run web server (blocking)
& $phpPath "$sparkScript" serve

# Cleanup
Write-Host ""
Write-Host "[INFO] Stopping services..." -ForegroundColor Yellow
Get-Process php -ErrorAction SilentlyContinue | Stop-Process -Force
Write-Host "[OK] All services stopped" -ForegroundColor Green
