<?php

use App\Models\Task;
use App\Models\TaskSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it moves overdue tasks to today while keeping overdue status (non-daily via rollover command)', function () {
    // Create a non-daily test task (weekly) to be eligible for rollover
    $task = Task::factory()->create([
        'name' => 'Test Task',
        'interval_type' => 'weekly',
        'is_active' => true,
    ]);

    // Create an overdue task schedule from yesterday
    $yesterday = now()->subDay()->format('Y-m-d');
    $overdueSchedule = TaskSchedule::create([
        'task_id' => $task->id,
        'scheduled_date' => $yesterday,
        'due_time' => '18:00:00',
        'status' => 'overdue',
    ]);

    // Run the rollover command
    $this->artisan('tasks:rollover-overdue')
        ->expectsOutput('Moved 1 overdue non-daily tasks to today.');

    // Assert the task was moved to today but kept its overdue status
    $todaysTask = TaskSchedule::where('task_id', $task->id)
        ->whereDate('scheduled_date', now())
        ->first();

    expect($todaysTask)->not->toBeNull()
        ->and($todaysTask->status)->toBe('overdue')
        ->and($todaysTask->notes)->toContain('Flyttad från')
        ->and($todaysTask->notes)->toContain('(försenad)');

    // Assert the old schedule no longer exists for yesterday
    $yesterdaysTask = TaskSchedule::where('task_id', $task->id)
        ->whereDate('scheduled_date', $yesterday)
        ->first();

    expect($yesterdaysTask)->toBeNull();
});

test('it does not move overdue task if same task already exists for today (non-daily via rollover command)', function () {
    // Create a non-daily test task (weekly)
    $task = Task::factory()->create([
        'name' => 'Test Task',
        'interval_type' => 'weekly',
        'is_active' => true,
    ]);

    // Create an overdue task from yesterday
    TaskSchedule::create([
        'task_id' => $task->id,
        'scheduled_date' => now()->subDay()->format('Y-m-d'),
        'due_time' => '18:00:00',
        'status' => 'overdue',
    ]);

    // Create a task for today (regular schedule)
    TaskSchedule::create([
        'task_id' => $task->id,
        'scheduled_date' => now()->format('Y-m-d'),
        'due_time' => '23:59:00',
        'status' => 'pending',
    ]);

    // Run the rollover command (should not move since one exists today)
    $this->artisan('tasks:rollover-overdue');

    // Assert no tasks were moved (still only one for today)
    $todaysTasks = TaskSchedule::where('task_id', $task->id)
        ->whereDate('scheduled_date', now())
        ->get();

    expect($todaysTasks)->toHaveCount(1)
        ->and($todaysTasks->first()->status)->toBe('pending'); // Original status preserved
});
