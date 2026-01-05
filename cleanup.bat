@echo off
REM Script untuk membersihkan file yang tidak digunakan

echo ========================================
echo   Cleanup Project Files
echo ========================================
echo.

echo [1/5] Cleaning Python cache...
if exist python\__pycache__ (
    rmdir /s /q python\__pycache__
    echo   ✓ Removed python/__pycache__
)

echo.
echo [2/5] Removing test scripts...
if exist python\test_api.bat (
    del python\test_api.bat
    echo   ✓ Removed python/test_api.bat
)
if exist python\test_api.ps1 (
    del python\test_api.ps1
    echo   ✓ Removed python/test_api.ps1
)

echo.
echo [3/5] Cleaning Laravel cache...
call php artisan cache:clear >nul 2>&1
call php artisan config:clear >nul 2>&1
call php artisan route:clear >nul 2>&1
call php artisan view:clear >nul 2>&1
echo   ✓ Cleared Laravel cache

echo.
echo [4/5] Cleaning logs (keeping today's log)...
for /f "skip=1" %%f in ('dir /b /o-d storage\logs\*.log 2^>nul') do (
    del storage\logs\%%f
    echo   ✓ Removed storage/logs/%%f
)

echo.
echo [5/5] Cleaning node_modules (optional, will be big)...
set /p cleanup_node="Clean node_modules? This will save ~500MB but require npm install later (y/n): "
if /i "%cleanup_node%"=="y" (
    rmdir /s /q node_modules
    echo   ✓ Removed node_modules (run 'npm install' to restore)
)

echo.
echo ========================================
echo   Cleanup Complete!
echo ========================================
echo.
echo Files cleaned:
echo   - Python cache (__pycache__)
echo   - Test scripts
echo   - Laravel cache
echo   - Old logs
if /i "%cleanup_node%"=="y" echo   - node_modules

echo.
pause
