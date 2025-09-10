#!/bin/bash
set -e

echo "🔧 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔑 Generating application key..."
php artisan key:generate --force

echo "📦 Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🗃️ Setting up database..."
touch database/database.sqlite
chmod 644 database/database.sqlite

echo "📊 Running migrations..."
php artisan migrate --force

echo "🌱 Running seeders..."
php artisan db:seed --force

echo "🔗 Creating storage link..."
php artisan storage:link

echo "📁 Setting storage permissions..."
chmod -R 755 storage bootstrap/cache

echo "✅ Build completed successfully!"