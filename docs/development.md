# Development Guide

Comprehensive guide for developers working on AutoClean.

## Table of Contents
- [Development Environment Setup](#development-environment-setup)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Creating Features](#creating-features)
- [Database Migrations](#database-migrations)
- [Testing](#testing)
- [Debugging](#debugging)
- [Git Workflow](#git-workflow)

## Development Environment Setup

### Prerequisites

Install the following:
- PHP 8.2+ with required extensions
- Composer 2.x
- MySQL/MariaDB
- Node.js 18+ and npm
- Git

### Initial Setup

```bash
# Clone repository
git clone https://github.com/yourusername/autoclean.git
cd autoclean

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=mysql
# DB_DATABASE=autoclean_dev

# Run migrations with seeders
php artisan migrate:fresh --seed

# Start development server
composer dev
```

The `composer dev` command starts:
- Laravel development server (http://localhost:8000)
- Vite dev server with hot reload
- Queue worker
- Laravel Pail (log viewer)

### IDE Setup

#### VSCode (Recommended)

**Extensions**:
- PHP Intelephense
- Laravel Extension Pack
- Tailwind CSS IntelliSense
- Livewire Language Support
- EditorConfig

**Settings** (`.vscode/settings.json`):
```json
{
  "php.suggest.basic": false,
  "intelephense.files.maxSize": 5000000,
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "bmewburn.vscode-intelephense-client",
  "tailwindCSS.includeLanguages": {
    "blade": "html"
  }
}
```

#### PhpStorm

- Enable Laravel plugin
- Enable Livewire plugin
- Configure PHP interpreter
- Setup database connection
- Enable Tailwind CSS support

---

## Development Workflow

### Starting Development

```bash
# Full stack (recommended)
composer dev

# Or individual services
php artisan serve        # Terminal 1
npm run dev             # Terminal 2
php artisan queue:listen # Terminal 3
php artisan pail        # Terminal 4
```

### Making Changes

1. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make Changes**
   - Write code following [Coding Standards](#coding-standards)
   - Write tests for new features
   - Update documentation

3. **Run Tests**
   ```bash
   composer test
   ```

4. **Format Code**
   ```bash
   ./vendor/bin/pint
   ```

5. **Commit Changes**
   ```bash
   git add .
   git commit -m "Add: your feature description"
   ```

6. **Push and Create PR**
   ```bash
   git push origin feature/your-feature-name
   ```

---

## Coding Standards

### PHP Code Style

AutoClean uses **Laravel Pint** for code formatting.

**Run Pint**:
```bash
# Fix all files
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test

# Fix specific file
./vendor/bin/pint app/Models/Task.php
```

**Configuration**: `pint.json`
```json
{
    "preset": "laravel"
}
```

### Laravel Conventions

#### Naming Conventions

**Classes**:
```php
// Models: Singular, PascalCase
class Task extends Model {}
class TaskSchedule extends Model {}

// Controllers: Singular, PascalCase + Controller
class TaskController extends Controller {}

// Livewire: Nested namespaces, PascalCase
class Admin\Tasks\Create extends Component {}

// Services: Descriptive name + Service
class RecurrenceCalculator {}
class SettingsService {}
```

**Database**:
```php
// Tables: Plural, snake_case
tasks, task_schedules, time_logs

// Columns: snake_case
due_date, completed_at, station_id

// Foreign keys: singular_table_id
station_id, user_id, task_schedule_id

// Pivot tables: alphabetically ordered, singular
station_user (not user_station)
```

**Routes**:
```php
// Resource routes: plural, kebab-case
Route::resource('task-schedules', TaskScheduleController::class);

// Livewire routes: descriptive, kebab-case
Route::get('/admin/tasks/create', Create::class);
```

---

### Livewire Best Practices

#### Component Structure

```php
namespace App\Livewire\Admin\Tasks;

use Livewire\Component;
use Livewire\Attributes\Validate;

class Create extends Component
{
    // Public properties (reactive)
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|exists:stations,id')]
    public int $station_id;

    // Computed properties (cached)
    public function stations()
    {
        return Station::active()->get();
    }

    // Lifecycle hooks
    public function mount(): void
    {
        $this->station_id = auth()->user()->stations->first()?->id;
    }

    // Public methods (callable from view)
    public function save(): void
    {
        $this->validate();

        Task::create([
            'name' => $this->name,
            'station_id' => $this->station_id,
        ]);

        $this->redirect(route('tasks.index'));
    }

    // Render method
    public function render()
    {
        return view('livewire.admin.tasks.create');
    }
}
```

#### Property Types

**Always use typed properties**:
```php
// Good
public string $name = '';
public int $quantity = 0;
public ?Carbon $dueDate = null;
public array $selectedItems = [];

// Bad
public $name;
public $quantity;
public $dueDate;
```

#### Validation

**Use #[Validate] attribute**:
```php
use Livewire\Attributes\Validate;

#[Validate('required|email')]
public string $email = '';

#[Validate('required|min:8')]
public string $password = '';
```

**Or rules() method for dynamic validation**:
```php
protected function rules(): array
{
    return [
        'email' => ['required', 'email', Rule::unique('users')->ignore($this->user)],
        'password' => 'required|min:8',
    ];
}
```

---

### Eloquent Best Practices

#### Model Structure

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    // 1. Properties
    protected $fillable = [
        'name',
        'description',
        'station_id',
        'due_date',
        'completed',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // 2. Relationships
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // 3. Scopes
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('completed', false);
    }

    // 4. Accessors
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date->isPast() && !$this->completed;
    }

    // 5. Methods
    public function markAsCompleted(User $user): void
    {
        $this->update([
            'completed' => true,
            'completed_at' => now(),
            'completed_by' => $user->id,
        ]);
    }

    // 6. Boot method
    protected static function booted(): void
    {
        static::created(function (Task $task) {
            // Log task creation
        });
    }
}
```

#### Query Optimization

**Eager Loading** (avoid N+1 queries):
```php
// Good
$tasks = Task::with(['station', 'completedBy'])->get();

// Bad
$tasks = Task::all();
foreach ($tasks as $task) {
    echo $task->station->name; // N+1 query
}
```

**Select Only Needed Columns**:
```php
// Good
$tasks = Task::select('id', 'name', 'due_date')->get();

// Avoid
$tasks = Task::all(); // Selects everything
```

**Use Chunking for Large Datasets**:
```php
Task::chunk(100, function ($tasks) {
    foreach ($tasks as $task) {
        // Process task
    }
});
```

---

## Creating Features

### Adding a New Livewire Component

**1. Create Component**:
```bash
php artisan make:livewire Admin/Tasks/Create
```

This creates:
- `app/Livewire/Admin/Tasks/Create.php`
- `resources/views/livewire/admin/tasks/create.blade.php`

**2. Define Route**:
```php
// routes/web.php
use App\Livewire\Admin\Tasks\Create;

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/tasks/create', Create::class)->name('tasks.create');
});
```

**3. Implement Component Logic**:
```php
// app/Livewire/Admin/Tasks/Create.php
class Create extends Component
{
    public string $name = '';
    public int $station_id;

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'station_id' => 'required|exists:stations,id',
        ]);

        Task::create([
            'name' => $this->name,
            'station_id' => $this->station_id,
        ]);

        $this->redirect(route('tasks.index'));
    }

    public function render()
    {
        return view('livewire.admin.tasks.create');
    }
}
```

**4. Create View**:
```blade
{{-- resources/views/livewire/admin/tasks/create.blade.php --}}
<div>
    <form wire:submit="save">
        <flux:input
            wire:model="name"
            label="Task Name"
            placeholder="Enter task name"
        />

        <flux:select wire:model="station_id" label="Station">
            @foreach(Station::all() as $station)
                <option value="{{ $station->id }}">{{ $station->name }}</option>
            @endforeach
        </flux:select>

        <flux:button type="submit">Create Task</flux:button>
    </form>
