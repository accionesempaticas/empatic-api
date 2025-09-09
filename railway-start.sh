#!/bin/bash

# Railway startup script
echo "🚀 Starting Railway deployment..."

# Run migrations
echo "📋 Running migrations..."
php artisan migrate --force

# Run seeders
echo "🌱 Running seeders..."
php artisan db:seed --class=AdminUserSeeder --force

# Start the server
echo "🌐 Starting web server..."
php artisan serve --host=0.0.0.0 --port=$PORT