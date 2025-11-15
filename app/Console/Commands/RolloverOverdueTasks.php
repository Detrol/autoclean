<?php

namespace App\Console\Commands;

use App\Models\TaskSchedule;
use Illuminate\Console\Command;

class RolloverOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:rollover-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move incomplete overdue tasks from previous days to today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');

        // Markera försenade uppgifter först (körs alltid)
        $this->markOverdueTasks();

        // Kontrollera om rollover är aktiverat
        if (settings('task_rollover_enabled', true)) {
            // Flytta försenade icke-dagliga uppgifter till idag
            $this->moveOverdueTasksToToday();
        } else {
            $this->info('Task rollover is disabled. Overdue tasks will not be moved.');
        }

        return 0;
    }

    private function markOverdueTasks(): void
    {
        $overdueTasks = TaskSchedule::where('status', 'pending')
            ->where(function ($query) {
                $query->whereDate('scheduled_date', '<', now())
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereDate('scheduled_date', '=', now())
                            ->whereTime('due_time', '<', now()->format('H:i:s'));
                    });
            })
            ->update(['status' => 'overdue']);

        if ($overdueTasks > 0) {
            $this->info("Marked {$overdueTasks} tasks as overdue.");
        }
    }

    private function moveOverdueTasksToToday(): void
    {
        $today = now()->format('Y-m-d');

        // Hitta alla försenade uppgifter från tidigare dagar (exkludera dagliga uppgifter)
        $overdueTasks = TaskSchedule::with('task')
            ->where('status', 'overdue')
            ->whereDate('scheduled_date', '<', now())
            ->whereHas('task', function ($query) {
                $query->where('interval_type', '!=', 'daily');
            })
            ->get();

        $movedCount = 0;

        foreach ($overdueTasks as $overdueTask) {
            // Kontrollera om samma uppgift redan är schemalagd för idag
            $existsToday = TaskSchedule::where('task_id', $overdueTask->task_id)
                ->whereDate('scheduled_date', $today)
                ->exists();

            if (! $existsToday) {
                // Flytta uppgiften till idag, behåll status som 'overdue'
                $originalDate = $overdueTask->scheduled_date->format('Y-m-d');

                $overdueTask->update([
                    'scheduled_date' => $today,
                    'notes' => ($overdueTask->notes ? $overdueTask->notes."\n" : '').
                               "Flyttad från {$originalDate} (försenad)",
                ]);

                $movedCount++;
            }
        }

        if ($movedCount > 0) {
            $this->info("Moved {$movedCount} overdue non-daily tasks to today.");
        }
    }
}
