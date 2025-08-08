# Guía de Deployment y DevOps

## Configuración de Producción

### Variables de Entorno Críticas

```env
# Aplicación
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_URL=https://tu-dominio.com

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=logrosls_prod
DB_USERNAME=logrosls_user
DB_PASSWORD=PASSWORD_SEGURO

# Cache y Sesiones (Redis recomendado)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (para notificaciones)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null

# Filament
FILAMENT_SHIELD_ENABLED=true
```

### Optimizaciones de Servidor

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/logrosls/public;

    index index.php;

    # Compresión
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    # Cache de assets estáticos
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;

        # Timeout para reportes largos
        fastcgi_read_timeout 300;
    }

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Seguridad
    location ~ /\.ht {
        deny all;
    }
}
```

#### Configuración PHP (php.ini)

```ini
; Memoria para reportes grandes
memory_limit = 512M

; Tiempo de ejecución para procesos largos
max_execution_time = 300

; Upload de archivos
upload_max_filesize = 20M
post_max_size = 25M

; Optimizaciones de OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
```

## Scripts de Deployment

### Deploy Script Básico

```bash
#!/bin/bash
# deploy.sh

echo "🚀 Iniciando deployment de LogrosLS"

# Activar modo mantenimiento
php artisan down --message="Actualizando sistema..." --retry=60

# Backup de base de datos
echo "📦 Creando backup..."
mysqldump -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > "backup_$(date +%Y%m%d_%H%M%S).sql"

# Actualizar código
echo "📥 Actualizando código..."
git pull origin main

# Instalar dependencias
echo "📚 Instalando dependencias..."
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Ejecutar migraciones
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force

# Optimizar aplicación
echo "⚡ Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# Limpiar cache
php artisan cache:clear
php artisan queue:restart

# Desactivar modo mantenimiento
php artisan up

echo "✅ Deployment completado exitosamente"
```

### Deploy con Zero Downtime

```bash
#!/bin/bash
# zero-downtime-deploy.sh

RELEASE_DIR="/var/www/releases/$(date +%Y%m%d%H%M%S)"
CURRENT_DIR="/var/www/logrosls"
SHARED_DIR="/var/www/shared"

echo "🔄 Preparando nuevo release..."

# Crear directorio del release
mkdir -p $RELEASE_DIR

# Clonar código
git clone --depth 1 https://github.com/tu-usuario/logrosls.git $RELEASE_DIR

cd $RELEASE_DIR

# Crear enlaces simbólicos a directorios compartidos
ln -nfs $SHARED_DIR/.env .env
ln -nfs $SHARED_DIR/storage/app/public storage/app/public
ln -nfs $SHARED_DIR/storage/logs storage/logs

# Instalar dependencias
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Ejecutar migraciones con el nuevo código
php artisan migrate --force

# Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Cambiar enlace simbólico atómicamente
ln -nfs $RELEASE_DIR $CURRENT_DIR

# Reiniciar servicios
sudo systemctl reload php8.2-fpm
php artisan queue:restart

echo "✅ Zero-downtime deployment completado"

