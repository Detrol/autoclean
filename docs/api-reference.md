# API Reference

Technical reference for AutoClean's Livewire components, models, services, and helpers.

## Table of Contents
- [Livewire Components](#livewire-components)
- [Eloquent Models](#eloquent-models)
- [Services](#services)
- [Helper Functions](#helper-functions)
- [Events](#events)

## Livewire Components

### Admin Components

#### `App\Livewire\Admin\Stations\Index`

**Purpose**: Display list of all stations

**Public Properties**:
```php
public Collection $stations
```

**Public Methods**:
```php
public function delete(Station $station): void
// Soft deletes a station
// Redirects to index after deletion
```

**View**: `resources/views/livewire/admin/stations/index.blade.php`

**Route**: `/admin/stations`

---

#### `App\Livewire\Admin\Stations\Create`

**Purpose**: Create new station

**Public Properties**:
```php
#[Validate('required|string|max:255')]
public string $name = '';

#[Validate('nullable|string')]
public string $description = '';

public array $selectedUsers = [];
```

**Public Methods**:
```php
public function save(): void
// Validates input
// Creates station
// Syncs users
// Redirects to station index
```

**Usage Example**:
```php
// In Blade view
<input wire:model="name" />
<button wire:click="save">Create Station</button>
```

---

#### `App\Livewire\Admin\Stations\Edit`

**Purpose**: Edit existing station

**Public Properties**:
```php
public Station $station;

#[Validate('required|string|max:255')]
public string $name;

#[Validate('nullable|string')]
public string $description;

public array $selectedUsers = [];
```

**Public Methods**:
```php
public function mount(Station $station): void
// Initialize component with station data

public function save(): void
// Updates station
// Syncs users
// Redirects to index
```

---

#### `App\Livewire\Admin\Tasks\Index`

**Purpose**: List all tasks with filtering

**Public Properties**:
```php
public ?int $stationFilter = null;
public ?string $statusFilter = null;  // 'completed', 'pending', 'overdue'
public ?string $dateFilter = null;
```

**Computed Properties**:
```php
public function tasks(): Collection
// Returns filtered task collection
// Eager loads station and schedule relationships
```

**Public Methods**:
```php
public function delete(Task $task): void
// Deletes task with confirmation

public function toggleComplete(Task $task): void
// Toggles task completion status
```

---

#### `App\Livewire\Admin\Tasks\Create`

**Purpose**: Create new task or task schedule

**Public Properties**:
```php
#[Validate('required|string|max:255')]
public string $name = '';

#[Validate('nullable|string')]
public string $description = '';

#[Validate('required|exists:stations,id')]
public int $station_id;

public string $type = 'one-time';  // 'one-time' or 'recurring'

#[Validate('required_if:type,one-time|date')]
public ?string $due_date = null;

// Recurring fields
public ?string $frequency = null;
public array $days_of_week = [];
public ?int $day_of_month = null;
public ?string $week_type = null;
```

**Public Methods**:
```php
public function save(): void
// Creates one-time task or task schedule based on type
// Validates recurrence patterns
// Redirects to task index
```

---

#### `App\Livewire\Admin\Users\Index`

**Purpose**: Manage users

**Public Properties**:
```php
public Collection $users;
public string $roleFilter = 'all';
```

**Public Methods**:
```php
public function delete(User $user): void
// Deletes user with confirmation

public function toggleRole(User $user): void
// Toggles between admin and employee
```

---

#### `App\Livewire\Admin\Inventory\Index`

**Purpose**: Manage inventory items

**Public Properties**:
```php
public Collection $inventoryItems;
```

**Public Methods**:
```php
public function delete(InventoryItem $item): void
// Deletes inventory item

public function showTransactions(InventoryItem $item): void
// Shows transaction history modal
```

---

#### `App\Livewire\Admin\Settings\Index`

**Purpose**: System settings management

**Public Properties**:
```php
public array $settings = [];
```

**Public Methods**:
```php
public function mount(): void
// Loads all settings from database

public function save(): void
// Saves all settings
// Clears settings cache
// Shows success notification
```

**Settings Array Structure**:
```php
[
    'admin_requires_clock_in' => true,
    'TASK_ROLLOVER_ENABLED' => true,
    'app_name' => 'AutoClean',
    // ... more settings
]
```

---

### Employee Components

#### `App\Livewire\Employee\Dashboard`

**Purpose**: Employee main dashboard

**Computed Properties**:
```php
public function todayTasks(): Collection
// Tasks due today at user's assigned stations

public function overdueTasks(): Collection
// Past-due incomplete tasks

public function currentTimeLog(): ?TimeLog
// Active time log (clocked in but not out)
```

**Public Methods**:
```php
public function completeTask(Task $task): void
// Marks task as completed
// Records completion timestamp and user

public function addAdditionalTask(string $name, string $description): void
// Logs additional unscheduled task
```

---

#### `App\Livewire\Employee\TimeLog\ClockIn`

**Purpose**: Clock in functionality

**Public Properties**:
```php
#[Validate('required|exists:stations,id')]
public ?int $station_id = null;

public Collection $availableStations;
```

**Public Methods**:
```php
public function clockIn(): void
// Validates user not already clocked in
// Creates new TimeLog with clock_in timestamp
// Refreshes parent component
// Shows success notification
```

**Validation Rules**:
- User must not have active time log
- Station must be assigned to user
- Cannot clock in to multiple stations

---

#### `App\Livewire\Employee\TimeLog\ClockOut`

**Purpose**: Clock out functionality

**Public Properties**:
```php
public ?TimeLog $activeTimeLog = null;
public string $notes = '';
```

**Public Methods**:
```php
public function clockOut(): void
// Updates TimeLog with clock_out timestamp
// Calculates duration_minutes
// Saves notes if provided
// Refreshes parent component
```

---

#### `App\Livewire\Employee\TimeReport\Index`

**Purpose**: View and export time reports

**Public Properties**:
```php
public string $startDate;
public string $endDate;
public Collection $timeLogs;
```

**Computed Properties**:
```php
public function totalHours(): float
// Sum of all time log durations in hours

public function totalShifts(): int
// Count of time logs

public function averageShiftDuration(): float
// Average hours per shift
```

**Public Methods**:
```php
public function generateReport(): void
// Loads time logs for date range
// Calculates statistics

public function exportPdf(): Response
// Generates PDF report using DomPDF
// Returns PDF download response
```

---

### Auth Components

#### `App\Livewire\Auth\Login`

**Purpose**: User login

**Public Properties**:
```php
#[Validate('required|email')]
public string $email = '';

#[Validate('required')]
public string $password = '';

public bool $remember = false;
```

**Public Methods**:
```php
public function login(): void
// Attempts authentication
// Redirects to dashboard on success
// Shows error on failure
```

---

## Eloquent Models

### User Model

**File**: `app/Models/User.php`

**Fillable Attributes**:
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
];
```

**Relationships**:
```php
public function stations(): BelongsToMany
// Many-to-many with Station via station_user pivot

public function timeLogs(): HasMany
// One-to-many with TimeLog

public function completedTasks(): HasMany
// One-to-many with Task (via completed_by foreign key)

public function employeeInvitations(): HasMany
// One-to-many with EmployeeInvitation (via invited_by)
```

**Scopes**:
```php
public function scopeAdmins($query)
// Filters users with role = 'admin'

public function scopeEmployees($query)
// Filters users with role = 'employee'

public function scopeAtStation($query, Station $station)
// Filters users assigned to specific station
```

**Accessors**:
```php
public function getIsAdminAttribute(): bool
// Returns true if user is admin

public function getIsEmployeeAttribute(): bool
// Returns true if user is employee
```

**Methods**:
```php
public function hasActiveTimeLog(): bool
// Checks if user has active (not clocked out) time log

public function activeTimeLog(): ?TimeLog
// Returns active time log or null

public function isAssignedTo(Station $station): bool
// Checks if user is assigned to station
```

---

### Station Model

**File**: `app/Models/Station.php`

**Fillable Attributes**:
```php
protected $fillable = [
    'name',
    'description',
    'active',
];
```

**Casts**:
```php
protected $casts = [
    'active' => 'boolean',
];
```

**Relationships**:
```php
public function users(): BelongsToMany
// Many-to-many with User

public function tasks(): HasMany
// One-to-many with Task

public function timeLogs(): HasMany
// One-to-many with TimeLog

public function inventoryItems(): BelongsToMany
// Many-to-many with InventoryItem via station_inventory

public function taskSchedules(): HasMany
// One-to-many with TaskSchedule
```

**Scopes**:
```php
public function scopeActive($query)
// Filters active stations

public function scopeWithTaskCounts($query)
// Eager loads task counts

public function scopeWithUserCount($query)
// Eager loads user count
```

**Methods**:
```php
public function getTasksForDate(Carbon $date): Collection
// Returns tasks for specific date

public function getPendingTasksCount(): int
// Count of incomplete tasks

public function getCompletedTasksCount(): int
// Count of completed tasks
```

---

### Task Model

**File**: `app/Models/Task.php`

**Fillable Attributes**:
```php
protected $fillable = [
    'name',
    'description',
    'station_id',
    'task_schedule_id',
    'due_date',
    'completed',
    'completed_at',
    'completed_by',
    'is_additional',
];
```

**Casts**:
```php
protected $casts = [
    'due_date' => 'date',
    'completed' => 'boolean',
    'completed_at' => 'datetime',
    'is_additional' => 'boolean',
];
```

**Relationships**:
```php
public function station(): BelongsTo
// Belongs to Station

public function taskSchedule(): BelongsTo
// Belongs to TaskSchedule (nullable)

public function completedBy(): BelongsTo
// Belongs to User (via completed_by foreign key)

public function completedAdditionalTasks(): HasMany
// One-to-many with CompletedAdditionalTask
```

**Scopes**:
```php
public function scopeCompleted($query)
// Filters completed tasks

public function scopePending($query)
// Filters incomplete tasks

public function scopeOverdue($query)
// Filters tasks past due date and not completed

public function scopeDueToday($query)
// Filters tasks due today

public function scopeForStation($query, Station $station)
// Filters tasks for specific station

public function scopeDueBetween($query, Carbon $start, Carbon $end)
// Filters tasks due between dates
```

**Methods**:
```php
public function markAsCompleted(User $user): void
// Marks task complete with user and timestamp

public function isOverdue(): bool
// Checks if task is past due and not completed

public function daysUntilDue(): int
// Days until due date (negative if overdue)
```

---

### TaskSchedule Model

**File**: `app/Models/TaskSchedule.php`

**Fillable Attributes**:
```php
protected $fillable = [
    'name',
    'description',
    'station_id',
    'frequency',
    'interval',
    'start_date',
    'end_date',
    'days_of_week',
    'day_of_month',
    'month_of_year',
    'week_type',
    'active',
    'last_generated',
];
```

**Casts**:
```php
protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'last_generated' => 'date',
    'days_of_week' => 'array',
    'active' => 'boolean',
];
```

**Relationships**:
```php
public function station(): BelongsTo
// Belongs to Station

public function tasks(): HasMany
// One-to-many with Task
```

**Scopes**:
```php
public function scopeActive($query)
// Filters active schedules

public function scopeFrequency($query, string $frequency)
// Filters by frequency type

public function scopeNeedsGeneration($query)
// Filters schedules that need task generation
```

**Methods**:
```php
public function generateTasks(int $days = 30): int
// Generates tasks for next N days
// Returns count of tasks created

public function calculateNextOccurrence(?Carbon $after = null): ?Carbon
// Calculates next occurrence date
// Uses RecurrenceCalculator service
```

---

### TimeLog Model

**File**: `app/Models/TimeLog.php`

**Fillable Attributes**:
```php
protected $fillable = [
    'user_id',
    'station_id',
    'clock_in',
    'clock_out',
    'duration_minutes',
    'notes',
];
```

**Casts**:
```php
protected $casts = [
    'clock_in' => 'datetime',
    'clock_out' => 'datetime',
];
```

**Relationships**:
```php
public function user(): BelongsTo
// Belongs to User

public function station(): BelongsTo
// Belongs to Station
```

**Scopes**:
```php
public function scopeActive($query)
// Filters time logs not clocked out

public function scopeBetweenDates($query, Carbon $start, Carbon $end)
// Filters time logs within date range

public function scopeForUser($query, User $user)
// Filters time logs for specific user
```

**Accessors**:
```php
public function getDurationHoursAttribute(): float
// Returns duration in hours (duration_minutes / 60)

public function getIsActiveAttribute(): bool
// Returns true if clock_out is null
```

**Methods**:
```php
public function clockOut(?string $notes = null): void
// Sets clock_out timestamp
// Calculates duration
// Saves notes

public function calculateDuration(): void
// Calculates and saves duration_minutes
```

---

### InventoryItem Model

**File**: `app/Models/InventoryItem.php`

**Fillable Attributes**:
```php
protected $fillable = [
    'name',
    'description',
    'sku',
    'unit',
    'min_quantity',
];
```

**Relationships**:
```php
public function stations(): BelongsToMany
// Many-to-many with Station via station_inventory
// Includes 'quantity' in pivot

public function transactions(): HasMany
// One-to-many with InventoryTransaction
```

**Methods**:
```php
public function getQuantityAtStation(Station $station): int
// Returns current quantity at station

public function isLowAtStation(Station $station): bool
// Checks if quantity < min_quantity at station

public function addStock(Station $station, int $quantity, ?string $notes = null): void
// Adds stock with transaction record

public function removeStock(Station $station, int $quantity, ?string $notes = null): void
// Removes stock with transaction record
```

---

## Services

### RecurrenceCalculator Service

**File**: `app/Services/RecurrenceCalculator.php`

**Purpose**: Calculate next occurrence dates for recurring tasks

**Public Methods**:

```php
public function calculateNextOccurrence(
    TaskSchedule $schedule,
    ?Carbon $after = null
): ?Carbon
// Calculates next occurrence after given date
// Returns null if schedule has ended
// Parameters:
//   $schedule: TaskSchedule model
//   $after: Date to calculate from (default: today)

public function generateOccurrences(
    TaskSchedule $schedule,
    int $count = 10
): array
// Generates array of next N occurrence dates
// Returns array of Carbon instances

public function calculateDaily(TaskSchedule $schedule, Carbon $after): Carbon
// Calculates next daily occurrence

public function calculateWeekly(TaskSchedule $schedule, Carbon $after): Carbon
// Calculates next weekly occurrence
// Handles even/odd weeks
// Handles specific days of week

public function calculateMonthly(TaskSchedule $schedule, Carbon $after): Carbon
// Calculates next monthly occurrence
// Handles specific day of month
// Handles nth weekday (e.g., 2nd Tuesday)

public function calculateYearly(TaskSchedule $schedule, Carbon $after): Carbon
// Calculates next yearly occurrence

public function isValidOccurrence(TaskSchedule $schedule, Carbon $date): bool
// Validates if date matches schedule pattern
```

**Usage Example**:
```php
use App\Services\RecurrenceCalculator;

$calculator = app(RecurrenceCalculator::class);
$nextDate = $calculator->calculateNextOccurrence($schedule);

// Generate next 5 occurrences
$dates = $calculator->generateOccurrences($schedule, 5);
```

---

### SettingsService

**File**: `app/Services/SettingsService.php`

**Purpose**: Manage application settings with caching

**Public Methods**:

```php
public function get(string $key, mixed $default = null): mixed
// Retrieves setting value
// Caches for 1 hour
// Returns default if not found

public function set(string $key, mixed $value): void
// Sets setting value
// Clears cache
// Creates or updates setting record

public function all(): Collection
// Returns all settings as collection

public function forget(string $key): void
// Removes setting and clears cache

public function refresh(): void
// Clears entire settings cache
```

**Usage Example**:
```php
use App\Services\SettingsService;

$settings = app(SettingsService::class);

// Get setting
$requireClockIn = $settings->get('admin_requires_clock_in', false);

// Set setting
$settings->set('admin_requires_clock_in', true);

// Get all settings
$allSettings = $settings->all();
```

---

## Helper Functions

### settings()

**File**: `app/Helpers.php`

**Signature**:
```php
function settings(string $key, mixed $default = null): mixed
```

**Purpose**: Quick access to settings

**Usage**:
```php
// Check if admin requires clock in
if (settings('admin_requires_clock_in')) {
    // Admin must clock in
}

// Get task rollover setting
$rolloverEnabled = settings('TASK_ROLLOVER_ENABLED', true);
```

---

## Events

### Model Events

#### Task Events

```php
// Task::creating
// Fired before task is created
static::creating(function (Task $task) {
    // Set default values
});

// Task::updated
// Fired after task is updated
static::updated(function (Task $task) {
    if ($task->wasChanged('completed') && $task->completed) {
        // Task was just marked complete
        Log::info("Task {$task->id} completed");
    }
});
```

#### TimeLog Events

```php
// TimeLog::updating
// Fired before time log is updated
static::updating(function (TimeLog $timeLog) {
    if ($timeLog->isDirty('clock_out') && $timeLog->clock_out) {
        // Calculate duration
        $timeLog->calculateDuration();
    }
});
```

---

## Next Steps

- Review [Features Guide](features.md) for feature details
- Check [Development Guide](development.md) for coding standards
- See [Testing](testing.md) for testing these components

---

**Navigation**: [← Features](features.md) | [Back to Documentation](README.md) | [Development →](development.md)
