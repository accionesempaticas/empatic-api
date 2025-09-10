#!/bin/bash
set -e

# Create .env from example if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Start the Laravel server
php artisan serve --host=0.0.0.0 --port=$PORT