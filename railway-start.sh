#!/bin/bash

# Railway startup script
echo "🚀 Starting Railway deployment with SQLite..."

# Create SQLite database file if it doesn't exist
echo "📁 Ensuring SQLite database exists..."
touch database/database.sqlite
chmod 666 database/database.sqlite

# Clear config cache
echo "🧹 Clearing config cache..."
php artisan config:clear

# Run migrations
echo "📋 Running migrations..."
php artisan migrate --force

echo "✅ Build process complete. Handing over to web server."