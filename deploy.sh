#!/bin/bash

# 🚀 SCRIPT DE DESPLIEGUE AUTOMÁTICO - EMPATHIC ACTIONS API
# Este script automatiza el proceso de despliegue del backend

echo "🚀 Iniciando despliegue de Empathic Actions API..."

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    print_error "Este script debe ejecutarse desde el directorio raíz del proyecto Laravel"
    exit 1
fi

print_warning "PASO 1: Instalando dependencias..."
composer install --no-dev --optimize-autoloader
if [ $? -eq 0 ]; then
    print_success "Dependencias instaladas correctamente"
else
    print_error "Error al instalar dependencias"
    exit 1
fi

print_warning "PASO 2: Generando key de aplicación..."
php artisan key:generate --force
print_success "Key de aplicación generada"

print_warning "PASO 3: Configurando cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Cache configurado"

print_warning "PASO 4: Ejecutando migraciones..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    print_success "Migraciones ejecutadas correctamente"
else
    print_error "Error en las migraciones"
    exit 1
fi

print_warning "PASO 5: Ejecutando seeders..."
php artisan db:seed --force
if [ $? -eq 0 ]; then
    print_success "Seeders ejecutados correctamente"
else
    print_warning "Advertencia: Los seeders pueden haber fallado (normal si ya existen datos)"
fi

print_warning "PASO 6: Configurando storage..."
php artisan storage:link
mkdir -p storage/app/privates
chmod -R 755 storage
chmod -R 755 bootstrap/cache
print_success "Storage configurado"

print_warning "PASO 7: Limpiando cache anterior..."
php artisan cache:clear
php artisan config:cache
print_success "Cache limpiado y regenerado"

echo ""
print_success "🎉 DESPLIEGUE COMPLETADO EXITOSAMENTE!"
echo ""
echo "📋 Información importante:"
echo "   • Usuario Admin: admin@empathicactions.com / admin123"
echo "   • Usuario Test: usuario@empathicactions.com / usuario123"
echo "   • Storage: $(pwd)/storage/app/privates"
echo "   • Logs: $(pwd)/storage/logs/laravel.log"
echo ""
print_warning "🔧 No olvides configurar las variables de entorno en .env"
print_warning "🌐 Actualizar NEXT_PUBLIC_API_URL en el frontend"
echo ""