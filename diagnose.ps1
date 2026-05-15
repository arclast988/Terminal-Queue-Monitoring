$projectRoot = "c:\xampp2\htdocs\jeepneynvans"

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "WebSocket System Diagnostics" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Check if PHP is running
Write-Host "1. Checking PHP processes..." -ForegroundColor Yellow
$phpProcesses = Get-Process php -ErrorAction SilentlyContinue
if ($phpProcesses) {
    Write-Host "   [OK] PHP is running" -ForegroundColor Green
} else {
    Write-Host "   [FAIL] PHP not running - start run_ws.bat first!" -ForegroundColor Red
}
Write-Host ""

# Check port 8081 (WebSocket)
Write-Host "2. Checking port 8081 (WebSocket)..." -ForegroundColor Yellow
$wsPort = netstat -ano | Select-String ":8081"
if ($wsPort) {
    Write-Host "   [OK] Port 8081 is listening" -ForegroundColor Green
    $wsPort | ForEach-Object { Write-Host "     $_" -ForegroundColor Green }
} else {
    Write-Host "   [FAIL] Port 8081 NOT listening - run_ws.bat may not have started WebSocket server" -ForegroundColor Red
}
Write-Host ""

# Check port 8000 (Web Server)
Write-Host "3. Checking port 8000 (Web Server)..." -ForegroundColor Yellow
$webPort = netstat -ano | Select-String ":8000"
if ($webPort) {
    Write-Host "   [OK] Port 8000 is listening" -ForegroundColor Green
} else {
    Write-Host "   [FAIL] Port 8000 NOT listening - Web server not running" -ForegroundColor Red
}
Write-Host ""

# Check port 8082 (Broadcast)
Write-Host "4. Checking port 8082 (Broadcast)..." -ForegroundColor Yellow
$bcastPort = netstat -ano | Select-String ":8082"
if ($bcastPort) {
    Write-Host "   [OK] Port 8082 is listening" -ForegroundColor Green
} else {
    Write-Host "   [FAIL] Port 8082 NOT listening - Broadcast service not running" -ForegroundColor Red
}
Write-Host ""

# Check CodeIgniter logs
Write-Host "5. Checking CodeIgniter logs..." -ForegroundColor Yellow
$logFile = "$projectRoot\writable\logs\log-*.log"
$latestLog = Get-ChildItem $logFile -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending | Select-Object -First 1

if ($latestLog) {
    Write-Host "   [OK] Latest log file: $($latestLog.Name)" -ForegroundColor Green
    
    # Check for WebSocket errors
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
Write-Host "1. Open http://localhost:8000 in browser" -ForegroundColor White
Write-Host "2. Go to Staff Queue page" -ForegroundColor White
Write-Host "3. Open browser DevTools (F12) - Console tab" -ForegroundColor White
Write-Host "4. You should see: '[Staff Queue WebSocket] Initializing...'" -ForegroundColor White
Write-Host "5. Then: '[WS Connected] Listening for queue updates'" -ForegroundColor White
Write-Host "6. Try adding a vehicle and watch for '[Queue Update]'" -ForegroundColor White
Write-Host "======================================" -ForegroundColor Cyan
