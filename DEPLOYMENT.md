# 🚀 GUÍA DE DESPLIEGUE - EMPATHIC ACTIONS

## 📋 REQUISITOS PREVIOS

### Backend (Laravel API)
- PHP 8.1+
- Composer
- Base de datos (MySQL, PostgreSQL, SQLite)
- Storage con permisos de escritura

### Frontend (Next.js)
- Node.js 18+
- npm o yarn

---

## 🗄️ CONFIGURACIÓN DE BASE DE DATOS

### 1. Migraciones (Ejecutar en orden)

```bash
php artisan migrate:fresh
```

Las migraciones incluyen:
- ✅ Tabla `cache` y `jobs`
- ✅ Tabla `people` (usuarios principales)
- ✅ Tablas `locations`, `academic_formations`, `experiences`
- ✅ Tablas `programs`, `participants`
- ✅ Tabla `personal_access_tokens` (Sanctum)
- ✅ Tabla `sessions`
- ✅ Tabla `signed_documents`
- ✅ Campos adicionales: `area`, `group`, `status`, `document_paths`

### 2. Seeders (Datos iniciales)

```bash
php artisan db:seed
```

Los seeders incluyen:
- ✅ **AdminUserSeeder**: Usuario administrador principal
- ✅ **TestUsersSeeder**: 20+ usuarios de prueba con datos completos
- ✅ **PersonSeeder**: Usuarios básicos con Factory
- ✅ **DocumentTemplatesTableSeeder**: Plantillas de documentos

**Credenciales de Admin creadas:**
- Email: `admin@empathicactions.com`
- Password: `admin123`
- Email: `usuario@empathicactions.com`
- Password: `usuario123`

---

## ⚙️ VARIABLES DE ENTORNO

### Backend (.env)

```env
APP_NAME="Empathic Actions"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-api-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-db-username
DB_PASSWORD=your-db-password

SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=database

FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-email-password
MAIL_FROM_ADDRESS=noreply@empathicactions.com
```

### Frontend (.env.local)

```env
NEXT_PUBLIC_API_URL=https://your-api-domain.com/api
```

---

## 🚀 DESPLIEGUE BACKEND

### 1. Preparar el servidor

```bash
# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Generar key de aplicación
php artisan key:generate

# Optimizar configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrar base de datos
php artisan migrate --force

# Ejecutar seeders
php artisan db:seed --force

# Configurar permisos de storage
chmod -R 755 storage bootstrap/cache
```

### 2. Configurar storage

```bash
# Crear enlaces simbólicos
php artisan storage:link

# Crear directorios necesarios
mkdir -p storage/app/privates
chmod -R 755 storage/app/privates
```

---

## 🌐 DESPLIEGUE FRONTEND

### 1. Configurar variables de entorno

**IMPORTANTE:** Actualizar `NEXT_PUBLIC_API_URL` en `.env.local`:
```env
NEXT_PUBLIC_API_URL=https://your-backend-url.com/api
```

### 2. Build y Deploy

```bash
# Instalar dependencias
npm install

# Build para producción
npm run build

# Iniciar servidor (opcional)
npm start
```

---

## 🔧 CONFIGURACIONES ADICIONALES

### 1. CORS (Backend)

El archivo `config/cors.php` debe permitir el dominio del frontend:

```php
'allowed_origins' => [
    'https://your-frontend-domain.com',
    'http://localhost:3000',
    'http://localhost:3001'
],
```

### 2. Middleware Rate Limiting

Ya configurado en `routes/api.php` para registro de postulantes.

### 3. File Upload Limits

Verificar configuración en `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

---

## ✅ CHECKLIST DE DESPLIEGUE

### Backend:
- [ ] Variables de entorno configuradas
- [ ] Base de datos creada y configurada
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate --force`
- [ ] `php artisan db:seed --force`
- [ ] `php artisan storage:link`
- [ ] Permisos de storage configurados (755)
- [ ] Cache de configuración: `php artisan config:cache`
- [ ] Cache de rutas: `php artisan route:cache`

### Frontend:
- [ ] Variable `NEXT_PUBLIC_API_URL` actualizada
- [ ] `npm install`
- [ ] `npm run build`
- [ ] Deploy en plataforma (Vercel, Netlify, etc.)

### Testing Post-Deploy:
- [ ] Login con admin funciona
- [ ] Registro de usuarios funciona
- [ ] Subida de archivos funciona
- [ ] Vista de archivos en modal funciona
- [ ] Edición de usuarios funciona
- [ ] Firma de documentos funciona

---

## 🐛 TROUBLESHOOTING

### Error de permisos en storage:
```bash
sudo chown -R www-data:www-data storage
sudo chmod -R 755 storage
```

### Error de migraciones:
```bash
php artisan migrate:status
php artisan migrate:fresh --seed --force
```

### Error de cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Frontend no conecta con API:
- Verificar `NEXT_PUBLIC_API_URL` en `.env.local`
- Verificar CORS en backend
- Verificar que el backend esté desplegado y accesible

---

## 📞 SOPORTE

Si encuentras problemas durante el despliegue:
1. Verificar logs de Laravel en `storage/logs/laravel.log`
2. Verificar logs del servidor web
3. Verificar configuración de base de datos
4. Verificar permisos de archivos y directorios