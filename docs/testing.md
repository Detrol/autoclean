# Testing Guide

Comprehensive testing documentation for AutoClean using Pest PHP.

## Table of Contents
- [Testing Overview](#testing-overview)
- [Running Tests](#running-tests)
- [Test Structure](#test-structure)
- [Writing Tests](#writing-tests)
- [Testing Livewire Components](#testing-livewire-components)
- [Testing Models](#testing-models)
- [Testing Services](#testing-services)
- [Test Coverage](#test-coverage)
- [CI/CD Integration](#cicd-integration)

## Testing Overview

AutoClean uses **Pest PHP**, a modern testing framework built on PHPUnit with an expressive syntax.

### Test Suite Structure

```
tests/
├── Feature/              # Integration tests
│   ├── Auth/            # Authentication tests
│   ├── Admin/           # Admin feature tests
│   ├── Employee/        # Employee feature tests
│   └── Api/             # API tests (if applicable)
├── Unit/                # Unit tests
│   ├── Models/          # Model tests
│   ├── Services/        # Service tests
│   └── Helpers/         # Helper function tests
├── Pest.php             # Pest configuration
└── TestCase.php         # Base test case
```

### Test Types

- **Feature Tests**: Test user-facing features and workflows
- **Unit Tests**: Test individual classes and methods in isolation
- **Integration Tests**: Test how components work together

---

## Running Tests

### Basic Commands

```bash
# Run all tests
composer test
# or
php artisan test
# or
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit

# Run specific test file
./vendor/bin/pest tests/Feature/Auth/LoginTest.php

# Run tests matching pattern
./vendor/bin/pest --filter=login
```

### Test Options

```bash
# With coverage
./vendor/bin/pest --coverage

# With minimum coverage requirement
./vendor/bin/pest --coverage --min=80

# Parallel execution (faster)
./vendor/bin/pest --parallel

# Stop on first failure
./vendor/bin/pest --stop-on-failure

# Verbose output
./vendor/bin/pest -v
```

### Watch Mode (Development)

```bash
# Re-run tests on file changes (requires pest-watch plugin)
./vendor/bin/pest --watch
```

---

## Test Structure

### Pest Configuration

**File**: `tests/Pest.php`

```php
<?php

uses(Tests\TestCase::class)->in('Feature', 'Unit');

// Global functions available in all tests
function actingAsAdmin()
{
    return test()->actingAs(
        User::factory()->create(['role' => 'admin'])
    );
}

function actingAsEmployee()
{
    return test()->actingAs(
        User::factory()->create(['role' => 'employee'])
    );
}
```

### Base Test Case

**File**: `tests/TestCase.php`

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup
    }
}
```

---

## Writing Tests

### Basic Test Structure

```php
<?php

use App\Models\Task;
use App\Models\Station;

test('can create a task', function () {
    $station = Station::factory()->create();

    $task = Task::create([
        'name' => 'Test Task',
        'station_id' => $station->id,
        'due_date' => now(),
    ]);

    expect($task)
        ->name->toBe('Test Task')
        ->station_id->toBe($station->id)
        ->completed->toBeFalse();
});

it('marks task as overdue when past due date', function () {
    $task = Task::factory()->create([
        'due_date' => now()->subDay(),
        'completed' => false,
    ]);

    expect($task->isOverdue())->toBeTrue();
});
```

### Using Datasets

```php
<?php

use App\Services\RecurrenceCalculator;

test('calculates correct next occurrence', function ($frequency, $expected) {
    $schedule = TaskSchedule::factory()->create([
        'frequency' => $frequency,
        'start_date' => now(),
    ]);

    $calculator = new RecurrenceCalculator();
    $next = $calculator->calculateNextOccurrence($schedule);

    expect($next->format('Y-m-d'))->toBe($expected);
})->with([
    ['daily', now()->addDay()->format('Y-m-d')],
    ['weekly', now()->addWeek()->format('Y-m-d')],
    ['monthly', now()->addMonth()->format('Y-m-d')],
]);
```

### Test Hooks

```php
<?php

beforeEach(function () {
    // Runs before each test in this file
    $this->station = Station::factory()->create();
});

afterEach(function () {
    // Runs after each test in this file
    // Cleanup code
});

beforeAll(function () {
    // Runs once before all tests in this file
});

afterAll(function () {
    // Runs once after all tests in this file
});
```

---

## Testing Livewire Components

### Basic Livewire Test

```php
<?php

use App\Livewire\Admin\Tasks\Create;
use App\Models\Station;
use Livewire\Livewire;

test('can render create task component', function () {
    actingAsAdmin();

    Livewire::test(Create::class)
        ->assertStatus(200);
});

test('can create task through component', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $station = Station::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Create::class)
        ->set('name', 'New Task')
        ->set('station_id', $station->id)
        ->set('due_date', now()->addDay()->format('Y-m-d'))
        ->call('save')
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'name' => 'New Task',
        'station_id' => $station->id,
    ]);
});
```

### Testing Validation

```php
<?php

test('validates required fields', function () {
    actingAsAdmin();

    Livewire::test(Create::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('validates station exists', function () {
    actingAsAdmin();

    Livewire::test(Create::class)
        ->set('name', 'Task')
        ->set('station_id', 999)
        ->call('save')
        ->assertHasErrors(['station_id' => 'exists']);
});
```

### Testing User Interaction

```php
<?php

use App\Livewire\Employee\TimeLog\ClockIn;

test('employee can clock in', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $station = Station::factory()->create();
    $employee->stations()->attach($station);

    $this->actingAs($employee);

    Livewire::test(ClockIn::class)
        ->set('station_id', $station->id)
        ->call('clockIn')
        ->assertDispatched('time-log-created');

    $this->assertDatabaseHas('time_logs', [
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'clock_out' => null,
    ]);
});

test('employee cannot clock in twice', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $station = Station::factory()->create();
    $employee->stations()->attach($station);

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'clock_out' => null,
    ]);

    $this->actingAs($employee);

    Livewire::test(ClockIn::class)
        ->set('station_id', $station->id)
        ->call('clockIn')
        ->assertHasErrors(['station_id']);
});
```

### Testing Computed Properties

```php
<?php

use App\Livewire\Employee\Dashboard;

test('shows only today tasks', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $station = Station::factory()->create();
    $employee->stations()->attach($station);

    $todayTask = Task::factory()->create([
        'station_id' => $station->id,
        'due_date' => now(),
    ]);

    $tomorrowTask = Task::factory()->create([
        'station_id' => $station->id,
        'due_date' => now()->addDay(),
    ]);

    $this->actingAs($employee);

    Livewire::test(Dashboard::class)
        ->assertSee($todayTask->name)
        ->assertDontSee($tomorrowTask->name);
});
```

---

## Testing Models

### Basic Model Tests

```php
<?php

use App\Models\Task;
use App\Models\Station;

test('task belongs to station', function () {
    $task = Task::factory()->create();

    expect($task->station)->toBeInstanceOf(Station::class);
});

test('task has completed scope', function () {
    Task::factory()->count(3)->create(['completed' => true]);
    Task::factory()->count(2)->create(['completed' => false]);

    expect(Task::completed()->count())->toBe(3);
});

test('task has overdue scope', function () {
    Task::factory()->count(2)->create([
        'due_date' => now()->subDay(),
        'completed' => false,
    ]);

    Task::factory()->create([
        'due_date' => now()->addDay(),
        'completed' => false,
    ]);

    expect(Task::overdue()->count())->toBe(2);
});
```

### Testing Relationships

```php
<?php

test('station has many tasks', function () {
    $station = Station::factory()
        ->has(Task::factory()->count(3))
        ->create();

    expect($station->tasks)->toHaveCount(3);
});

test('user can have multiple stations', function () {
    $user = User::factory()->create();
    $stations = Station::factory()->count(3)->create();

    $user->stations()->attach($stations);

    expect($user->stations)->toHaveCount(3);
});
```

### Testing Model Methods

```php
<?php

test('task can be marked as completed', function () {
    $task = Task::factory()->create(['completed' => false]);
    $user = User::factory()->create();

    $task->markAsCompleted($user);

    expect($task->fresh())
        ->completed->toBeTrue()
        ->completed_at->not->toBeNull()
        ->completed_by->toBe($user->id);
});

test('time log calculates duration correctly', function () {
    $timeLog = TimeLog::factory()->create([
        'clock_in' => now()->subHours(8),
        'clock_out' => now(),
    ]);

    $timeLog->calculateDuration();

    expect($timeLog->duration_minutes)->toBe(480);
    expect($timeLog->duration_hours)->toBe(8.0);
});
```

### Testing Accessors

```php
<?php

test('task is_overdue accessor works', function () {
    $overdueTask = Task::factory()->create([
        'due_date' => now()->subDay(),
        'completed' => false,
    ]);

    $currentTask = Task::factory()->create([
        'due_date' => now()->addDay(),
        'completed' => false,
    ]);

    expect($overdueTask->is_overdue)->toBeTrue();
    expect($currentTask->is_overdue)->toBeFalse();
});
```

---

## Testing Services

### Testing RecurrenceCalculator

```php
<?php

use App\Services\RecurrenceCalculator;
use App\Models\TaskSchedule;

test('calculates daily recurrence correctly', function () {
    $schedule = TaskSchedule::factory()->create([
        'frequency' => 'daily',
        'start_date' => '2025-01-01',
    ]);

    $calculator = new RecurrenceCalculator();
    $next = $calculator->calculateNextOccurrence($schedule, Carbon::parse('2025-01-01'));

    expect($next->format('Y-m-d'))->toBe('2025-01-02');
});

test('calculates weekly recurrence for specific days', function () {
    $schedule = TaskSchedule::factory()->create([
        'frequency' => 'weekly',
        'start_date' => '2025-01-01', // Wednesday
        'days_of_week' => [1, 3], // Monday, Wednesday
    ]);

    $calculator = new RecurrenceCalculator();
    $next = $calculator->calculateNextOccurrence($schedule, Carbon::parse('2025-01-01'));

    // Next Monday after Wednesday
    expect($next->format('Y-m-d'))->toBe('2025-01-06');
    expect($next->dayOfWeek)->toBe(Carbon::MONDAY);
});

test('handles even/odd week patterns', function () {
    $schedule = TaskSchedule::factory()->create([
        'frequency' => 'weekly',
        'start_date' => '2025-01-01',
        'days_of_week' => [1], // Monday
        'week_type' => 'even',
    ]);

    $calculator = new RecurrenceCalculator();

    // Test generates occurrences only on even weeks
    $occurrences = $calculator->generateOccurrences($schedule, 4);

    foreach ($occurrences as $occurrence) {
        $weekNumber = $occurrence->weekOfYear;
        expect($weekNumber % 2)->toBe(0);
    }
});
```

### Testing SettingsService

```php
<?php

use App\Services\SettingsService;

test('can get setting with default', function () {
    $service = app(SettingsService::class);

    $value = $service->get('non_existent', 'default_value');

    expect($value)->toBe('default_value');
});

test('can set and get setting', function () {
    $service = app(SettingsService::class);

    $service->set('test_key', 'test_value');
    $value = $service->get('test_key');

    expect($value)->toBe('test_value');
});

test('setting is cached', function () {
    $service = app(SettingsService::class);

    $service->set('cached_key', 'cached_value');

    // Delete from database
    \DB::table('settings')->where('key', 'cached_key')->delete();

    // Still returns cached value
    $value = $service->get('cached_key');
    expect($value)->toBe('cached_value');

    // After clearing cache
    Cache::forget('settings.cached_key');
    $value = $service->get('cached_key');
    expect($value)->toBeNull();
});
```

---

## Test Coverage

### Generating Coverage Reports

```bash
# HTML coverage report
./vendor/bin/pest --coverage --coverage-html=coverage

# Open in browser
open coverage/index.html

# Coverage summary in terminal
./vendor/bin/pest --coverage

# Minimum coverage requirement
./vendor/bin/pest --coverage --min=80
```

### Coverage Goals

| Area | Target Coverage |
|------|----------------|
| Models | 90%+ |
| Services | 85%+ |
| Livewire Components | 80%+ |
| Overall | 75%+ |

### Excluding Files from Coverage

**File**: `phpunit.xml`

```xml
<coverage>
    <include>
        <directory suffix=".php">./app</directory>
    </include>
    <exclude>
        <directory suffix=".php">./app/Console</directory>
        <file>./app/Helpers.php</file>
    </exclude>
</coverage>
```

---

## Testing Best Practices

### 1. Use Factories

```php
// Good: Use factories
$user = User::factory()->create();
$station = Station::factory()->create();

// Bad: Manual creation
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'role' => 'employee',
]);
```

### 2. Test One Thing

```php
// Good: Single assertion
test('task name is required', function () {
    actingAsAdmin();

    Livewire::test(Create::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);
});

