# PowerShell script untuk menjalankan semua services
# Online Course Recommender System

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Online Course Recommender System" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Starting all services..." -ForegroundColor Yellow
Write-Host ""

# Check if port 8000 is already in use
$port8000 = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
if ($port8000) {
    Write-Host "[WARNING] Port 8000 already in use!" -ForegroundColor Red
    $continue = Read-Host "Press Enter to continue anyway or Ctrl+C to cancel"
}

# Start Python API Server
Write-Host "[1/3] Starting Python API Server (Port 5000)..." -ForegroundColor Green
Start-Process cmd -ArgumentList "/k", "cd python && start_api_server.bat" -WindowStyle Normal
Start-Sleep -Seconds 3

# Start Queue Worker
Write-Host "[2/3] Starting Queue Worker (Auto-Training)..." -ForegroundColor Green
Start-Process powershell -ArgumentList "-NoExit", "-Command", "php artisan queue:work --tries=3" -WindowStyle Normal
Start-Sleep -Seconds 2

# Start Laravel Server
Write-Host "[3/3] Starting Laravel Server (Port 8000)..." -ForegroundColor Green
Start-Process powershell -ArgumentList "-NoExit", "-Command", "php artisan serve" -WindowStyle Normal
Start-Sleep -Seconds 3

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  All Services Started!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "- Laravel:     " -NoNewline
Write-Host "http://localhost:8000" -ForegroundColor Yellow
Write-Host "- Python API:  " -NoNewline
Write-Host "http://localhost:5000" -ForegroundColor Yellow
Write-Host "- Queue:       " -NoNewline
Write-Host "Running in background" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press Enter to open browser..." -ForegroundColor Cyan
Read-Host

Start-Process "http://localhost:8000"

Write-Host ""
Write-Host "All services are running in separate windows." -ForegroundColor Green
Write-Host "Close those windows to stop the services." -ForegroundColor Yellow
Write-Host ""
Write-Host "Press Enter to exit this window..." -ForegroundColor Cyan
Read-Host
