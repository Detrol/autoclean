<?php

use App\Models\Task;
use App\Models\TaskSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it moves overdue tasks to today while keeping overdue status', function () {
    // Create a test task
    $task = Task::factory()->create([
        'name' => 'Test Task',
        'interval_type' => 'daily',
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

    // Run the command
    $this->artisan('tasks:generate', ['--days' => 1])
        ->expectsOutput('Moved 1 overdue tasks to today.');

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

test('it does not move overdue task if same task already exists for today', function () {
    // Create a test task
    $task = Task::factory()->create([
        'name' => 'Test Task',
        'interval_type' => 'daily',
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
        'due_time' => '18:00:00',
        'status' => 'pending',
    ]);

    // Run the command
    $this->artisan('tasks:generate', ['--days' => 1])
        ->expectsOutput('Generated 0 task schedules successfully!');

    // Assert no tasks were moved (should output 0 moved)
    $todaysTasks = TaskSchedule::where('task_id', $task->id)
        ->whereDate('scheduled_date', now())
        ->get();

    expect($todaysTasks)->toHaveCount(1)
        ->and($todaysTasks->first()->status)->toBe('pending'); // Original status preserved
});