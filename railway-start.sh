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

# Set permissions for storage and cache
echo "🔒 Setting permissions for storage and cache..."
chmod -R 775 storage bootstrap/cache

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations without deleting data
echo "📋 Running migrations..."
php artisan migrate --force

# Seed the admin user
echo "🌱 Seeding admin user..."
php artisan db:seed --class=AdminUserSeeder --force

# Start the server
echo "🌐 Starting web server..."
php artisan serve --host=0.0.0.0 --port=80