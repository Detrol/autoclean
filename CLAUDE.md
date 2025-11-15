# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
Laravel 12 application with Livewire 3, using the official Laravel Livewire Starter Kit. Full-stack PHP application with Tailwind CSS v4, Flux UI components, and Vite build system.

## Essential Commands

### Development
```bash
# Start full development stack (recommended)
composer dev

# Individual services
php artisan serve        # Laravel dev server
npm run dev             # Vite with hot reload
php artisan queue:listen # Queue worker
php artisan pail        # Log viewer
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test
# or
./vendor/bin/pest

# Run specific test suites
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit

# Run specific test file
./vendor/bin/pest tests/Feature/Auth/LoginTest.php
```

### Code Quality
```bash
# Fix PHP code style
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

### Database
```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Reset and seed database
php artisan db:seed             # Run seeders only
```

### Building
```bash
# Production build
npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Architecture

### Livewire Components
All Livewire components follow this structure:
- **PHP Class**: `app/Livewire/{Feature}/{ComponentName}.php`
- **Blade View**: `resources/views/livewire/{feature}/{component-name}.blade.php`
- Components use Volt for single-file components in `resources/views/livewire/`

Key component patterns:
- Use typed properties for data binding
- Implement validation rules via `#[Validate]` attributes or `rules()` method
- Mount method for initialization
- Computed properties for derived state

### Authentication Flow
Complete authentication system with:
- Login/Register/Logout in `app/Livewire/Auth/`
- Email verification support
- Password reset functionality
- Two-factor authentication ready

### Frontend Assets
- **CSS**: Tailwind CSS v4 configured in `resources/css/app.css`
- **Flux UI**: Component library imported, use `<flux:button>`, `<flux:input>`, etc.
- **JavaScript**: Minimal JS in `resources/js/app.js`, primarily for Livewire
- **Build**: Vite handles all asset compilation with hot reloading

### Database Conventions
- Migrations use anonymous classes
- Factories defined for all models in `database/factories/`
- MySQL database in development and production
- Soft deletes implemented where appropriate

### Testing Strategy
- **Feature Tests**: User flows and integration tests in `tests/Feature/`
- **Unit Tests**: Model and service tests in `tests/Unit/`
- Tests use RefreshDatabase trait
- Factory-based test data generation
- Livewire testing helpers for component assertions

## Key File Locations
- **Routes**: `routes/web.php` for web routes
- **Config**: `config/` directory, especially `app.php`, `database.php`, `livewire.php`
- **Environment**: `.env` for local configuration (never commit)
- **Models**: `app/Models/` following Eloquent conventions
- **Middleware**: `app/Http/Middleware/` for request filtering

## Business Domain
This is a task management application with cleaning/maintenance scheduling features:
- **Core Models**: Task, TaskSchedule, TaskTemplate, TimeLog, Station, InventoryItem
- **Key Service**: `RecurrenceCalculator` handles complex recurring task logic
- **Features**: Task scheduling with recurrence patterns, inventory tracking, time logging, station management

## Development Environment
- **Local Server**: Uses `composer dev` which starts all services (including php artisan serve)
- **Database**: MySQL configured in `.env` (changed from SQLite)

## Development Workflow
1. Create Livewire components: `php artisan make:livewire ComponentName`
2. Run tests after changes: `./vendor/bin/pest`
3. Format code before commits: `./vendor/bin/pint`
4. Use `composer dev` for full-stack development
5. Database changes require migrations: `php artisan make:migration`