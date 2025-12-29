<?php

use App\Models\CompletedAdditionalTask;
use App\Models\Station;
use App\Models\Task;
use App\Models\TaskSchedule;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;

test('guests cannot access admin user activity export', function () {
    $response = $this->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'csv',
    ]));

    $response->assertRedirect(route('login'));
});

test('non-admin users cannot access admin user activity export', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'csv',
    ]));

    $response->assertForbidden();
});

test('export validates required parameters', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'invalid',
        'date' => 'invalid-date',
    ]));

    $response->assertSessionHasErrors(['period', 'date']);
});

test('csv export generates correct filename and headers', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $station = Station::factory()->create(['name' => 'Test Station']);

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:00:00'),
        'total_minutes' => 480,
        'is_oncall' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'csv',
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertDownload('anvandaraktivitet-week-2024-01-15-2024-01-21.csv');
});

test('csv export contains correct swedish headers and formatting', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create(['name' => 'Test Employee', 'email' => 'test@example.com']);
    $station = Station::factory()->create(['name' => 'Test Station']);

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:30:00'),
        'total_minutes' => 510,
        'is_oncall' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
    ]));

    $content = $response->streamedContent();

    // Check UTF-8 BOM
    expect(substr($content, 0, 3))->toBe("\xEF\xBB\xBF");

    // Check Swedish headers and formatting
    expect($content)->toContain('Sammanfattning');
    expect($content)->toContain('Aktiva användare');
    expect($content)->toContain('Tidsloggar');
    expect($content)->toContain('Test Employee');
    expect($content)->toContain('Test Station');
    expect($content)->toContain('Ordinarie');

    // Check CRLF line endings
    expect($content)->toContain("\r\n");
});

test('csv export handles empty dataset correctly', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'month',
        'date' => '2024-01-15',
    ]));

    $content = $response->streamedContent();

    expect($content)->toContain('Sammanfattning');
    expect($content)->toContain('Aktiva användare;0');
    expect($content)->toContain('Ingen data för vald period');
});

test('csv export filters by user', function () {
    $admin = User::factory()->admin()->create();
    $employee1 = User::factory()->create(['name' => 'Employee One']);
    $employee2 = User::factory()->create(['name' => 'Employee Two']);
    $station = Station::factory()->create();

    TimeLog::factory()->create([
        'user_id' => $employee1->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:00:00'),
        'total_minutes' => 480,
    ]);

    TimeLog::factory()->create([
        'user_id' => $employee2->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 09:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 17:00:00'),
        'total_minutes' => 480,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'user_id' => $employee1->id,
    ]));

    $content = $response->streamedContent();

    expect($content)->toContain('Employee One');
    expect($content)->not->toContain('Employee Two');
});

test('csv export filters by station', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $station1 = Station::factory()->create(['name' => 'Station Alpha']);
    $station2 = Station::factory()->create(['name' => 'Station Beta']);

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station1->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 12:00:00'),
        'total_minutes' => 240,
    ]);

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station2->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 13:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 17:00:00'),
        'total_minutes' => 240,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'station_id' => $station1->id,
    ]));

    $content = $response->streamedContent();

    expect($content)->toContain('Station Alpha');
    expect($content)->not->toContain('Station Beta');
});

test('csv export filters by work type', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create(['name' => 'Test Employee']);
    $station = Station::factory()->create(['name' => 'Test Station']);

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:00:00'),
        'total_minutes' => 480,
        'is_oncall' => false,
    ]);

    TimeLog::factory()->oncall()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 18:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 22:00:00'),
        'total_minutes' => 240,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'work_type' => 'oncall',
    ]));

    $content = $response->streamedContent();

    // When filtering by oncall, should only have oncall entries in the time logs
    expect($content)->toContain('Jour');

    // Count occurrences of Jour and Ordinarie in data rows (not headers)
    $lines = explode("\r\n", $content);
    $jourRows = array_filter($lines, fn ($line) => str_contains($line, '2024-01-15') && str_contains($line, 'Jour'));
    $regularRows = array_filter($lines, fn ($line) => str_contains($line, '2024-01-15') && str_contains($line, 'Ordinarie'));

    expect(count($jourRows))->toBe(1);
    expect(count($regularRows))->toBe(0);
});

test('csv export includes task completions', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create(['name' => 'Task Worker']);
    $station = Station::factory()->create(['name' => 'Task Station']);
    $task = Task::factory()->create([
        'station_id' => $station->id,
        'name' => 'Scheduled Task',
    ]);

    TaskSchedule::factory()->completed()->create([
        'task_id' => $task->id,
        'scheduled_date' => '2024-01-15',
        'completed_by' => $employee->id,
        'completed_at' => Carbon::parse('2024-01-15 10:30:00'),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
    ]));

    $content = $response->streamedContent();

    expect($content)->toContain('Schemalagda uppgifter');
    expect($content)->toContain('Scheduled Task');
    expect($content)->toContain('Task Worker');
});

test('csv export includes additional tasks', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create(['name' => 'Extra Worker']);
    $station = Station::factory()->create(['name' => 'Extra Station']);

    CompletedAdditionalTask::factory()->create([
        'station_id' => $station->id,
        'user_id' => $employee->id,
        'task_name' => 'Extra Cleaning',
        'completed_date' => '2024-01-15',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
    ]));

    $content = $response->streamedContent();

    expect($content)->toContain('Extra uppgifter');
    expect($content)->toContain('Extra Cleaning');
    expect($content)->toContain('Extra Worker');
});

test('pdf export generates correct filename and headers', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $station = Station::factory()->create();

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:00:00'),
        'total_minutes' => 480,
        'is_oncall' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'pdf',
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertDownload('anvandaraktivitet-week-2024-01-15-2024-01-21.pdf');
});

test('export validates format parameter correctly', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'invalid-format',
    ]));

    $response->assertSessionHasErrors(['format']);
});

test('export supports all period types', function (string $period) {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.user-activity.export', [
        'period' => $period,
        'date' => '2024-01-15',
        'format' => 'csv',
    ]));

    $response->assertOk();
})->with(['day', 'week', 'month', 'year']);
