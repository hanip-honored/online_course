@echo off
REM Script untuk menjalankan semua services (Laravel, Python API, Queue Worker)

title Online Course Recommender - All Services

echo ========================================
echo   Online Course Recommender System
echo ========================================
echo.
echo Starting all services...
echo.

REM Check if port 8000 is already in use
netstat -ano | findstr :8000 >nul
if %errorlevel%==0 (
    echo [WARNING] Port 8000 already in use!
    echo Press any key to continue anyway or Ctrl+C to cancel...
    pause >nul
)

REM Start Python API Server
echo [1/3] Starting Python API Server (Port 5000)...
start "Python API Server" cmd /k "cd python && start_api_server.bat"
timeout /t 3 /nobreak >nul

REM Start Queue Worker
echo [2/3] Starting Queue Worker (Auto-Training)...
start "Queue Worker" cmd /k "php artisan queue:work --tries=3"
timeout /t 2 /nobreak >nul

REM Start Laravel Server
echo [3/3] Starting Laravel Server (Port 8000)...
start "Laravel Server" cmd /k "php artisan serve"
timeout /t 2 /nobreak >nul

echo.
echo ========================================
echo   All Services Started!
echo ========================================
echo.
echo - Laravel:     http://localhost:8000
echo - Python API:  http://localhost:5000
echo - Queue:       Running in background
echo.
echo Press any key to open browser...
pause >nul

start http://localhost:8000

echo.
echo All services are running in separate windows.
echo Close those windows to stop the services.
echo.
pause
