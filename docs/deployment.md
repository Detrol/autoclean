# Deployment Guide

Complete guide for deploying AutoClean to production.

## Table of Contents
- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Server Requirements](#server-requirements)
- [Deployment Steps](#deployment-steps)
- [Server Configuration](#server-configuration)
- [Optimization](#optimization)
- [Maintenance](#maintenance)
- [Monitoring](#monitoring)
- [Troubleshooting](#troubleshooting)

## Pre-Deployment Checklist

Before deploying to production:

- [ ] All tests passing (`composer test`)
- [ ] Code formatted (`./vendor/bin/pint`)
- [ ] `.env` configured for production (APP_ENV=production, APP_DEBUG=false)
- [ ] Database backed up
- [ ] SSL certificate obtained
- [ ] Domain name configured
- [ ] Email service configured (SMTP/SES)
- [ ] Error tracking setup (optional: Sentry, Bugsnag)
- [ ] Backup strategy in place
- [ ] Monitoring configured
- [ ] **CRITICAL**: Real admin user created (never use seeders in production!)
- [ ] **CRITICAL**: Test users deleted (admin@autoclean.se, employee@autoclean.se)
- [ ] Strong passwords used (16+ characters)

---

## Server Requirements

### Minimum Requirements

- **OS**: Ubuntu 22.04 LTS (recommended) or similar
- **PHP**: 8.2+ with required extensions
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Web Server**: Nginx or Apache
- **Node.js**: 18+ (for asset compilation)
- **Memory**: 1GB RAM minimum, 2GB recommended
- **Disk**: 5GB minimum
- **SSL**: Valid SSL certificate (Let's Encrypt recommended)

### Required PHP Extensions

```bash
php -m | grep -E 'pdo|mysql|mbstring|xml|bcmath|curl|gd|zip|intl|fileinfo|tokenizer|json|redis'
```

Required:
- pdo, pdo_mysql
- mbstring, xml
- bcmath, curl
- gd, zip
- intl, fileinfo
- tokenizer, json
- redis (optional but recommended)

---

## Deployment Steps

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mysql \
  php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl \
  php8.3-gd php8.3-zip php8.3-intl php8.3-redis

# Install MySQL
sudo apt install -y mysql-server

# Install Nginx
sudo apt install -y nginx

# Install Redis (recommended for cache/sessions)
sudo apt install -y redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Git
sudo apt install -y git
```

### 2. Application Deployment

```bash
# Create application directory
sudo mkdir -p /var/www/autoclean
cd /var/www/autoclean

# Clone repository
sudo git clone https://github.com/yourusername/autoclean.git .

# Set permissions
sudo chown -R www-data:www-data /var/www/autoclean
sudo chmod -R 755 /var/www/autoclean
sudo chmod -R 775 storage bootstrap/cache

# Install PHP dependencies (no dev packages)
composer install --optimize-autoloader --no-dev

# Install and build assets
npm ci
npm run build

# Create .env file
cp .env.example .env
php artisan key:generate

# Edit .env with production values
nano .env
```

### 3. Database Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p

CREATE DATABASE autoclean_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'autoclean'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON autoclean_production.* TO 'autoclean'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# WARNING: Do NOT seed in production!
# Seeders create test users with weak passwords
```

**Create Your First Admin User**:

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Your Name';
$user->email = 'your@email.com';
$user->password = bcrypt('strong_secure_password_here');
$user->is_admin = true;
$user->email_verified_at = now();
$user->save();
```

Press `Ctrl+D` to exit.

**Security Notes**:
- Use a strong password (16+ characters, mix of upper/lower/numbers/symbols)
- Use your real email address for password resets
- Never use seeders in production (they contain `admin@autoclean.se` / `password`)

### 4. Nginx Configuration

Create `/etc/nginx/sites-available/autoclean`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/autoclean/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Logging
    access_log /var/log/nginx/autoclean_access.log;
    error_log /var/log/nginx/autoclean_error.log;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Asset caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript application/json;
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/autoclean /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (certbot installs cron job automatically)
sudo certbot renew --dry-run
```

### 6. Task Scheduler (Cron)

```bash
# Edit crontab for www-data user
sudo crontab -e -u www-data

# Add Laravel scheduler
* * * * * cd /var/www/autoclean && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Queue Worker (Systemd)

Create `/etc/systemd/system/autoclean-worker.service`:

```ini
[Unit]
Description=AutoClean Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
ExecStart=/usr/bin/php /var/www/autoclean/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --timeout=60

StandardOutput=journal
StandardError=journal
SyslogIdentifier=autoclean-worker

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable autoclean-worker
sudo systemctl start autoclean-worker
sudo systemctl status autoclean-worker
```

### 8. Production Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear caches if needed
php artisan optimize:clear

# Optimize autoloader
composer install --optimize-autoloader --no-dev --classmap-authoritative
```

---

## Server Configuration

### PHP-FPM Optimization

Edit `/etc/php/8.3/fpm/pool.d/www.conf`:

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

php_admin_value[error_log] = /var/log/php-fpm/www-error.log
php_admin_flag[log_errors] = on
```

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 60
date.timezone = UTC

opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.save_comments=1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

### MySQL Optimization

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Performance
max_connections = 150
innodb_buffer_pool_size = 512M
innodb_log_file_size = 128M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Logging (disable in production for performance)
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

### Redis Configuration

Edit `/etc/redis/redis.conf`:

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

Restart Redis:
```bash
sudo systemctl restart redis-server
```

---

## Optimization

### Application Caching

```bash
# Production caching
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Event caching (if using many events)
php artisan event:cache
```

### Asset Optimization

```bash
# Production build with minification
npm run build

# Verify assets
ls -lh public/build
```

### Database Indexing

Ensure all migrations include proper indexes:
- Foreign keys
- Frequently queried columns
- Composite indexes for common queries

### OPcache

Verify OPcache is enabled:
```bash
php -i | grep opcache.enable
```

Should show:
```
opcache.enable => On => On
```

---

## Maintenance

### Deployment Updates

```bash
# Pull latest code
cd /var/www/autoclean
sudo -u www-data git pull origin main

# Update dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm ci
sudo -u www-data npm run build

# Run migrations
sudo -u www-data php artisan migrate --force

# Clear and rebuild caches
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Restart services
sudo systemctl restart php8.3-fpm
sudo systemctl restart autoclean-worker
```

### Database Backups

**Automated Daily Backup**:

Create `/usr/local/bin/backup-autoclean.sh`:

```bash
#!/bin/bash

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/backups/autoclean"
DB_NAME="autoclean_production"
DB_USER="autoclean"
DB_PASS="YOUR_PASSWORD"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$TIMESTAMP.sql.gz

# Backup application files
tar -czf $BACKUP_DIR/app_$TIMESTAMP.tar.gz /var/www/autoclean/storage

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
find $BACKUP_DIR -name "app_*.tar.gz" -mtime +7 -delete
```

Make executable and add to cron:
```bash
sudo chmod +x /usr/local/bin/backup-autoclean.sh
sudo crontab -e

# Add daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-autoclean.sh >> /var/log/backup-autoclean.log 2>&1
```

### Log Rotation

Create `/etc/logrotate.d/autoclean`:

```
/var/www/autoclean/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    missingok
    create 0640 www-data www-data
    sharedscripts
    postrotate
        php /var/www/autoclean/artisan queue:restart > /dev/null 2>&1 || true
    endscript
}
```

---

## Monitoring

### Application Monitoring

**Laravel Telescope** (Development/Staging):
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Error Tracking** (Production):
- Sentry: `composer require sentry/sentry-laravel`
- Bugsnag: `composer require bugsnag/bugsnag-laravel`

### Server Monitoring

**Check Application Status**:
```bash
# Check queue worker
sudo systemctl status autoclean-worker

# Check PHP-FPM
sudo systemctl status php8.3-fpm

# Check Nginx
sudo systemctl status nginx

# Check MySQL
sudo systemctl status mysql

# Check disk space
df -h

# Check memory usage
free -h

# Check running processes
ps aux | grep php
```

**Monitoring Tools**:
- **New Relic**: Application performance monitoring
- **DataDog**: Infrastructure and application monitoring
- **UptimeRobot**: Uptime monitoring (free tier available)

---

## Troubleshooting

### Common Issues

**Issue**: 500 Internal Server Error

**Solution**:
```bash
# Check Laravel logs
tail -f /var/www/autoclean/storage/logs/laravel.log

# Check Nginx error log
tail -f /var/log/nginx/autoclean_error.log

# Check PHP-FPM log
tail -f /var/log/php8.3-fpm.log

# Verify permissions
sudo chown -R www-data:www-data /var/www/autoclean/storage
sudo chmod -R 775 /var/www/autoclean/storage
```

**Issue**: Assets not loading

**Solution**:
```bash
# Rebuild assets
npm run build

# Clear cache
php artisan optimize:clear

# Check Nginx configuration
sudo nginx -t
```

**Issue**: Queue not processing

**Solution**:
```bash
# Check worker status
sudo systemctl status autoclean-worker

# Restart worker
sudo systemctl restart autoclean-worker

# Check for failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

**Issue**: Database connection errors

**Solution**:
```bash
# Test database connection
php artisan db:show

# Check MySQL status
sudo systemctl status mysql

# Verify credentials in .env
cat .env | grep DB_
```

---

## Zero-Downtime Deployment

For production environments requiring zero downtime:

1. **Use Deployer or Envoy**
2. **Implement Blue-Green Deployment**
3. **Use Queue Workers for Long-Running Tasks**
4. **Implement Health Checks**

**Example with Envoy**:

```bash
# Install Envoy
composer global require laravel/envoy

# Create Envoy.blade.php in project root
# Then deploy with:
envoy run deploy
```

---

## Security Best Practices

1. **Keep Software Updated**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Configure Firewall (UFW)**
   ```bash
   sudo ufw allow 22/tcp
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw enable
   ```

3. **Disable Directory Listing**
   Already configured in Nginx

4. **Hide Server Information**
   Add to Nginx: `server_tokens off;`

5. **Use Environment Variables**
   Never commit `.env` to version control

6. **Regular Security Audits**
   ```bash
   composer audit
   npm audit
   ```

---

## Next Steps

- Review [Configuration Guide](configuration.md) for environment settings
- Check [Monitoring](#monitoring) for application health
- Setup [Backups](#database-backups) for disaster recovery
- Configure [Error Tracking](#application-monitoring) for production

---

**Navigation**: [← Testing](testing.md) | [Back to Documentation](README.md) | [User Guide →](user-guide.md)
