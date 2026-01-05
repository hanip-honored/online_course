@echo off
REM Start Course Recommender Microservice with Conda

echo ====================================
echo Starting Course Recommender API
echo ====================================
echo.

REM Change to python directory
cd /d "%~dp0"

REM Start the server using conda environment Python
echo Starting Flask server on http://localhost:5000
echo Press Ctrl+C to stop the server
echo.

C:\Users\USER\miniconda3\envs\online_course\python.exe api_server.py

pause
