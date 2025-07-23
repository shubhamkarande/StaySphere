@echo off
echo 🏠 StaySphere Deployment Script
echo ================================

REM Check if we're in the right directory
if not exist "README.md" (
    echo ❌ Error: Please run this script from the project root directory
    exit /b 1
)
if not exist "frontend" (
    echo ❌ Error: Please run this script from the project root directory
    exit /b 1
)
if not exist "backend" (
    echo ❌ Error: Please run this script from the project root directory
    exit /b 1
)

REM Check prerequisites
echo 🔍 Checking prerequisites...

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ PHP is not installed. Please install PHP 8.1 or higher.
    exit /b 1
)

where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ Composer is not installed. Please install Composer.
    exit /b 1
)

where node >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ Node.js is not installed. Please install Node.js 18 or higher.
    exit /b 1
)

where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ npm is not installed. Please install npm.
    exit /b 1
)

echo ✅ All prerequisites are installed

REM Setup backend
echo.
echo 🔧 Setting up backend...
cd backend

if not exist ".env" (
    echo 📝 Creating .env file from .env.example
    copy .env.example .env
    echo ⚠️  Please update the .env file with your database and API credentials
)

echo 📦 Installing PHP dependencies...
composer install --no-dev --optimize-autoloader

echo 🔑 Generating application key...
php artisan key:generate

echo 🗄️  Running database migrations...
php artisan migrate --force

echo 🔗 Creating storage link...
php artisan storage:link

echo 🧹 Clearing caches...
php artisan config:cache
php artisan route:cache
php artisan view:cache

cd ..

REM Setup frontend
echo.
echo 🎨 Setting up frontend...
cd frontend

echo 📦 Installing Node.js dependencies...
npm install

echo 🏗️  Building frontend...
npm run build

cd ..

echo.
echo 🎉 Deployment completed successfully!
echo.
echo 📋 Next steps:
echo 1. Update backend\.env with your database credentials
echo 2. Update backend\.env with your Stripe API keys
echo 3. Update backend\.env with your Cloudinary credentials
echo 4. Start the backend: cd backend ^&^& php artisan serve
echo 5. Start the frontend: cd frontend ^&^& npm run dev
echo.
echo 🌐 Your application will be available at:
echo    Frontend: http://localhost:4321
echo    Backend:  http://localhost:8000
echo.
echo 📚 For more information, see README.md

pause