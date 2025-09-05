# DOCUMENTACIÓN DE DESPLIEGUE - LOGROSLS

## 📋 **OVERVIEW**

Esta guía proporciona instrucciones detalladas para el despliegue de LogrosLS en diferentes entornos, desde desarrollo local hasta producción empresarial.

---

## 🏗️ **ARQUITECTURA DE DESPLIEGUE**

### **Entornos Disponibles**

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   DEVELOPMENT   │    │     STAGING     │    │   PRODUCTION    │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ • SQLite        │    │ • MySQL         │    │ • MySQL/MariaDB │
│ • Telescope     │    │ • Redis Cache   │    │ • Redis Cluster │
│ • Debug Bar     │    │ • Queue Workers │    │ • Load Balancer │
│ • File Sessions │    │ • SSL/TLS       │    │ • CDN           │
│ • Local Storage │    │ • Backups       │    │ • Monitoring    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

---

## 🔧 **CONFIGURACIÓN POR ENTORNOS**

### **Desarrollo Local**

#### **Requisitos del Sistema**

| Componente | Versión | Instalación |
|------------|---------|-------------|
| PHP | 8.2+ | XAMPP/Laragon/Herd |
| Composer | 2.5+ | getcomposer.org |
| Node.js | 18+ | nodejs.org |
| MySQL | 8.0+ | Incluido en XAMPP |

#### **Variables de Entorno (.env)**

```env
# Aplicación
APP_NAME="LogrosLS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_TIMEZONE=America/Bogota
APP_URL=http://localhost:8000

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=logrosls_dev
DB_USERNAME=root
DB_PASSWORD=

# Cache y Sesiones (Desarrollo)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail (Testing)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@liceo.edu.co"
MAIL_FROM_NAME="${APP_NAME}"

# Filament
FILAMENT_SHIELD_ENABLED=true

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

#### **Comandos de Instalación**

```bash
# 1. Clonar y configurar
git clone https://github.com/jumahl/logrosls.git
cd logrosls
composer install
npm install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate

# 3. Base de datos
mysql -u root -p -e "CREATE DATABASE logrosls_dev"
php artisan migrate
php artisan db:seed

# 4. Permisos de Filament
php artisan shield:generate --all

# 5. Iniciar desarrollo
composer run dev
```

---

### **Staging/Testing**

#### **Variables de Entorno (.env)**

```env
# Aplicación
APP_NAME="LogrosLS Staging"
APP_ENV=staging
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://staging.liceo.edu.co

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=logrosls_staging
DB_USERNAME=staging_user
DB_PASSWORD=secure_password_staging

# Cache y Performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=notifications@liceo.edu.co
MAIL_PASSWORD=app_password
MAIL_ENCRYPTION=tls

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=info
```

#### **Setup Staging**

```bash
# 1. Servidor
sudo apt update
sudo apt install nginx mysql-server redis-server php8.3-fpm php8.3-mysql php8.3-redis

# 2. PHP Extensions
sudo apt install php8.3-gd php8.3-zip php8.3-xml php8.3-curl php8.3-mbstring

# 3. Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# 5. Configurar aplicación
git clone https://github.com/jumahl/logrosls.git /var/www/logrosls
cd /var/www/logrosls
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 6. Permisos
sudo chown -R www-data:www-data /var/www/logrosls
sudo chmod -R 755 /var/www/logrosls
sudo chmod -R 775 /var/www/logrosls/storage
sudo chmod -R 775 /var/www/logrosls/bootstrap/cache
```

---

### **Producción**

#### **Variables de Entorno (.env)**

```env
# Aplicación
APP_NAME="LogrosLS"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://logros.liceo.edu.co

# Base de Datos Principal
DB_CONNECTION=mysql
DB_HOST=mysql-master.internal
DB_PORT=3306
DB_DATABASE=logrosls_prod
DB_USERNAME=logrosls_user
DB_PASSWORD=ultra_secure_password_2024

# Base de Datos Lectura (Opcional)
DB_READ_HOST=mysql-slave.internal

# Redis Cluster
REDIS_HOST=redis-cluster.internal
REDIS_PASSWORD=redis_secure_password
REDIS_PORT=6379
REDIS_CLUSTER=true

# Cache y Performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# CDN y Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=AKIA...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=logrosls-storage

# Mail Producción
MAIL_MAILER=ses
MAIL_FROM_ADDRESS="noreply@liceo.edu.co"
MAIL_FROM_NAME="Sistema LogrosLS"

# Monitoring
LOG_CHANNEL=stack
LOG_LEVEL=warning
SENTRY_LARAVEL_DSN=https://...@sentry.io/...

