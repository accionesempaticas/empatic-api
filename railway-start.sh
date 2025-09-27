#!/bin/bash

# Railway startup script
echo "🚀 Starting Railway deployment with SQLite..."

# Create SQLite database file if it doesn't exist
echo "📁 Ensuring SQLite database exists..."
rm -f database/database.sqlite
touch database/database.sqlite

# Fresh migrations (drop all tables and recreate)
echo "📋 Running fresh migrations..."
php artisan migrate:fresh --force

# Seeding with dev dependencies is disabled for production.

# Start the server
echo "🌐 Starting web server..."
php artisan serve --host=0.0.0.0 --port=80