// Bad: Multiple unrelated assertions
test('task validation', function () {
    // Testing too many things
});
```

### 3. Use Descriptive Test Names

```php
// Good
test('employee cannot clock in to unassigned station')
test('admin can view all time logs')
test('task rollover creates new task for today')

// Bad
test('test clock in')
test('test validation')
```

### 4. Arrange-Act-Assert Pattern

```php
test('task completion records user and timestamp', function () {
    // Arrange
    $task = Task::factory()->create(['completed' => false]);
    $user = User::factory()->create();

    // Act
    $task->markAsCompleted($user);

    // Assert
    expect($task->fresh())
        ->completed->toBeTrue()
        ->completed_by->toBe($user->id)
        ->completed_at->not->toBeNull();
});
```

### 5. Clean Up After Tests

```php
// Use RefreshDatabase trait
uses(RefreshDatabase::class);

// Or DatabaseTransactions for faster tests
uses(DatabaseTransactions::class);
```

---

## CI/CD Integration

### GitHub Actions Example

**File**: `.github/workflows/tests.yml`

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: autoclean_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, bcmath, pdo_mysql

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate key
        run: php artisan key:generate

      - name: Run migrations
        run: php artisan migrate --force

      - name: Run tests
        run: ./vendor/bin/pest --coverage --min=75
```

