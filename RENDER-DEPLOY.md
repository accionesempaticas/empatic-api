# 🚀 Guía de Despliegue en Render

## Pasos para desplegar en Render:

### 1. Crear cuenta en Render.com

### 2. Conectar repositorio
- Ve a Dashboard → New → Web Service
- Conecta tu repositorio GitHub/GitLab

### 3. Configuración del servicio:
- **Name**: empathic-actions-api
- **Runtime**: PHP
- **Build Command**: `./render-build.sh`
- **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`

### 4. Variables de entorno (en Render Dashboard):

```bash
APP_NAME="Empathic Actions"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-servicio.onrender.com
DB_CONNECTION=sqlite
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_LEVEL=error
FILESYSTEM_DISK=local
MAIL_MAILER=log
UPLOAD_MAX_SIZE=10240
FILE_STORAGE_PATH=storage/app/privates
```

**IMPORTANTE**: Render generará automáticamente `APP_KEY`

### 5. Actualizar frontend
Después del despliegue, actualizar en tu frontend de Vercel:
```bash
NEXT_PUBLIC_API_URL=https://tu-servicio.onrender.com/api
```

### 6. Configurar dominio personalizado (opcional)
En Render Dashboard → Settings → Custom Domains

## 🔍 Verificar despliegue:

1. **API funcionando**: `https://tu-servicio.onrender.com/api`
2. **Login admin**: 
   - Email: `admin@empathicactions.com`
   - Password: `admin123`
3. **Health check**: `https://tu-servicio.onrender.com/health`

## ⚠️ Notas importantes:

- Render plan gratuito: 15 min de inactividad = suspensión
- SQLite se reinicia en cada deploy (usar volume persistence)
- Storage se borra en redeploy (configurar storage externo si necesitas persistencia)

## 🐛 Troubleshooting:

- **Build falla**: Verificar PHP version (8.2+)
- **Storage errors**: Verificar permisos en build script
- **CORS errors**: Verificar dominio en `config/cors.php`