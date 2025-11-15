# Architecture Overview

Comprehensive guide to AutoClean's architecture, design patterns, and system structure.

## Table of Contents
- [System Overview](#system-overview)
- [Technology Stack](#technology-stack)
- [Directory Structure](#directory-structure)
- [Application Layers](#application-layers)
- [Design Patterns](#design-patterns)
- [Core Components](#core-components)
- [Data Flow](#data-flow)
- [Security Architecture](#security-architecture)

## System Overview

AutoClean is built on Laravel 12 with Livewire 3, following a modern full-stack PHP architecture. The application uses a component-based approach with Livewire for reactive UI updates without page reloads.

### Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                         Browser                              │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐            │
│  │  Livewire  │  │  Tailwind  │  │    Flux    │            │
│  │ Components │  │    CSS     │  │     UI     │            │
│  └────────────┘  └────────────┘  └────────────┘            │
└─────────────────────┬───────────────────────────────────────┘
                      │ HTTP/WebSocket
┌─────────────────────▼───────────────────────────────────────┐
│                    Laravel 12                                │
│  ┌───────────────────────────────────────────────────────┐  │
│  │              Livewire Components Layer                 │  │
│  │  ┌─────────┐ ┌──────────┐ ┌──────────┐ ┌─────────┐  │  │
│  │  │  Admin  │ │ Employee │ │   Auth   │ │ Settings│  │  │
│  │  └─────────┘ └──────────┘ └──────────┘ └─────────┘  │  │
│  └───────────────────┬───────────────────────────────────┘  │
│  ┌───────────────────▼───────────────────────────────────┐  │
│  │               Service Layer                            │  │
│  │  ┌──────────────────┐  ┌────────────────────┐        │  │
│  │  │ RecurrenceCalc   │  │  SettingsService   │        │  │
│  │  └──────────────────┘  └────────────────────┘        │  │
│  └───────────────────┬───────────────────────────────────┘  │
│  ┌───────────────────▼───────────────────────────────────┐  │
│  │                Model Layer (Eloquent)                  │  │
│  │  ┌──────┐ ┌────────┐ ┌──────┐ ┌─────────┐ ┌───────┐ │  │
│  │  │ User │ │Station │ │ Task │ │TimeLog  │ │  ...  │ │  │
│  │  └──────┘ └────────┘ └──────┘ └─────────┘ └───────┘ │  │
│  └───────────────────┬───────────────────────────────────┘  │
│  ┌───────────────────▼───────────────────────────────────┐  │
│  │             Console Commands / Jobs                    │  │
│  │  ┌──────────────────┐  ┌────────────────────┐        │  │
│  │  │ GenerateTasks    │  │  RolloverOverdue   │        │  │
│  │  └──────────────────┘  └────────────────────┘        │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                      MySQL Database                          │
│  ┌─────────┐ ┌──────────┐ ┌─────────┐ ┌──────────────┐    │
│  │  users  │ │ stations │ │  tasks  │ │   time_logs  │    │
│  └─────────┘ └──────────┘ └─────────┘ └──────────────┘    │
│  ┌─────────────────┐ ┌────────────┐ ┌─────────────────┐   │
│  │  task_schedules │ │ inventory  │ │  ... (12 tables)│   │
│  └─────────────────┘ └────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Frontend Framework**: Livewire 3 (TALL stack)
- **Database ORM**: Eloquent
- **Authentication**: Laravel Breeze with Livewire
- **Queue System**: Database driver (configurable to Redis)

### Frontend
- **UI Library**: Flux UI Components
- **CSS Framework**: Tailwind CSS v4
- **JavaScript**: Minimal vanilla JS via Livewire
- **Build Tool**: Vite 5

### Development Tools
- **Testing**: Pest PHP
- **Code Style**: Laravel Pint
- **Log Viewer**: Laravel Pail
- **PDF Generation**: DomPDF

## Directory Structure

```
autoclean/
├── app/
│   ├── Console/
│   │   ├── Commands/             # Artisan commands
│   │   │   ├── GenerateScheduledTasks.php
│   │   │   └── RolloverOverdueTasks.php
│   │   └── Kernel.php            # Command scheduling
│   ├── Http/
│   │   ├── Controllers/          # Traditional controllers (minimal use)
│   │   └── Middleware/           # Request middleware
│   ├── Livewire/                 # Livewire components (main logic)
│   │   ├── Admin/               # Admin-only components
│   │   │   ├── Stations/
│   │   │   ├── Tasks/
│   │   │   ├── Users/
│   │   │   └── Inventory/
│   │   ├── Employee/            # Employee components
│   │   │   ├── Dashboard.php
│   │   │   ├── TimeLog/
│   │   │   └── TimeReport/
│   │   ├── Auth/                # Authentication
│   │   └── Settings/            # System settings
│   ├── Models/                  # Eloquent models
│   │   ├── User.php
│   │   ├── Station.php
│   │   ├── Task.php
│   │   ├── TaskSchedule.php
│   │   └── ... (12+ models)
│   ├── Services/                # Business logic services
│   │   ├── RecurrenceCalculator.php
│   │   └── SettingsService.php
│   ├── View/
│   │   └── Components/          # Blade components
│   └── Helpers.php              # Global helper functions
├── bootstrap/
│   └── app.php                  # Application bootstrap
├── config/                      # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── livewire.php
│   └── tasks.php                # Custom task config
├── database/
│   ├── factories/               # Model factories for testing
│   ├── migrations/              # Database migrations
│   └── seeders/                 # Database seeders
├── public/                      # Public web root
│   ├── index.php               # Application entry point
│   └── build/                  # Compiled assets (Vite)
├── resources/
│   ├── css/
│   │   └── app.css             # Tailwind CSS
│   ├── js/
│   │   └── app.js              # JavaScript entry
│   └── views/
│       ├── components/         # Blade components
│       │   ├── layouts/        # Layout templates
│       │   └── flux/           # Flux UI overrides
│       └── livewire/           # Livewire views
│           ├── admin/
│           ├── employee/
│           └── auth/
├── routes/
│   ├── web.php                 # Web routes
│   └── console.php             # Console routes
├── storage/
│   ├── app/                    # Application storage
│   ├── framework/              # Framework files
│   └── logs/                   # Application logs
├── tests/
│   ├── Feature/                # Feature tests
│   └── Unit/                   # Unit tests
└── vendor/                     # Composer dependencies
```

## Application Layers

### 1. Presentation Layer (Livewire Components)

Livewire components handle both UI and user interactions, replacing traditional controllers.

**Structure**:
```
app/Livewire/
├── Admin/                      # Admin interface
│   ├── Stations/
│   │   ├── Index.php          # List stations
│   │   ├── Create.php         # Create station
│   │   └── Edit.php           # Edit station
│   ├── Tasks/
│   │   ├── Index.php
│   │   ├── Create.php
│   │   └── TaskForm.php       # Reusable form component
│   └── ...
└── Employee/                   # Employee interface
    ├── Dashboard.php
    └── TimeLog/
        ├── ClockIn.php
        └── ClockOut.php
```

**Example Component**:
```php
// app/Livewire/Admin/Tasks/Create.php
class Create extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|exists:stations,id')]
    public int $station_id;

    #[Validate('required|in:daily,weekly,monthly,yearly')]
    public string $frequency;

    public function save()
    {
        $this->validate();

        Task::create([
            'name' => $this->name,
            'station_id' => $this->station_id,
            'frequency' => $this->frequency,
        ]);

        $this->redirect(route('tasks.index'));
    }

    public function render()
    {
        return view('livewire.admin.tasks.create');
    }
}
```

### 2. Service Layer

Services encapsulate complex business logic that doesn't belong in models or components.

**RecurrenceCalculator Service**:
```php
// app/Services/RecurrenceCalculator.php
class RecurrenceCalculator
{
    public function calculateNextOccurrence(TaskSchedule $schedule): ?Carbon
    {
        return match ($schedule->frequency) {
            'daily' => $this->calculateDaily($schedule),
            'weekly' => $this->calculateWeekly($schedule),
            'monthly' => $this->calculateMonthly($schedule),
            'yearly' => $this->calculateYearly($schedule),
        };
    }

    private function calculateWeekly(TaskSchedule $schedule): Carbon
    {
        // Complex logic for weekly recurrence
        // Handles even/odd weeks, specific days
    }
}
```

**SettingsService**:
```php
// app/Services/SettingsService.php
class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("settings.{$key}", 3600, function () use ($key, $default) {
            return Settings::where('key', $key)->value('value') ?? $default;
        });
    }

    public function set(string $key, mixed $value): void
    {
        Settings::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("settings.{$key}");
    }
}
```

### 3. Model Layer (Eloquent ORM)

Models represent database tables and contain relationships, scopes, and business logic.

**Key Models**:
- `User` - Users with role-based access
- `Station` - Work locations
- `Task` - Individual task instances
- `TaskSchedule` - Recurring task definitions
- `TimeLog` - Time tracking records
- `InventoryItem` - Inventory items

**Example Model with Relationships**:
```php
// app/Models/Station.php
class Station extends Model
{
    protected $fillable = ['name', 'description'];

    // Relationships
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    // Scopes
    public function scopeWithTaskCounts($query)
    {
        return $query->withCount(['tasks', 'tasks as completed_tasks_count' => function ($q) {
            $q->where('completed', true);
        }]);
    }
}
```

### 4. Data Access Layer

Eloquent handles database interaction through:
- **Query Builder**: Fluent interface for queries
- **Relationships**: Eager loading to avoid N+1 queries
- **Scopes**: Reusable query logic
- **Accessors/Mutators**: Data transformation

## Design Patterns

### 1. Repository Pattern (Implicit via Eloquent)

Eloquent models act as repositories, abstracting database queries.

### 2. Service Pattern

Complex business logic is extracted into service classes.

### 3. Observer Pattern

Model events trigger actions:
```php
// app/Models/Task.php
protected static function booted()
{
    static::completed(function (Task $task) {
        // Log completion
        Log::info("Task {$task->id} completed");
    });
}
```

### 4. Factory Pattern

Used for test data generation:
```php
// database/factories/TaskFactory.php
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'station_id' => Station::factory(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
        ];
    }
}
```

### 5. Strategy Pattern

RecurrenceCalculator uses strategy pattern for different frequencies:
```php
return match ($frequency) {
    'daily' => $this->calculateDaily($schedule),
    'weekly' => $this->calculateWeekly($schedule),
    'monthly' => $this->calculateMonthly($schedule),
    'yearly' => $this->calculateYearly($schedule),
};
```

### 6. Command Pattern

Console commands encapsulate actions:
```php
// app/Console/Commands/GenerateScheduledTasks.php
class GenerateScheduledTasks extends Command
{
    protected $signature = 'tasks:generate {--days=30}';

    public function handle(RecurrenceCalculator $calculator)
    {
        // Generate tasks logic
    }
}
```

## Core Components

### 1. Authentication System

Uses Laravel Breeze with Livewire:
- Login/Logout
- Registration
- Password reset
- Email verification
- Two-factor authentication ready

**Middleware**:
- `auth`: Require authentication
- `role`: Role-based access control

### 2. Task Scheduling System

**Components**:
- `TaskSchedule`: Defines recurrence pattern
- `Task`: Individual task instance
- `RecurrenceCalculator`: Calculates next occurrences
- `GenerateScheduledTasks`: Console command to generate tasks

**Flow**:
1. Admin creates TaskSchedule with recurrence pattern
2. Cron runs `tasks:generate` daily
3. RecurrenceCalculator determines next occurrences
4. Tasks are created up to lookahead period (30 days)

### 3. Time Tracking System

**Components**:
- `TimeLog`: Tracks clock in/out
- `ClockIn` component: Start time tracking
- `ClockOut` component: End time tracking
- Time reports with PDF generation

**Flow**:
1. Employee clocks in at station
2. TimeLog record created with `clock_in` timestamp
3. Employee completes tasks
4. Employee clocks out
5. TimeLog updated with `clock_out` timestamp
6. Time report generated from TimeLog records

### 4. Inventory Management

**Components**:
- `InventoryItem`: Items available at stations
- `StationInventory`: Pivot table with quantities
- `InventoryTransaction`: Transaction history

## Data Flow

### Task Creation Flow

```
Admin creates TaskSchedule
         ↓
RecurrenceCalculator.calculateNextOccurrence()
         ↓
GenerateScheduledTasks command (cron)
         ↓
Multiple Task instances created
         ↓
Tasks appear on Employee Dashboard
         ↓
Employee completes Task
         ↓
Task marked as completed
```

### Time Tracking Flow

```
Employee clicks "Clock In"
         ↓
Livewire ClockIn component
         ↓
TimeLog::create(['clock_in' => now()])
         ↓
Employee works and completes tasks
         ↓
Employee clicks "Clock Out"
         ↓
Livewire ClockOut component
         ↓
TimeLog->update(['clock_out' => now()])
         ↓
Duration calculated and stored
```

## Security Architecture

### Authentication & Authorization

1. **Session-based Authentication**: Laravel's built-in session authentication
2. **Role-based Access Control**: `role` column on users table ('admin' or 'employee')
3. **Middleware Protection**: Routes protected by `auth` and custom `role` middleware

### Input Validation

1. **Livewire Validation**: `#[Validate]` attributes on component properties
2. **Form Requests**: Custom validation rules for complex forms
3. **Database Constraints**: Foreign keys, unique constraints

### CSRF Protection

- Automatic CSRF token validation on all POST/PUT/DELETE requests
- Livewire handles CSRF automatically

### SQL Injection Prevention

- Eloquent ORM parameterizes all queries
- Query builder uses parameter binding

### XSS Prevention

- Blade templating escapes output by default: `{{ $variable }}`
- Flux UI components sanitize inputs

## Performance Considerations

### Database Optimization

1. **Eager Loading**: Prevent N+1 queries
```php
Station::with(['users', 'tasks', 'inventoryItems'])->get();
```

2. **Indexes**: Applied to foreign keys and frequently queried columns

3. **Query Caching**: Settings cached for 1 hour

### Livewire Optimization

1. **Lazy Loading**: Load components on demand
2. **Polling**: Efficient real-time updates
3. **Deferred Loading**: Defer heavy queries

### Asset Optimization

1. **Vite**: Modern build tool with hot module replacement
2. **Tailwind CSS**: Purges unused CSS in production
3. **Asset Versioning**: Cache busting for updates

## Scalability

### Horizontal Scaling

- **Stateless Application**: Session stored in database/Redis
- **Load Balancing Ready**: No file-based sessions
- **Queue Workers**: Separate processes for background jobs

### Vertical Scaling

- **Database Optimization**: Indexes, query optimization
- **Cache Layer**: Redis for caching and sessions
- **OPcache**: PHP opcode caching

## Next Steps

- Review [Database Schema](database-schema.md) for data model details
- Check [API Reference](api-reference.md) for component documentation
- See [Development Guide](development.md) for coding standards

---

**Navigation**: [← Configuration](configuration.md) | [Back to Documentation](README.md) | [Database Schema →](database-schema.md)
