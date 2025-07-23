#!/bin/bash

# StaySphere Deployment Script
echo "🏠 StaySphere Deployment Script"
echo "================================"

# Check if we're in the right directory
if [ ! -f "README.md" ] || [ ! -d "frontend" ] || [ ! -d "backend" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
echo "🔍 Checking prerequisites..."

if ! command_exists php; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

if ! command_exists composer; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi

if ! command_exists node; then
    echo "❌ Node.js is not installed. Please install Node.js 18 or higher."
    exit 1
fi

if ! command_exists npm; then
    echo "❌ npm is not installed. Please install npm."
    exit 1
fi

echo "✅ All prerequisites are installed"

# Setup backend
echo ""
echo "🔧 Setting up backend..."
cd backend

if [ ! -f ".env" ]; then
    echo "📝 Creating .env file from .env.example"
    cp .env.example .env
    echo "⚠️  Please update the .env file with your database and API credentials"
fi

echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "🔑 Generating application key..."
php artisan key:generate

echo "🗄️  Running database migrations..."
php artisan migrate --force

echo "🔗 Creating storage link..."
php artisan storage:link

echo "🧹 Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

cd ..

# Setup frontend
echo ""
echo "🎨 Setting up frontend..."
cd frontend

echo "📦 Installing Node.js dependencies..."
npm install

echo "🏗️  Building frontend..."
npm run build

cd ..

echo ""
echo "🎉 Deployment completed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Update backend/.env with your database credentials"
echo "2. Update backend/.env with your Stripe API keys"
echo "3. Update backend/.env with your Cloudinary credentials"
echo "4. Start the backend: cd backend && php artisan serve"
echo "5. Start the frontend: cd frontend && npm run dev"
echo ""
echo "🌐 Your application will be available at:"
echo "   Frontend: http://localhost:4321"
echo "   Backend:  http://localhost:8000"
echo ""
echo "📚 For more information, see README.md"