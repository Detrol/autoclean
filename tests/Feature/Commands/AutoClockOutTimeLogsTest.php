<?php

use App\Models\Setting;
use App\Models\TimeLog;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('auto clock-out closes stale time logs', function () {
    Setting::updateOrCreate(
        ['key' => 'auto_clock_out_enabled'],
        ['value' => '1', 'type' => 'boolean', 'group' => 'timekeeping', 'label' => '', 'description' => '']
    );
    Setting::updateOrCreate(
        ['key' => 'auto_clock_out_hours'],
        ['value' => '12', 'type' => 'integer', 'group' => 'timekeeping', 'label' => '', 'description' => '']
    );

    $timeLog = TimeLog::factory()->create([
        'clock_in' => now()->subHours(14),
        'clock_out' => null,
        'total_minutes' => null,
    ]);

    $this->artisan('timelogs:auto-clock-out')
        ->assertExitCode(0);

    $timeLog->refresh();
    expect($timeLog->clock_out)->not->toBeNull();
    expect($timeLog->total_minutes)->toBe(720);
    expect($timeLog->notes)->toContain('Automatisk utklocking');
});

test('auto clock-out does not touch recent active logs', function () {
    Setting::updateOrCreate(
        ['key' => 'auto_clock_out_enabled'],
        ['value' => '1', 'type' => 'boolean', 'group' => 'timekeeping', 'label' => '', 'description' => '']
    );
    Setting::updateOrCreate(
        ['key' => 'auto_clock_out_hours'],
        ['value' => '12', 'type' => 'integer', 'group' => 'timekeeping', 'label' => '', 'description' => '']
    );

    $timeLog = TimeLog::factory()->create([
        'clock_in' => now()->subHours(2),
        'clock_out' => null,
        'total_minutes' => null,
    ]);

    $this->artisan('timelogs:auto-clock-out')
        ->assertExitCode(0);

    $timeLog->refresh();
    expect($timeLog->clock_out)->toBeNull();
});

test('auto clock-out respects disabled setting', function () {
    Setting::updateOrCreate(
        ['key' => 'auto_clock_out_enabled'],
        ['value' => '0', 'type' => 'boolean', 'group' => 'timekeeping', 'label' => '', 'description' => '']
    );

    $timeLog = TimeLog::factory()->create([
        'clock_in' => now()->subHours(14),
        'clock_out' => null,
        'total_minutes' => null,
    ]);

    $this->artisan('timelogs:auto-clock-out')
        ->assertExitCode(0);

    $timeLog->refresh();
    expect($timeLog->clock_out)->toBeNull();
});
