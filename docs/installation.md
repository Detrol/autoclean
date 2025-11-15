# Installation Guide

Complete installation instructions for AutoClean in various environments.

## Table of Contents
- [System Requirements](#system-requirements)
- [Local Development Setup](#local-development-setup)
- [Production Installation](#production-installation)
- [Database Setup](#database-setup)
- [Initial Configuration](#initial-configuration)
- [Verification](#verification)
- [Troubleshooting](#troubleshooting)

## System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Node.js**: 18.x or higher
- **Composer**: 2.x
- **Memory**: 512MB RAM
- **Disk Space**: 500MB

### Recommended Requirements
- **PHP**: 8.3
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Node.js**: 20.x LTS
- **Memory**: 1GB RAM
- **Disk Space**: 2GB
- **Cache**: Redis (optional but recommended)

### PHP Extensions Required
```bash
php -m | grep -E 'pdo|mysql|mbstring|xml|bcmath|curl|gd|zip|intl'
```

Required extensions:
- PDO
- pdo_mysql
- mbstring
- xml
- bcmath
- curl
- gd
- zip
- intl
- fileinfo
- tokenizer
- json

## Local Development Setup

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/autoclean.git
cd autoclean
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

This will install all Laravel and PHP packages defined in `composer.json`.

### Step 3: Install JavaScript Dependencies

```bash
npm install
```

This installs Vite, Tailwind CSS, and other frontend dependencies.

### Step 4: Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Database

Edit `.env` file and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autoclean
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 6: Create Database

```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE autoclean CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 7: Run Migrations and Seeders

```bash
# Run all migrations
php artisan migrate

# Seed the database with sample data (optional but recommended for development)
php artisan db:seed
```

The seeder creates:
- Default admin user (admin@example.com / password)
- Sample stations
- Sample tasks and schedules
- Sample inventory items
- Sample time logs

### Step 8: Build Assets

```bash
# Development build with hot reload
npm run dev

# Or for a one-time build
npm run build
```

### Step 9: Start Development Server

You have two options:

**Option A: Full Development Stack (Recommended)**
```bash
composer dev
```
This starts:
- Laravel development server (http://localhost:8000)
- Vite dev server with hot reload
- Queue worker
- Log viewer (Pail)

**Option B: Individual Services**
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev

# Terminal 3: Queue worker (if using queues)
php artisan queue:listen

# Terminal 4: Log viewer (optional)
php artisan pail
```

### Step 10: Access Application

Open your browser and navigate to:
```
http://localhost:8000
```

**Default Login Credentials** (from seeders):
- **Email**: admin@example.com
- **Password**: password

## Production Installation

### Prerequisites

1. **Server Requirements**:
   - Ubuntu 22.04 LTS or similar
   - Root or sudo access
   - Domain name (optional but recommended)

2. **Install Required Software**:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3 and extensions
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql \
  php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl \
  php8.3-gd php8.3-zip php8.3-intl php8.3-redis

# Install MySQL
sudo apt install -y mysql-server

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx
sudo apt install -y nginx

# Install Redis (optional)
sudo apt install -y redis-server
```

### Production Setup

1. **Clone and Configure**:

```bash
cd /var/www
sudo git clone https://github.com/yourusername/autoclean.git
cd autoclean

# Set ownership
sudo chown -R www-data:www-data /var/www/autoclean
sudo chmod -R 755 /var/www/autoclean
sudo chmod -R 775 storage bootstrap/cache
```

2. **Install Dependencies**:

```bash
# Install PHP dependencies (no dev packages)
composer install --optimize-autoloader --no-dev

# Install and build JavaScript assets
npm install
npm run build
```

3. **Configure Environment**:

```bash
cp .env.example .env
php artisan key:generate

# Edit .env for production
nano .env
```

Production `.env` example:
```env
APP_NAME=AutoClean
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autoclean_production
DB_USERNAME=autoclean_user
DB_PASSWORD=secure_password_here

CACHE_DRIVER=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

4. **Database Setup**:

```bash
# Create production database
mysql -u root -p
CREATE DATABASE autoclean_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'autoclean_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON autoclean_production.* TO 'autoclean_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# WARNING: Do NOT run seeders in production!
# Seeders contain test users with weak passwords
# See "Create Admin User" section below instead
```

5. **Configure Nginx**:

```nginx
# /etc/nginx/sites-available/autoclean
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/autoclean/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/autoclean /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

6. **Setup Task Scheduler** (cron):

```bash
# Edit crontab for www-data user
sudo crontab -e -u www-data

# Add this line:
* * * * * cd /var/www/autoclean && php artisan schedule:run >> /dev/null 2>&1
```

7. **Setup Queue Worker** (systemd):

Create `/etc/systemd/system/autoclean-queue.service`:
```ini
[Unit]
Description=AutoClean Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/autoclean/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable autoclean-queue
sudo systemctl start autoclean-queue
```

8. **Optimize Application**:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Database Setup

### MySQL Configuration

Recommended MySQL configuration for production (`/etc/mysql/mysql.conf.d/mysqld.cnf`):

```ini
[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
max_connections = 150
innodb_buffer_pool_size = 256M
```

### Database Migrations

AutoClean uses Laravel migrations for database schema management.

```bash
# Check migration status
php artisan migrate:status

# Run pending migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database (WARNING: destroys all data)
php artisan migrate:fresh

# Reset and seed
php artisan migrate:fresh --seed
```

## Initial Configuration

### Create Admin User (Production)

**IMPORTANT**: Never use seeders in production as they contain test users with weak passwords (`password`).

**Method 1: Using Tinker (Recommended for First Admin)**

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Your Name';
$user->email = 'your@email.com';
$user->password = bcrypt('your_strong_password_here');
$user->is_admin = true;
$user->email_verified_at = now();
$user->save();
```

Press `Ctrl+D` to exit Tinker.

**Method 2: One-liner**

```bash
php artisan tinker --execute="App\Models\User::create(['name' => 'Your Name', 'email' => 'your@email.com', 'password' => bcrypt('your_password'), 'is_admin' => true, 'email_verified_at' => now()]);"
```

**Security Best Practices**:
- ✅ Use a strong, unique password (16+ characters)
- ✅ Use your real email address
- ✅ Never commit passwords to version control
- ✅ Change password immediately after first login
- ⚠️ Delete or disable test users created by seeders:
  ```bash
  # Check for test users
  php artisan tinker --execute="App\Models\User::whereIn('email', ['admin@autoclean.se', 'employee@autoclean.se'])->get();"

  # Delete test users (if they exist)
  php artisan tinker --execute="App\Models\User::whereIn('email', ['admin@autoclean.se', 'employee@autoclean.se'])->delete();"
  ```

### Configure Settings

After logging in as admin:
1. Navigate to Settings
2. Configure:
   - System name
   - Clock-in requirements
   - Task rollover settings
   - Email notifications

## Verification

### Test Installation

```bash
# Run tests
php artisan test

# Check application status
php artisan about

# Verify queue is working
php artisan queue:monitor

# Check scheduled tasks
php artisan schedule:list
```

### Browser Tests

1. **Login**: Verify you can log in with admin credentials
2. **Create Station**: Test creating a new station
3. **Create Task**: Test task creation with recurrence
4. **Clock In**: Test time tracking functionality
5. **View Reports**: Generate and export a time report

## Troubleshooting

### Permission Issues

```bash
# Fix storage and cache permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection Errors

```bash
# Test database connection
php artisan db:show

# Clear config cache
php artisan config:clear
```

### Asset Build Errors

```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Queue Not Processing

```bash
# Check queue status
php artisan queue:monitor

# Restart queue worker
sudo systemctl restart autoclean-queue

# Or manually
php artisan queue:restart
php artisan queue:work
```

### Cache Issues

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Livewire Issues

```bash
# Clear Livewire cache
php artisan livewire:clear

# Republish Livewire assets
php artisan livewire:publish --assets
```

### Common Errors

**Error**: "No application encryption key has been specified"
```bash
php artisan key:generate
```

**Error**: "SQLSTATE[HY000] [1045] Access denied"
- Check database credentials in `.env`
- Verify database user has correct permissions

**Error**: "Class 'PDO' not found"
```bash
sudo apt install php8.3-mysql
sudo systemctl restart php8.3-fpm
```

**Error**: "npm ERR! code ELIFECYCLE"
```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

## Next Steps

After successful installation:
1. Review [Configuration Guide](configuration.md)
2. Read [User Guide](user-guide.md) to understand features
3. Check [Development Guide](development.md) if contributing
4. Review [Deployment Guide](deployment.md) for production best practices

---

**Navigation**: [← Back to Documentation](README.md) | [Configuration →](configuration.md)
