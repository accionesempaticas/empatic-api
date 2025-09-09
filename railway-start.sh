#!/bin/bash

# Railway startup script
echo "🚀 Starting Railway deployment with SQLite..."

# Create SQLite database file if it doesn't exist
echo "📁 Ensuring SQLite database exists..."
touch database/database.sqlite

# Run migrations
echo "📋 Running migrations..."
php artisan migrate --force

# Run seeders (ignore if already exists)
echo "🌱 Running seeders..."
php artisan db:seed --class=AdminUserSeeder --force || true
php artisan db:seed --class=TestUsersSeeder --force || true

# Start the server
echo "🌐 Starting web server..."
php artisan serve --host=0.0.0.0 --port=$PORT