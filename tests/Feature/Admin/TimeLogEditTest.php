<?php

use App\Livewire\Admin\UserActivityDashboard;
use App\Models\Station;
use App\Models\TimeLog;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin can edit a time log', function () {
    $admin = User::factory()->admin()->create();
    $station = Station::factory()->create();
    $timeLog = TimeLog::factory()->create([
        'station_id' => $station->id,
        'date' => '2026-03-05',
        'clock_in' => '2026-03-05 08:00',
        'clock_out' => '2026-03-05 16:00',
        'total_minutes' => 480,
    ]);

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->call('editTimeLog', $timeLog->id)
        ->assertSet('showTimeLogModal', true)
        ->assertSet('isCreating', false)
        ->assertSet('editingTimeLogId', $timeLog->id)
        ->set('formClockIn', '09:00')
        ->set('formClockOut', '17:00')
        ->call('saveTimeLog')
        ->assertHasNoErrors()
        ->assertSet('showTimeLogModal', false);

    $timeLog->refresh();
    expect($timeLog->clock_in->format('H:i'))->toBe('09:00');
    expect($timeLog->clock_out->format('H:i'))->toBe('17:00');
    expect($timeLog->total_minutes)->toBe(480);
});

test('admin can create a new time log', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $station = Station::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->call('createTimeLog')
        ->assertSet('showTimeLogModal', true)
        ->assertSet('isCreating', true)
        ->set('formUserId', $employee->id)
        ->set('formStationId', $station->id)
        ->set('formDate', '2026-03-05')
        ->set('formClockIn', '08:00')
        ->set('formClockOut', '12:00')
        ->call('saveTimeLog')
        ->assertHasNoErrors()
        ->assertSet('showTimeLogModal', false);

    expect(TimeLog::where('user_id', $employee->id)->exists())->toBeTrue();

    $log = TimeLog::where('user_id', $employee->id)->first();
    expect($log->total_minutes)->toBe(240);
});

test('admin can delete a time log', function () {
    $admin = User::factory()->admin()->create();
    $timeLog = TimeLog::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->call('editTimeLog', $timeLog->id)
        ->call('deleteTimeLog')
        ->assertSet('showTimeLogModal', false);

    expect(TimeLog::find($timeLog->id))->toBeNull();
});

test('validation rejects missing required fields', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->call('createTimeLog')
        ->set('formUserId', null)
        ->set('formStationId', null)
        ->set('formDate', '')
        ->set('formClockIn', '')
        ->set('formClockOut', '')
        ->call('saveTimeLog')
        ->assertHasErrors(['formUserId', 'formStationId', 'formDate', 'formClockIn', 'formClockOut']);
});

test('midnight crossing shift calculates correct total minutes', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $station = Station::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->call('createTimeLog')
        ->set('formUserId', $employee->id)
        ->set('formStationId', $station->id)
        ->set('formDate', '2026-03-05')
        ->set('formClockIn', '22:00')
        ->set('formClockOut', '02:00')
        ->call('saveTimeLog')
        ->assertHasNoErrors();

    $log = TimeLog::where('user_id', $employee->id)->first();
    expect($log->total_minutes)->toBe(240);
    expect($log->clock_out->format('Y-m-d'))->toBe('2026-03-06');
});

test('dashboard shows active time logs', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $station = Station::factory()->create();

    TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'clock_in' => now()->subHours(2),
        'clock_out' => null,
        'total_minutes' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->assertSee('Aktiva pass');
});

test('admin can clock out active time log via dashboard', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $station = Station::factory()->create();

    $timeLog = TimeLog::factory()->create([
        'user_id' => $employee->id,
        'station_id' => $station->id,
        'clock_in' => now()->subHours(2),
        'clock_out' => null,
        'total_minutes' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(UserActivityDashboard::class)
        ->call('adminClockOut', $timeLog->id)
        ->assertHasNoErrors();

    $timeLog->refresh();
    expect($timeLog->clock_out)->not->toBeNull();
    expect($timeLog->notes)->toBe('Utklockad av admin');
});

test('non-admin cannot access the dashboard', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserActivityDashboard::class)
        ->assertForbidden();
});