# Security
SANCTUM_STATEFUL_DOMAINS=logros.liceo.edu.co
SESSION_SECURE_COOKIE=true
```

---

## 🐳 **CONTAINERIZACIÓN CON DOCKER**

### **Dockerfile**

```dockerfile
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nginx \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install and build assets
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### **docker-compose.yml**

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: logrosls:latest
    container_name: logrosls-app
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./storage:/var/www/storage
      - ./bootstrap/cache:/var/www/bootstrap/cache
    depends_on:
      - mysql
      - redis
    networks:
      - logrosls-network

  mysql:
    image: mysql:8.0
    container_name: logrosls-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: logrosls
      MYSQL_USER: logrosls
      MYSQL_PASSWORD: password
      MYSQL_ROOT_PASSWORD: rootpassword
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    networks:
      - logrosls-network

  redis:
    image: redis:7-alpine
    container_name: logrosls-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - logrosls-network

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: logrosls-queue
    restart: unless-stopped
    command: php artisan queue:work --sleep=3 --tries=3
    volumes:
      - ./storage:/var/www/storage
    depends_on:
      - mysql
      - redis
    networks:
      - logrosls-network

volumes:
  mysql_data:
    driver: local

networks:
  logrosls-network:
    driver: bridge
```

### **Comandos Docker**

```bash
# Construir y ejecutar
docker-compose up -d --build

# Ver logs
docker-compose logs -f app

# Ejecutar comandos Laravel
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# Backup de base de datos
docker-compose exec mysql mysqldump -u root -p logrosls > backup.sql

# Detener servicios
docker-compose down
```

---

## 🔐 **CONFIGURACIÓN DE NGINX**

### **Configuración Principal**

```nginx
# /etc/nginx/sites-available/logrosls
server {
    listen 80;
    listen [::]:80;
    server_name logros.liceo.edu.co;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name logros.liceo.edu.co;
    root /var/www/logrosls/public;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/logrosls.crt;
    ssl_certificate_key /etc/ssl/private/logrosls.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Logs
    access_log /var/log/nginx/logrosls.access.log;
    error_log /var/log/nginx/logrosls.error.log;

    # Index
    index index.php;

    # Character Set
    charset utf-8;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Security
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # File size limit
    client_max_body_size 100M;
}
```

### **Optimizaciones de Performance**

```nginx
# /etc/nginx/nginx.conf
http {
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        application/atom+xml
        application/javascript
        application/json
        application/rss+xml
        application/vnd.ms-fontobject
        application/x-font-ttf
        application/x-web-app-manifest+json
        application/xhtml+xml
        application/xml
        font/opentype
        image/svg+xml
        image/x-icon
        text/css
        text/plain
        text/x-component;

    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=30r/m;

    # Buffer Sizes
    client_body_buffer_size 10K;
    client_header_buffer_size 1k;
    large_client_header_buffers 2 1k;

    # Timeouts
    client_body_timeout 12;
    client_header_timeout 12;
    keepalive_timeout 15;
    send_timeout 10;
}
```

---

## 📊 **BASE DE DATOS EN PRODUCCIÓN**

### **Configuración MySQL**

```sql
-- my.cnf optimizations
[mysqld]
# Basic Settings
default-storage-engine = innodb
sql-mode = "STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"

# Memory Settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_log_buffer_size = 16M
innodb_flush_log_at_trx_commit = 2

# Connection Settings
max_connections = 200
max_connect_errors = 10000
thread_cache_size = 50
table_open_cache = 2048

# Query Cache
query_cache_type = 1
query_cache_size = 128M
query_cache_limit = 1M

# Slow Query Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### **Índices Optimizados**

```sql
-- Índices críticos para performance
ALTER TABLE desempenos_materia 
ADD INDEX idx_estudiante_periodo (estudiante_id, periodo_id),
ADD INDEX idx_materia_periodo (materia_id, periodo_id),
ADD INDEX idx_periodo_estado (periodo_id, estado);

ALTER TABLE estudiante_logros 
ADD INDEX idx_desempeno_logro (desempeno_materia_id, logro_id),
ADD INDEX idx_logro_alcanzado (logro_id, alcanzado);

ALTER TABLE estudiantes 
ADD INDEX idx_grado_activo (grado_id, activo),
ADD INDEX idx_documento (documento),
ADD INDEX idx_nombre_apellido (nombre, apellido);

-- Índices para reportes
ALTER TABLE estudiantes 
ADD FULLTEXT INDEX idx_fulltext_estudiante (nombre, apellido, documento);
```

