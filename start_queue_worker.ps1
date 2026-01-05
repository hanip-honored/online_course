# Script untuk menjalankan Laravel Queue Worker
# Digunakan untuk auto-training model ketika ada rating baru

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Laravel Queue Worker untuk Auto-Training" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "[INFO] Starting queue worker..." -ForegroundColor Green
Write-Host "[INFO] Press Ctrl+C to stop" -ForegroundColor Yellow
Write-Host ""

$projectPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $projectPath

while ($true) {
    try {
        & php artisan queue:work --tries=3 --sleep=3 --timeout=180

        Write-Host ""
        Write-Host "[WARNING] Queue worker stopped. Restarting in 5 seconds..." -ForegroundColor Yellow
        Start-Sleep -Seconds 5
    }
    catch {
        Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "Restarting in 10 seconds..." -ForegroundColor Yellow
        Start-Sleep -Seconds 10
    }
}
