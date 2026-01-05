@echo off
REM Script untuk menjalankan Laravel Queue Worker
REM Digunakan untuk auto-training model ketika ada rating baru

echo ========================================
echo   Laravel Queue Worker untuk Auto-Training
echo ========================================
echo.
echo [INFO] Starting queue worker...
echo [INFO] Press Ctrl+C to stop
echo.

cd /d "%~dp0"

:loop
php artisan queue:work --tries=3 --sleep=3 --timeout=180
echo.
echo [WARNING] Queue worker stopped. Restarting in 5 seconds...
timeout /t 5 /nobreak
goto loop