</div>
```

---

### Adding a New Model

**1. Create Model and Migration**:
```bash
php artisan make:model Category -m
```

**2. Define Migration**:
```php
// database/migrations/xxxx_create_categories_table.php
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
}
```

**3. Define Model**:
```php
// app/Models/Category.php
class Category extends Model
{
    protected $fillable = ['name', 'description', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
```

**4. Create Factory**:
```bash
php artisan make:factory CategoryFactory
```

```php
// database/factories/CategoryFactory.php
public function definition(): array
{
    return [
        'name' => $this->faker->words(3, true),
        'description' => $this->faker->sentence,
        'active' => true,
    ];
}
```

**5. Run Migration**:
```bash
php artisan migrate
```

---

### Adding a Service

**1. Create Service Class**:
```php
// app/Services/NotificationService.php
namespace App\Services;

class NotificationService
{
    public function sendTaskReminder(Task $task): void
    {
        // Send notification logic
    }

    public function sendLowStockAlert(Station $station, InventoryItem $item): void
    {
        // Send alert logic
    }
}
```

**2. Register Service (Optional)**:
```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->singleton(NotificationService::class);
}
```

**3. Use Service**:
```php
// In a Livewire component or controller
use App\Services\NotificationService;

class TaskController
{
    public function __construct(
        private NotificationService $notifications
    ) {}

