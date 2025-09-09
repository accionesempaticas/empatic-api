#!/bin/bash

echo "🔧 Solucionando problemas del backend..."
echo

# Ir al directorio del proyecto
cd "$(dirname "$0")"

echo "📍 Directorio actual: $(pwd)"
echo

# Verificar si existe la base de datos
if [ ! -f "database/database.sqlite" ]; then
    echo "📄 Creando archivo de base de datos SQLite..."
    touch database/database.sqlite
    echo "✓ Base de datos SQLite creada"
else
    echo "✓ Base de datos SQLite ya existe"
fi

echo

# Agregar columnas area y group
echo "🔨 Agregando columnas area y group..."
php add_columns.php
echo

# Ejecutar migraciones pendientes
echo "🚀 Ejecutando migraciones..."
php artisan migrate --force
echo

# Iniciar servidor
echo "🌐 Iniciando servidor Laravel..."
echo "URL: http://localhost:8000"
echo "Presiona Ctrl+C para detener"
echo
php artisan serve --port=8000