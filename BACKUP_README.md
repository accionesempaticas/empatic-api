# 🛡️ Sistema de Backup y Restauración de Base de Datos

## 📋 Descripción

Sistema automático de backup para la base de datos SQLite de Empathic Actions Portal que:
- ✅ Crea backups automáticos cada 12 horas (6:00 AM y 6:00 PM)
- ✅ Preserva datos existentes en todos los despliegues
- ✅ Mantiene historial de backups (14 copias por defecto)
- ✅ Incluye herramientas de backup y restauración manual
- ✅ **Backup persistente en Railway Volume**
- ✅ **Soporte para almacenamiento en la nube (AWS S3, Google Cloud)**

## 🤖 Backup Automático

### Configuración
- **Frecuencia**: Cada 12 horas (6:00 AM y 6:00 PM)
- **Retención**: 14 backups (una semana de historial)
- **Ubicación**: `storage/app/backups/`
- **Formato**: `database_backup_YYYY-MM-DD_HH-mm-ss.sqlite`

### Comandos Laravel

```bash
# Crear backup manual local
php artisan db:backup

# Crear backup manteniendo solo 10 copias
php artisan db:backup --keep=10

# Crear backup persistente (Railway Volume)
php artisan db:cloud-backup --provider=local

# Crear backup en AWS S3 (requiere configuración)
php artisan db:cloud-backup --provider=s3

# Crear backup en Google Cloud Storage
php artisan db:cloud-backup --provider=google

# Limpiar logs antiguos
php artisan log:clear --keep=30
```

## 🔧 Scripts Manuales

### 1. Backup de Emergencia
```bash
# Crear backup inmediato
./backup-manual.sh
```

### 2. Restauración de Backup
```bash
# Modo interactivo - muestra lista de backups
./restore-backup.sh

# Restaurar backup específico por número
./restore-backup.sh 1

# Restaurar backup específico por nombre
./restore-backup.sh database_backup_2024-10-14_18-00-00.sqlite
```

## 🚀 Integración con Railway

### Configuración de Volúmenes Persistentes

**IMPORTANTE**: Para que los backups persistan entre despliegues, debes configurar volúmenes en Railway:

1. **Ve a tu proyecto en Railway**
2. **Settings → Variables → Volumes**
3. **Agrega estos volúmenes**:
   ```
   Nombre: database-volume
   Mount Path: /app/database

   Nombre: backups-volume
   Mount Path: /app/persistent-backups
   ```

### Ubicaciones de Backup
- **Temporal**: `storage/app/backups/` (se pierde en cada despliegue)
- **Persistente**: `/app/persistent-backups/` (Railway Volume)
- **Nube**: AWS S3 / Google Cloud Storage (opcional)

### Script de Despliegue Seguro
El archivo `railway-start.sh` incluye:

1. **Backup pre-despliegue**: Crea backup antes de ejecutar migraciones
2. **Migraciones seguras**: Usa `--force` (preserva datos) en lugar de `--fresh`
3. **Seeder inteligente**: Solo crea admin si no existe (`firstOrCreate`)
4. **Scheduler automático**: Inicia el sistema de backups automáticos

### Flujo de Despliegue
```
🚀 Inicio
📦 Instalar dependencias
🎨 Build frontend
💾 Backup pre-despliegue (si existe data)
📋 Migraciones (preserva datos)
🌱 Asegurar admin existe
⏰ Iniciar scheduler
🌐 Iniciar servidor
```

## 📁 Estructura de Archivos

```
storage/app/backups/
├── database_backup_2024-10-14_06-00-01.sqlite
├── database_backup_2024-10-14_18-00-01.sqlite
├── emergency_backup_2024-10-14_15-30-45.sqlite
└── pre_restore_backup_2024-10-14_16-45-22.sqlite
```

## 🛠️ Comandos Útiles

### Verificar Scheduler
```bash
# Ver tareas programadas
php artisan schedule:list

# Ejecutar scheduler manualmente (desarrollo)
php artisan schedule:work

# Ejecutar scheduler una vez
php artisan schedule:run
```

### Monitoreo de Backups
```bash
# Listar backups
ls -lah storage/app/backups/

# Ver tamaño de base de datos actual
du -h database/database.sqlite

# Ver logs de backup
tail -f storage/logs/laravel.log | grep backup
```

## ⚠️ Consideraciones Importantes

### Preservación de Datos
- ✅ **SÍ usa**: `php artisan migrate --force`
- ❌ **NO uses**: `php artisan migrate:fresh` o `php artisan migrate:refresh`
- ✅ **Seeders seguros**: Usar `firstOrCreate()` en lugar de `create()`

### Seguridad
- Los backups se almacenan en `storage/app/backups/` (fuera del directorio público)
- Se crea backup de seguridad antes de cada restauración
- Los logs registran todas las operaciones de backup

### Monitoreo
- Verificar regularmente que los backups se están creando
- Monitorear espacio en disco para evitar problemas de almacenamiento
- Probar restauraciones periódicamente para verificar integridad

## 🆘 Recuperación de Emergencia

### Si se pierde la base de datos:
1. Listar backups disponibles: `./restore-backup.sh`
2. Seleccionar backup más reciente
3. Confirmar restauración
4. Ejecutar migraciones si es necesario: `php artisan migrate --force`

### Si fallan los backups automáticos:
1. Crear backup manual: `./backup-manual.sh`
2. Verificar que el scheduler esté funcionando: `php artisan schedule:list`
3. Revisar logs de errores: `tail storage/logs/laravel.log`

## 📞 Soporte

Para problemas con el sistema de backup:
1. Verificar permisos de escritura en `storage/app/backups/`
2. Comprobar espacio disponible en disco
3. Revisar logs de Laravel para errores específicos
4. Ejecutar backup manual para verificar funcionalidad