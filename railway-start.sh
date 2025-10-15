#!/bin/bash

# Railway startup script
echo "🚀 Starting Railway deployment with SQLite..."

# Install frontend dependencies and build assets
echo "📦 Installing frontend dependencies..."
npm install
echo "🎨 Building frontend assets..."
npm run build

# Create SQLite database file if it doesn't exist
echo "📁 Ensuring SQLite database exists..."
touch database/database.sqlite

# Create backup directories if they don't exist
echo "📁 Ensuring backup directories exist..."
mkdir -p storage/app/backups
mkdir -p /app/persistent-backups

# Set permissions for storage and cache
echo "🔒 Setting permissions for storage and cache..."
chmod -R 775 storage bootstrap/cache

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Create backup before migration (only if database has data)
if [ -s database/database.sqlite ]; then
    echo "💾 Creating pre-deployment backup..."
    php artisan db:backup --keep=10
fi

# Run migrations without deleting data (preserves existing data)
echo "📋 Running migrations (preserving existing data)..."
php artisan migrate --force

# Seed the admin user (only if it doesn't exist)
echo "🌱 Ensuring admin user exists..."
php artisan db:seed --class=AdminUserSeeder --force

# Start scheduler in background for automated backups
echo "⏰ Starting Laravel scheduler..."
php artisan schedule:work &

# Start the server
echo "🌐 Starting web server..."
php artisan serve --host=0.0.0.0 --port=80