### **Backup Strategy**

```bash
#!/bin/bash
# backup-logrosls.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/logrosls"
DB_NAME="logrosls_prod"
DB_USER="backup_user"
DB_PASS="backup_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# MySQL Backup
mysqldump -u $DB_USER -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    $DB_NAME > $BACKUP_DIR/logrosls_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/logrosls_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz \
    /var/www/logrosls/storage/app \
    /var/www/logrosls/public/uploads

# Cleanup old backups (keep 30 days)
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

# Upload to S3 (optional)
aws s3 cp $BACKUP_DIR/logrosls_$DATE.sql.gz \
    s3://logrosls-backups/daily/

echo "Backup completed: logrosls_$DATE.sql.gz"
```

---

## ⚡ **OPTIMIZACIÓN DE PERFORMANCE**

### **Redis Configuration**

```redis
# redis.conf
maxmemory 512mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Network
timeout 300
tcp-keepalive 300

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log
```

### **Queue Workers con Supervisor**

```ini
# /etc/supervisor/conf.d/logrosls-queue.conf
[program:logrosls-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/logrosls/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/supervisor/logrosls-queue.log
stopwaitsecs=3600
```

### **Optimizaciones Laravel**

```bash
# Comandos de optimización para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

# Composer optimizations
composer install --no-dev --optimize-autoloader --classmap-authoritative

# OPcache configuration (php.ini)
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

---

## 📈 **MONITOREO Y ALERTAS**

### **Health Checks**

```php
// routes/web.php
Route::get('/health', function () {
    $checks = [
        'database' => false,
        'redis' => false,
        'storage' => false,
        'queue' => false
    ];
    
    try {
        // Database check
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (Exception $e) {}
    
    try {
        // Redis check
        Redis::ping();
        $checks['redis'] = true;
    } catch (Exception $e) {}
    
    try {
        // Storage check
        Storage::disk('local')->put('health-check.txt', 'ok');
        Storage::disk('local')->delete('health-check.txt');
        $checks['storage'] = true;
    } catch (Exception $e) {}
    
    try {
        // Queue check (last job processed in last 5 minutes)
        $recentJob = DB::table('jobs')->where('created_at', '>', now()->subMinutes(5))->exists();
        $checks['queue'] = $recentJob;
    } catch (Exception $e) {}
    
    $allHealthy = !in_array(false, $checks);
    
    return response()->json([
        'status' => $allHealthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now()->toISOString()
    ], $allHealthy ? 200 : 503);
});
```

### **Logging Configuration**

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
    
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'LogrosLS Bot',
        'emoji' => ':boom:',
        'level' => 'error',
    ],
    
    'performance' => [
        'driver' => 'daily',
        'path' => storage_path('logs/performance.log'),
        'level' => 'info',
        'days' => 7,
    ],
]
```

---

## 🚀 **CI/CD PIPELINE**

### **GitHub Actions**

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv
        coverage: none
    
    - name: Install dependencies
      run: composer install --no-dev --optimize-autoloader
    
    - name: Copy environment file
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"
    
    - name: Generate app key
      run: php artisan key:generate
    
    - name: Run tests
      run: php artisan test

  deploy:
    needs: tests
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - name: Deploy to server
      uses: appleboy/ssh-action@v0.1.7
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/logrosls
          git pull origin main
          composer install --no-dev --optimize-autoloader
          npm install && npm run build
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          sudo systemctl reload nginx
          sudo supervisorctl restart logrosls-queue:*
```

---

## 📋 **CHECKLIST DE DESPLIEGUE**

### **Pre-Despliegue**

- [ ] Tests pasando correctamente
- [ ] Variables de entorno configuradas
- [ ] Base de datos respaldada
- [ ] SSL certificados válidos
- [ ] Dependencias actualizadas
- [ ] Assets compilados
- [ ] Permisos de archivos configurados

### **Durante el Despliegue**

- [ ] Modo de mantenimiento activado
- [ ] Código desplegado
- [ ] Migraciones ejecutadas
- [ ] Cache limpiado y regenerado
- [ ] Queue workers reiniciados
- [ ] Health checks pasando

### **Post-Despliegue**

- [ ] Funcionalidades críticas probadas
- [ ] Logs monitoreados
- [ ] Performance verificada
- [ ] Backup post-despliegue
- [ ] Equipo notificado
- [ ] Documentación actualizada

---

**Última actualización**: Septiembre 2025  
**Versión**: 1.0  
**Mantenido por**: Equipo DevOps LogrosLS