    public function sendReminder(Task $task)
    {
        $this->notifications->sendTaskReminder($task);
    }
}
```

---

## Database Migrations

### Creating Migrations

```bash
# Create table migration
php artisan make:migration create_categories_table

# Modify table migration
php artisan make:migration add_priority_to_tasks_table

# Migration with model
php artisan make:model Category -m
```

### Migration Best Practices

**Use Descriptive Names**:
```php
// Good
xxxx_create_task_schedules_table.php
xxxx_add_completed_by_to_tasks_table.php

// Bad
xxxx_update_tasks.php
xxxx_new_fields.php
```

**Always Include Down Method**:
```php
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('categories');
}
```

**Use Foreign Keys**:
```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('station_id')->constrained()->cascadeOnDelete();
    $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

**Add Indexes**:
```php
$table->index('due_date');
$table->index(['station_id', 'completed']);
$table->unique('email');
```

### Running Migrations

```bash
# Run pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Fresh migration (drop all + migrate)
php artisan migrate:fresh

# Fresh with seeders
php artisan migrate:fresh --seed

# Migration status
php artisan migrate:status
```

---

## Debugging

### Laravel Pail (Log Viewer)

```bash
php artisan pail

# Filter by level
php artisan pail --level=error

# Filter by user
php artisan pail --user=1
```

### Debug Functions

```php
// Dump and die
dd($variable);

// Dump
dump($variable);

// Ray (if installed)
ray($variable);

// Log
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Task creation failed', ['error' => $e->getMessage()]);
```

### Livewire Debugging

```blade
{{-- In Blade view --}}
@dump($variable)

{{-- Component state --}}
<pre>{{ json_encode($this->all(), JSON_PRETTY_PRINT) }}</pre>
```

### Database Query Logging

```php
// Enable query log
\DB::enableQueryLog();

// Run queries
$tasks = Task::with('station')->get();

// Dump queries
dd(\DB::getQueryLog());
```

### Tinker (REPL)

```bash
php artisan tinker
```

```php
// Test queries
>>> Task::count()
=> 42

>>> User::first()
=> App\Models\User {#1234}

// Create records
>>> Task::factory()->create()

// Test services
>>> app(RecurrenceCalculator::class)->calculateNextOccurrence($schedule)
```

---

## Git Workflow

### Branch Naming

```
feature/task-templates
bugfix/clock-out-validation
hotfix/security-patch
refactor/recurrence-calculator
docs/api-reference
```

### Commit Messages

**Format**:
```
Type: Brief description (50 chars max)

Detailed explanation if needed (wrap at 72 chars)
```

**Types**:
- `Add`: New feature
- `Fix`: Bug fix
- `Update`: Modification to existing feature
- `Refactor`: Code restructuring
- `Docs`: Documentation changes
- `Test`: Test additions or changes
- `Style`: Code style changes (formatting)

**Examples**:
```
Add: Task template management
Fix: Clock out validation error
Update: Improve recurrence calculation performance
Refactor: Extract notification service
Docs: Add API reference for models
Test: Add task completion tests
```

---

## Code Review Checklist

Before submitting PR:

- [ ] All tests pass (`composer test`)
- [ ] Code formatted with Pint (`./vendor/bin/pint`)
- [ ] No debug code (dd(), dump(), console.log())
- [ ] Documentation updated
- [ ] Database migrations include down() method
- [ ] Foreign keys and indexes added
- [ ] Validation rules present
- [ ] Error handling implemented
- [ ] Security considerations addressed
- [ ] Responsive design (mobile-friendly)

---

## Useful Commands

```bash
# Development
composer dev                  # Start full dev stack
php artisan serve            # Laravel server
npm run dev                  # Vite dev server

# Database
php artisan migrate          # Run migrations
php artisan db:seed         # Run seeders
php artisan migrate:fresh --seed  # Reset & seed

# Testing
composer test                # Run tests
./vendor/bin/pest --coverage # With coverage

# Code Quality
./vendor/bin/pint           # Format code
./vendor/bin/pint --test    # Check formatting

# Cache
php artisan optimize:clear  # Clear all caches
php artisan config:cache    # Cache config
php artisan route:cache     # Cache routes
php artisan view:cache      # Cache views

# Livewire
php artisan livewire:make   # Create component
php artisan livewire:delete # Delete component
php artisan livewire:copy   # Copy component

# Queue
php artisan queue:work      # Process queue
php artisan queue:listen    # Process with reload
php artisan queue:restart   # Restart workers

# Logs
php artisan pail            # View logs
php artisan log:clear       # Clear logs
```

---

## Next Steps

- Review [Testing Guide](testing.md) for writing tests
- Check [Contributing Guide](contributing.md) for PR process
- See [API Reference](api-reference.md) for component details

---

**Navigation**: [← API Reference](api-reference.md) | [Back to Documentation](README.md) | [Testing →](testing.md)
