@echo off
echo 🏠 Starting StaySphere Development Servers
echo ==========================================

REM Start backend in new window
echo 🔧 Starting Laravel backend server...
start "StaySphere Backend" cmd /k "cd backend && php artisan serve"

REM Wait a moment for backend to start
timeout /t 3 /nobreak >nul

REM Start frontend in new window
echo 🎨 Starting Astro frontend server...
start "StaySphere Frontend" cmd /k "cd frontend && npm run dev"

echo.
echo 🎉 Development servers are starting!
echo.
echo 🌐 Your application will be available at:
echo    Frontend: http://localhost:4321
echo    Backend:  http://localhost:8000/api
echo.
echo Press any key to close this window...
pause >nul