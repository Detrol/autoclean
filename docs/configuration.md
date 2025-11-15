# Configuration Guide

Comprehensive guide to configuring AutoClean for your environment.

## Table of Contents
- [Environment Variables](#environment-variables)
- [Application Settings](#application-settings)
- [Database Configuration](#database-configuration)
- [Cache Configuration](#cache-configuration)
- [Queue Configuration](#queue-configuration)
- [Mail Configuration](#mail-configuration)
- [Task Configuration](#task-configuration)
- [System Settings](#system-settings)
- [Security Configuration](#security-configuration)

## Environment Variables

AutoClean uses a `.env` file for environment-specific configuration. Never commit this file to version control.

### Core Application Settings

```env
# Application
APP_NAME=AutoClean
APP_ENV=local                    # local, staging, production
APP_KEY=base64:xxx               # Generate with: php artisan key:generate
APP_DEBUG=true                   # Set to false in production
APP_URL=http://localhost:8000    # Your application URL

# Timezone
APP_TIMEZONE=UTC                 # Set to your timezone (e.g., Europe/Stockholm, America/New_York)
```

### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autoclean
DB_USERNAME=root
DB_PASSWORD=

# For production, use strong credentials:
# DB_USERNAME=autoclean_user
# DB_PASSWORD=strong_random_password
```

**Supported Databases**:
- MySQL 5.7+ (recommended)
- MariaDB 10.3+
- PostgreSQL 12+ (with minor modifications)

### Cache Configuration

```env
# Cache Driver Options:
# - file: Filesystem cache (default, no setup required)
# - redis: Redis cache (recommended for production)
# - database: Database cache
# - memcached: Memcached cache
CACHE_DRIVER=file

# Cache prefix (useful for shared cache servers)
CACHE_PREFIX=autoclean_cache
```

**Redis Configuration** (if using Redis):
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

### Queue Configuration

```env
# Queue Driver Options:
# - sync: Synchronous (no queue, immediate execution)
# - database: Database queue (recommended for most cases)
# - redis: Redis queue (faster, requires Redis)
# - sqs: Amazon SQS
QUEUE_CONNECTION=database

# Queue prefix
QUEUE_PREFIX=autoclean_
```

### Session Configuration

```env
# Session Driver Options:
# - file: File-based sessions
# - cookie: Cookie-based sessions
# - database: Database sessions (recommended for production)
# - redis: Redis sessions
SESSION_DRIVER=file
SESSION_LIFETIME=120            # Minutes
```

### Mail Configuration

```env
# Mail Driver Options:
# - smtp: SMTP server
# - sendmail: Sendmail
# - mailgun: Mailgun API
# - ses: Amazon SES
# - log: Write to log file (for testing)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@autoclean.local"
MAIL_FROM_NAME="${APP_NAME}"
```

**Production SMTP Example** (Gmail):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="AutoClean"
```

### Logging Configuration

```env
# Log Channel Options:
# - stack: Multiple channels
# - single: Single log file
# - daily: Daily rotating logs
# - slack: Slack notifications
# - syslog: System log
LOG_CHANNEL=stack
LOG_LEVEL=debug                 # debug, info, notice, warning, error, critical, alert, emergency

# Deprecations logging
LOG_DEPRECATIONS_CHANNEL=null
```

## Application Settings

### Task Configuration

Create `config/tasks.php` for task-specific settings:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Task Generation Settings
    |--------------------------------------------------------------------------
    */
    'generation' => [
        // Days to look ahead when generating tasks
        'lookahead_days' => 30,

        // Maximum tasks to generate per schedule
        'max_tasks_per_schedule' => 365,

        // Generate tasks in batches
        'batch_size' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Task Rollover Settings
    |--------------------------------------------------------------------------
    */
    'rollover' => [
        // Enable automatic rollover of overdue tasks
        'enabled' => env('TASK_ROLLOVER_ENABLED', true),

        // Days before considering a task overdue
        'overdue_threshold_days' => 1,

        // Maximum times a task can be rolled over
        'max_rollover_count' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Task Completion Settings
    |--------------------------------------------------------------------------
    */
    'completion' => [
        // Require clock-in before task completion
        'require_clock_in' => true,

        // Allow completing future tasks
        'allow_future_completion' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Recurrence Settings
    |--------------------------------------------------------------------------
    */
    'recurrence' => [
        // Maximum occurrences to calculate
        'max_occurrences' => 1000,

        // Supported frequencies
        'frequencies' => ['daily', 'weekly', 'monthly', 'yearly'],
    ],
];
```

### Livewire Configuration

Livewire configuration is in `config/livewire.php`:

```php
return [
    // Class namespace for Livewire components
    'class_namespace' => 'App\\Livewire',

    // View path for Livewire components
    'view_path' => resource_path('views/livewire'),

    // Layout view for Livewire components
    'layout' => 'components.layouts.app',

    // Enable lazy loading
    'lazy_placeholder' => null,

    // Temporary file uploads
    'temporary_file_upload' => [
        'disk' => 'local',
        'rules' => null,
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'preview_mimes' => ['png', 'gif', 'bmp', 'svg', 'wav', 'mp4', 'mov', 'avi', 'wmv', 'mp3', 'm4a', 'jpg', 'jpeg', 'mpga', 'webp', 'wma'],
        'max_upload_time' => 5,
    ],
];
```

### PDF Configuration

For time report PDF generation (`config/dompdf.php`):

```php
return [
    'show_warnings' => false,
    'public_path' => null,
    'convert_entities' => true,
    'options' => [
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),
        'allowed_protocols' => [
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],
        'log_output_file' => null,
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'serif',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => true,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],
];
```

## System Settings

AutoClean includes a database-driven settings system accessed via the admin interface or programmatically.

### Available Settings

Settings are stored in the `settings` table and can be accessed using the `settings()` helper function.

#### Admin Clock-In Requirement

```php
// Check if admins must clock in
$requireClockIn = settings('admin_requires_clock_in', false);

// Set via admin interface: Settings → General → Admin Clock-In Required
```

#### Task Rollover

```php
// Check if task rollover is enabled
$rolloverEnabled = settings('TASK_ROLLOVER_ENABLED', true);

// Set via admin interface: Settings → Tasks → Enable Overdue Task Rollover
```

### Using Settings in Code

```php
use function App\Helpers\settings;

// Get a setting with default value
$value = settings('setting_key', 'default_value');

// In Livewire components
public function mount()
{
    $this->requireClockIn = settings('admin_requires_clock_in', false);
}

// In models or services
if (settings('TASK_ROLLOVER_ENABLED', true)) {
    // Perform rollover logic
}
```

### Settings Service

For more advanced settings management:

```php
use App\Services\SettingsService;

class YourController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

    public function index()
    {
        $settings = $this->settingsService->all();
        $specificSetting = $this->settingsService->get('key', 'default');
    }
}
```

## Database Configuration

### Connection Pooling

For high-traffic production environments, configure connection pooling in `config/database.php`:

```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::ATTR_PERSISTENT => true,  // Connection pooling
    ]) : [],
],
```

### Query Logging

Enable query logging in development:

```env
# .env
DB_LOG_QUERIES=true
```

```php
// config/database.php
'connections' => [
    'mysql' => [
        // ... other config
        'dump' => [
            'log_queries' => env('DB_LOG_QUERIES', false),
        ],
    ],
],
```

## Security Configuration

### Trusted Proxies

If behind a load balancer or reverse proxy, configure trusted proxies in `app/Http/Middleware/TrustProxies.php`:

```php
protected $proxies = '*'; // Trust all proxies (use specific IPs in production)

protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
```

### CORS Configuration

For API endpoints, configure CORS in `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'livewire/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('APP_URL')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Content Security Policy

Add to `.env` for production:

```env
# Security Headers
SECURE_HEADERS_ENABLED=true
CSP_ENABLED=true
```

## Performance Optimization

### Production Optimization Commands

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache icons (if using Blade Icons)
php artisan icons:cache
```

### OPcache Configuration

Add to `php.ini` for production:

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0  # Set to 0 in production
opcache.revalidate_freq=0
opcache.save_comments=1
```

### Redis Configuration for Sessions and Cache

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis  # or predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Use different databases for different purposes
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3
```

## Scheduled Tasks

AutoClean includes scheduled tasks that must run via cron.

### Cron Setup

Add to crontab (`crontab -e`):

```bash
* * * * * cd /path-to-autoclean && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Commands

The schedule is defined in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generate upcoming scheduled tasks (daily at midnight)
    $schedule->command('tasks:generate')->daily();

    // Rollover overdue tasks (daily at 1 AM)
    $schedule->command('tasks:rollover-overdue')->dailyAt('01:00');

    // Clean up old time logs (weekly)
    $schedule->command('logs:cleanup')->weekly();
}
```

### View Scheduled Tasks

```bash
php artisan schedule:list
```

## Environment-Specific Configuration

### Development (.env.local)

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_DATABASE=autoclean_dev

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
MAIL_MAILER=log
```

### Staging (.env.staging)

```env
APP_ENV=staging
APP_DEBUG=true
LOG_LEVEL=info

CACHE_DRIVER=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=redis
MAIL_MAILER=smtp
```

### Production (.env.production)

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
MAIL_MAILER=smtp

# Enable OPcache
OPCACHE_ENABLE=true
```

## Configuration Files Reference

| File | Purpose |
|------|---------|
| `config/app.php` | Core application settings |
| `config/database.php` | Database connections |
| `config/cache.php` | Cache drivers and stores |
| `config/queue.php` | Queue connections |
| `config/mail.php` | Mail configuration |
| `config/livewire.php` | Livewire settings |
| `config/tasks.php` | Task-specific settings (custom) |
| `config/dompdf.php` | PDF generation settings |
| `config/log-viewer.php` | Log viewer configuration |

## Troubleshooting Configuration

### Clear All Caches

```bash
php artisan optimize:clear
```

This clears:
- Application cache
- Route cache
- Config cache
- View cache
- Compiled classes

### Verify Configuration

```bash
# Show application information
php artisan about

# Check specific configuration
php artisan config:show database
php artisan config:show cache
php artisan config:show queue
```

### Test Database Connection

```bash
php artisan db:show
php artisan db:table users
```

### Test Mail Configuration

```bash
php artisan tinker
```

```php
Mail::raw('Test email', function ($message) {
    $message->to('test@example.com')->subject('Test');
});
```

## Next Steps

- Review [Installation Guide](installation.md) for setup
- Check [Development Guide](development.md) for development settings
- See [Deployment Guide](deployment.md) for production optimization

---

**Navigation**: [← Installation](installation.md) | [Back to Documentation](README.md) | [Architecture →](architecture.md)
