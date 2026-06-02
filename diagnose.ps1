$projectRoot = $PSScriptRoot
# Convert this folder's Windows path to a WSL path in pure PowerShell.
# (wsl.exe mangles backslashes when called directly from PowerShell, so don't use wslpath here.)
$projectWsl = "/mnt/" + $projectRoot.Substring(0,1).ToLower() + ($projectRoot.Substring(2) -replace '\\','/')

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "WebSocket System Diagnostics (WSL + Nginx)" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# 1. PHP-FPM running inside WSL
Write-Host "1. Checking PHP-FPM inside WSL..." -ForegroundColor Yellow
$fpm = wsl bash -lc "pgrep -f php-fpm | head -n1 2>/dev/null"
if ($fpm) {
    Write-Host "   [OK] PHP-FPM is running in WSL" -ForegroundColor Green
} else {
    Write-Host "   [FAIL] PHP-FPM not running - run start_system.bat first!" -ForegroundColor Red
}
Write-Host ""

# 2. WebSocket server process + PID file
Write-Host "2. Checking WebSocket server (spark ws:serve)..." -ForegroundColor Yellow
$ws = wsl bash -lc "pgrep -f 'spark ws:serve' | head -n1 2>/dev/null"
$wsPidFile = wsl bash -lc "cat '$projectWsl/writable/ws_server.pid' 2>/dev/null"
if ($ws) {
    Write-Host "   [OK] WebSocket server is running (ws_server.pid: $wsPidFile)" -ForegroundColor Green
} else {
    Write-Host "   [FAIL] WebSocket server NOT running - run start_system.bat" -ForegroundColor Red
}
Write-Host ""

# 3. Web server reachability (Nginx on port 80)
Write-Host "3. Checking Nginx (http://localhost/)..." -ForegroundColor Yellow
$code = wsl bash -lc "curl -sS -o /dev/null -w '%{http_code}' http://localhost/ 2>/dev/null"
if ($code -match '^[2345]\d\d$') {
    Write-Host "   [OK] Nginx responded with HTTP $code" -ForegroundColor Green
} else {
    Write-Host "   [FAIL] No HTTP response on port 80 - Nginx/PHP-FPM not ready (502 = wrong PHP-FPM socket)" -ForegroundColor Red
}
Write-Host ""

# 4. Port 8081 (WebSocket client connections)
Write-Host "4. Checking port 8081 (WebSocket)..." -ForegroundColor Yellow
$wsPort = netstat -ano | Select-String ":8081"
if ($wsPort) {
    Write-Host "   [OK] Port 8081 is listening" -ForegroundColor Green
    $wsPort | ForEach-Object { Write-Host "     $_" -ForegroundColor Green }
} else {
    Write-Host "   [FAIL] Port 8081 NOT listening - WebSocket server not started" -ForegroundColor Red
}
Write-Host ""

# 5. Port 8082 (broadcast trigger - WSL-internal, bound to 127.0.0.1)
Write-Host "5. Checking port 8082 (broadcast trigger, WSL-internal)..." -ForegroundColor Yellow
$bcast = wsl bash -lc "ss -ltn 2>/dev/null | grep 127.0.0.1:8082"
if ($bcast) {
    Write-Host "   [OK] Port 8082 is listening inside WSL" -ForegroundColor Green
} else {
    Write-Host "   [INFO] Port 8082 not detected. It binds 127.0.0.1 inside WSL and is only used by broadcastUpdate() - not visible from Windows." -ForegroundColor Yellow
}
Write-Host ""

# 6. CodeIgniter logs
Write-Host "6. Checking CodeIgniter logs..." -ForegroundColor Yellow
$logFile = "$projectRoot\writable\logs\log-*.log"
$latestLog = Get-ChildItem $logFile -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending | Select-Object -First 1

if ($latestLog) {
    Write-Host "   [OK] Latest log file: $($latestLog.Name)" -ForegroundColor Green

    $wsErrors = Select-String "WS Broadcast" $latestLog.FullName -ErrorAction SilentlyContinue | Select-Object -Last 3
    if ($wsErrors) {
        Write-Host "   Recent WebSocket broadcasts:" -ForegroundColor Cyan
        $wsErrors | ForEach-Object { Write-Host "     $_" -ForegroundColor Gray }
    }
} else {
    Write-Host "   [FAIL] No log files found" -ForegroundColor Yellow
}
Write-Host ""

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. If anything failed above, run start_system.bat" -ForegroundColor White
Write-Host "2. Open http://localhost/ in browser" -ForegroundColor White
Write-Host "3. Go to Staff Queue page" -ForegroundColor White
Write-Host "4. Open browser DevTools (F12) - Console tab" -ForegroundColor White
Write-Host "5. You should see: '[WS] Connected to ws://localhost:8081'" -ForegroundColor White
Write-Host "6. Add/update a vehicle in another tab and watch for a 'queue_update'" -ForegroundColor White
Write-Host "======================================" -ForegroundColor Cyan
