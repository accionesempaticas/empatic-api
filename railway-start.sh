#!/bin/bash

# Railway startup script
echo "🚀 Starting Railway deployment with SQLite..."

# Create SQLite database file if it doesn't exist
echo "📁 Ensuring SQLite database exists..."
rm -f database/database.sqlite
touch database/database.sqlite
chmod 666 database/database.sqlite

# Fresh migrations (drop all tables and recreate)
echo "📋 Running fresh migrations..."
php artisan migrate --force

# Run document templates seeder only (avoid conflicts)
echo "🌱 Running document templates seeder..."
php artisan db:seed --class=DocumentTemplatesTableSeeder --force || echo "⚠️ Document templates seeder failed, continuing..."

# Start the server in background to allow populate endpoint
echo "🌐 Starting web server in background..."
php artisan serve --host=0.0.0.0 --port=$PORT &
SERVER_PID=$!

# Wait a moment for server to start
sleep 5

# Create admin user
echo "👤 Creating admin user..."
curl -X POST http://localhost:$PORT/api/create-admin \
     -H "Content-Type: application/json" \
     -d '{"email": "admin@empathicactions.com", "password": "admin123"}' || echo "⚠️ Admin user creation failed, continuing..."

# Populate database using endpoint
echo "📊 Populating database with test data..."
curl -X GET http://localhost:$PORT/populate-db || echo "⚠️ Initial populate failed, will try basic populate..."
curl -X GET http://localhost:$PORT/populate-basic || echo "⚠️ Basic populate also failed, database will be empty"

# Bring server back to foreground
echo "✅ Database setup complete, server running on port $PORT"
wait $SERVER_PID