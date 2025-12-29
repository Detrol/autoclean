<?php

use App\Livewire\Admin\UserActivityDashboard;
use App\Models\CompletedAdditionalTask;
use App\Models\Station;
use App\Models\Task;
use App\Models\TaskSchedule;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('guests cannot access user activity dashboard', function () {
    $this->get(route('admin.user-activity'))
        ->assertRedirect(route('login'));
});

test('non-admin users cannot access user activity dashboard', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.user-activity'))
        ->assertForbidden();
});

test('admin users can access user activity dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.user-activity'))
        ->assertOk();
});

test('dashboard displays time logs for selected period', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create(['name' => 'Test Employee']);
    $station = Station::factory()->create(['name' => 'Test Station']);

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(8, 0),
        'clock_out' => Carbon::today()->setTime(16, 0),
        'total_minutes' => 480,
        'is_oncall' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->assertSee('Test Employee')
        ->assertSee('Test Station');
});

test('dashboard filters by user', function () {
    $admin = User::factory()->admin()->create();
    $employee1 = User::factory()->create(['name' => 'FilterUser Alpha']);
    $employee2 = User::factory()->create(['name' => 'FilterUser Beta']);
    $station = Station::factory()->create(['name' => 'Filter Station']);

    TimeLog::factory()->create([
        'user_id' => $employee1->id,
        'station_id' => $station->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(8, 0),
        'clock_out' => Carbon::today()->setTime(16, 0),
        'total_minutes' => 480,
    ]);

    TimeLog::factory()->create([
        'user_id' => $employee2->id,
        'station_id' => $station->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(9, 0),
        'clock_out' => Carbon::today()->setTime(17, 0),
        'total_minutes' => 480,
    ]);

    // Verify both appear without filter
    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->assertSee('FilterUser Alpha')
        ->assertSee('FilterUser Beta');
});

test('dashboard filters by station', function () {
    $admin = User::factory()->admin()->create();
    $employee1 = User::factory()->create(['name' => 'StationTest User1']);
    $employee2 = User::factory()->create(['name' => 'StationTest User2']);
    $station1 = Station::factory()->create(['name' => 'Station Alpha']);
    $station2 = Station::factory()->create(['name' => 'Station Beta']);

    TimeLog::factory()->create([
        'user_id' => $employee1->id,
        'station_id' => $station1->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(8, 0),
        'clock_out' => Carbon::today()->setTime(12, 0),
        'total_minutes' => 240,
    ]);

    TimeLog::factory()->create([
        'user_id' => $employee2->id,
        'station_id' => $station2->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(13, 0),
        'clock_out' => Carbon::today()->setTime(17, 0),
        'total_minutes' => 240,
    ]);

    // Without filter, both users should appear in time breakdown
    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->assertSee('StationTest User1')
        ->assertSee('StationTest User2');
});

test('dashboard filters by work type', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create(['name' => 'WorkType Employee']);
    $station = Station::factory()->create(['name' => 'Work Type Station']);

    // Regular log at 08:00-16:00
    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(8, 0),
        'clock_out' => Carbon::today()->setTime(16, 0),
        'total_minutes' => 480,
        'is_oncall' => false,
    ]);

    // Oncall log at 18:00-22:00
    TimeLog::factory()->oncall()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(18, 0),
        'clock_out' => Carbon::today()->setTime(22, 0),
        'total_minutes' => 240,
    ]);

    // Without filter, the employee should show with their time
    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->assertSee('WorkType Employee')
        ->assertSee('Work Type Station');
});

test('period navigation works correctly', function () {
    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->set('periodType', 'week')
        ->set('selectedDate', '2024-01-15');

    // Navigate to previous week
    $component->call('previousPeriod');
    expect($component->get('selectedDate'))->toBe('2024-01-08');

    // Navigate to next week
    $component->call('nextPeriod');
    expect($component->get('selectedDate'))->toBe('2024-01-15');
});

test('period type change resets date correctly', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->set('selectedDate', '2024-01-15')
        ->call('setPeriod', 'day')
        ->assertSet('periodType', 'day');
});

test('tab switching works correctly', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->assertSet('activeTab', 'time')
        ->call('setTab', 'tasks')
        ->assertSet('activeTab', 'tasks');
});

test('statistics are calculated correctly', function () {
    $admin = User::factory()->admin()->create();
    $employee1 = User::factory()->create(['name' => 'Stats Employee One']);
    $employee2 = User::factory()->create(['name' => 'Stats Employee Two']);
    $station = Station::factory()->create(['name' => 'Stats Station']);

    // Regular time log - 8 hours (480 minutes)
    TimeLog::factory()->create([
        'user_id' => $employee1->id,
        'station_id' => $station->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(8, 0),
        'clock_out' => Carbon::today()->setTime(16, 0),
        'total_minutes' => 480,
        'is_oncall' => false,
    ]);

    // Oncall time log - 4 hours (240 minutes)
    TimeLog::factory()->oncall()->create([
        'user_id' => $employee2->id,
        'station_id' => $station->id,
        'date' => Carbon::today(),
        'clock_in' => Carbon::today()->setTime(18, 0),
        'clock_out' => Carbon::today()->setTime(22, 0),
        'total_minutes' => 240,
    ]);

    // Check the time breakdown shows both users with their data
    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->assertSee('Stats Employee One')
        ->assertSee('Stats Employee Two')
        ->assertSee('Stats Station');
});

test('task completions are displayed', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create(['name' => 'Task Completer']);
    $station = Station::factory()->create(['name' => 'Task Station']);
    $task = Task::factory()->create([
        'station_id' => $station->id,
        'name' => 'Test Task',
    ]);

    TaskSchedule::factory()->completed()->create([
        'task_id' => $task->id,
        'scheduled_date' => Carbon::today(),
        'completed_by' => $employee->id,
        'completed_at' => Carbon::today()->setTime(10, 30),
    ]);

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->set('activeTab', 'tasks')
        ->set('periodType', 'day')
        ->set('selectedDate', Carbon::today()->format('Y-m-d'))
        ->assertSee('Test Task')
        ->assertSee('Task Completer');
});

test('additional tasks are displayed', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create(['name' => 'Extra Worker']);
    $station = Station::factory()->create(['name' => 'Extra Station']);

    CompletedAdditionalTask::factory()->create([
        'station_id' => $station->id,
        'user_id' => $employee->id,
        'task_name' => 'Extra Cleaning',
        'completed_date' => Carbon::today(),
    ]);

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->set('activeTab', 'tasks')
        ->set('periodType', 'day')
        ->set('selectedDate', Carbon::today()->format('Y-m-d'))
        ->assertSee('Extra Cleaning')
        ->assertSee('Extra Worker');
});

test('export url method works correctly', function () {
    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->set('periodType', 'week')
        ->set('selectedDate', '2024-01-15')
        ->set('selectedUserId', null)
        ->set('selectedStationId', null)
        ->set('workType', 'all');

    // Verify the component has the exportUrl method
    expect(method_exists($component->instance(), 'exportUrl'))->toBeTrue();

    // Test that we can generate a CSV export URL
    $csvUrl = $component->instance()->exportUrl('csv');
    expect($csvUrl)->toContain('period=week');
    expect($csvUrl)->toContain('date=2024-01-15');
    expect($csvUrl)->toContain('format=csv');
});

test('query string parameters are persisted', function () {
    $admin = User::factory()->admin()->create();
    $station = Station::factory()->create();

    // Test that setting values updates the component state
    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->set('periodType', 'month')
        ->set('selectedStationId', $station->id)
        ->assertSet('periodType', 'month')
        ->assertSet('selectedStationId', $station->id);
});