# Limpiar releases antiguos (mantener últimos 3)
ls -1dt /var/www/releases/* | tail -n +4 | xargs rm -rf
```

## Monitoreo y Logging

### Configuración de Logs

```php
// config/logging.php - Configuración adicional
'channels' => [
    'evaluaciones' => [
        'driver' => 'daily',
        'path' => storage_path('logs/evaluaciones.log'),
        'level' => 'info',
        'days' => 30,
    ],

    'reportes' => [
        'driver' => 'daily',
        'path' => storage_path('logs/reportes.log'),
        'level' => 'debug',
        'days' => 7,
    ],

    'performance' => [
        'driver' => 'daily',
        'path' => storage_path('logs/performance.log'),
        'level' => 'warning',
        'days' => 14,
    ],
],
```

### Health Check Script

```bash
#!/bin/bash
# health-check.sh

echo "🔍 Verificando salud del sistema..."

# Verificar conectividad de base de datos
php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB: OK';"

# Verificar Redis
redis-cli ping

# Verificar espacio en disco
df -h | grep -E "(80|90)%" && echo "⚠️ Espacio en disco bajo"

# Verificar memoria
free -m | awk 'NR==2{printf "Memoria: %.1f%% usada\n", $3*100/$2}'

# Verificar procesos PHP-FPM
ps aux | grep -c php-fpm | awk '{if($1 < 5) print "⚠️ Pocos procesos PHP-FPM"}'

# Verificar colas
php artisan queue:monitor | grep "No jobs"

echo "✅ Health check completado"
```

### Monitoreo con Supervisor (Colas)

```ini
; /etc/supervisor/conf.d/logrosls-worker.conf
[program:logrosls-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/logrosls/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/logrosls/storage/logs/worker.log
stopwaitsecs=3600
```

## Backup y Recuperación

### Script de Backup Automatizado

```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/backups/logrosls"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="logrosls_prod"

mkdir -p $BACKUP_DIR

echo "📦 Iniciando backup completo..."

# Backup de base de datos
mysqldump -u $DB_USERNAME -p$DB_PASSWORD \
    --single-transaction \
    --routines \
    --triggers \
    $DB_NAME > "$BACKUP_DIR/db_$DATE.sql"

# Backup de archivos de aplicación (storage)
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" \
    /var/www/logrosls/storage/app \
    /var/www/logrosls/storage/framework/sessions

# Backup de configuración
cp /var/www/logrosls/.env "$BACKUP_DIR/env_$DATE"

# Comprimir todo
tar -czf "$BACKUP_DIR/complete_backup_$DATE.tar.gz" \
    "$BACKUP_DIR/db_$DATE.sql" \
    "$BACKUP_DIR/storage_$DATE.tar.gz" \
    "$BACKUP_DIR/env_$DATE"

# Limpiar archivos temporales
rm "$BACKUP_DIR/db_$DATE.sql" "$BACKUP_DIR/storage_$DATE.tar.gz" "$BACKUP_DIR/env_$DATE"

# Limpiar backups antiguos (mantener 30 días)
find $BACKUP_DIR -name "complete_backup_*.tar.gz" -mtime +30 -delete

echo "✅ Backup completado: complete_backup_$DATE.tar.gz"

# Subir a almacenamiento externo (opcional)
# aws s3 cp "$BACKUP_DIR/complete_backup_$DATE.tar.gz" s3://tu-bucket/backups/
```

### Cron Jobs Recomendados

```bash
# crontab -e

# Backup diario a las 2:00 AM
0 2 * * * /var/www/scripts/backup.sh >> /var/log/backup.log 2>&1

# Health check cada 5 minutos
*/5 * * * * /var/www/scripts/health-check.sh >> /var/log/health.log 2>&1

# Limpiar logs antiguos
0 1 * * 0 find /var/www/logrosls/storage/logs -name "*.log" -mtime +30 -delete

# Optimizar base de datos semanal
0 3 * * 0 mysql -u $DB_USERNAME -p$DB_PASSWORD -e "OPTIMIZE TABLE estudiante_logros, logros, estudiantes;"
```

## Seguridad en Producción

### Configuración SSL (Let's Encrypt)

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com

# Auto-renovación
sudo crontab -e
0 12 * * * /usr/bin/certbot renew --quiet
```

### Hardening del Servidor

```bash
#!/bin/bash
# server-hardening.sh

echo "🔒 Aplicando medidas de seguridad..."

# Actualizar sistema
apt update && apt upgrade -y

# Configurar firewall
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw --force enable

# Configurar fail2ban
apt install fail2ban -y
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Deshabilitar root login SSH
sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
systemctl restart ssh

# Configurar límites de rate limiting en Nginx
# (agregar a la configuración del servidor)
echo "limit_req_zone \$binary_remote_addr zone=login:10m rate=5r/m;" >> /etc/nginx/nginx.conf

echo "✅ Hardening completado"
```

---

> **Importante**: Adaptar estos scripts a tu infraestructura específica. Probar siempre en ambiente de staging antes de producción.