---

## Useful Testing Helpers

### Custom Expectations

```php
// tests/Pest.php
expect()->extend('toBeOverdue', function () {
    return $this->value->isOverdue() === true;
});

// Usage in tests
test('task is overdue', function () {
    $task = Task::factory()->create([
        'due_date' => now()->subDay(),
        'completed' => false,
    ]);

    expect($task)->toBeOverdue();
});
```

### Custom Assertions

```php
// tests/TestCase.php
protected function assertTaskCompleted(Task $task): void
{
    $this->assertTrue($task->completed);
    $this->assertNotNull($task->completed_at);
    $this->assertNotNull($task->completed_by);
}

// Usage
test('task is completed', function () {
    $task = Task::factory()->create();
    $user = User::factory()->create();

    $task->markAsCompleted($user);

    $this->assertTaskCompleted($task);
});
```

---

## Debugging Tests

### Dump and Die in Tests

```php
test('debug test', function () {
    $task = Task::factory()->create();

    dd($task->toArray());

    // Test continues after removing dd()
});
```

### Ray Integration

```php
test('debug with ray', function () {
    $tasks = Task::all();

    ray($tasks); // Sends to Ray app

    expect($tasks)->toHaveCount(3);
});
```

### Failed Test Screenshots (Livewire)

```php
test('capture screenshot on failure', function () {
    actingAsAdmin();

    Livewire::test(Create::class)
        ->set('name', '')
        ->call('save')
        ->screenshot('failure'); // Saves screenshot
});
```

---

## Next Steps

- Review [Development Guide](development.md) for coding standards
- Check [Contributing Guide](contributing.md) for PR requirements
- See [API Reference](api-reference.md) for component details

---

**Navigation**: [← Development](development.md) | [Back to Documentation](README.md) | [Deployment →](deployment.md)
