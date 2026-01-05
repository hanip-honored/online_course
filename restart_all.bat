@echo off
REM Script untuk restart semua services

echo ========================================
echo   Restarting All Services
echo ========================================
echo.

echo [1/3] Stopping services...
taskkill /FI "WINDOWTITLE eq Python API Server*" /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq Queue Worker*" /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq Laravel Server*" /F >nul 2>&1

timeout /t 2 /nobreak >nul

echo [2/3] Clearing cache...
php artisan cache:clear >nul 2>&1

echo [3/3] Starting services...
call start_all.bat

echo.
echo Done!
pause
