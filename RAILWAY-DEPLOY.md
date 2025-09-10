# 🚂 Guía de Despliegue en Railway

## Pasos para desplegar en Railway:

### 1. Crear cuenta en Railway
- Ve a: https://railway.app
- Sign up with GitHub

### 2. Crear nuevo proyecto
- Dashboard → **"New Project"**
- **"Deploy from GitHub repo"**
- Seleccionar: `accionesempaticas/empatic-api`

### 3. Variables de entorno automáticas
Railway detectará Laravel y configurará automáticamente:
- ✅ PHP 8.2
- ✅ Composer
- ✅ SQLite support

### 4. Variables de entorno manuales
En Railway Dashboard → Variables:

```bash
APP_NAME=Empathic Actions
APP_ENV=production
APP_DEBUG=false
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

**IMPORTANTE**: Railway genera APP_KEY automáticamente

### 5. Deploy automático
- ✅ Railway detecta `nixpacks.toml`
- ✅ Ejecuta build automático
- ✅ Deploy en ~2-3 minutos

### 6. URL generada
Railway te dará una URL como:
`https://empatic-api-production-xxxx.up.railway.app`

## 🔍 Verificar deploy:

1. **API**: `https://tu-app.up.railway.app/api`
2. **Health**: `https://tu-app.up.railway.app/health`
3. **Login admin**: 135 usuarios disponibles

## 💰 Plan gratuito:
- $5 crédito mensual
- Suficiente para aplicación pequeña-mediana
- Auto-sleep después de inactividad

## ⚡ Ventajas Railway:
- ✅ Detección automática PHP/Laravel
- ✅ Deploy continuo desde GitHub
- ✅ Variables de entorno fáciles
- ✅ Logs en tiempo real
- ✅ SSL automático
- ✅ Custom domains gratis