<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\TaskSchedule;
use App\Services\RecurrenceCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateTaskSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:generate {--days=7 : Number of days to generate schedules for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate task schedules based on task intervals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $startDate = now()->startOfDay();
        $endDate = now()->addDays($days)->endOfDay();

        $this->info("Generating task schedules from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        $tasks = Task::active()->get();
        $generatedCount = 0;

        foreach ($tasks as $task) {
            $generated = $this->generateSchedulesForTask($task, $startDate->copy(), $endDate->copy());
            $generatedCount += $generated;
        }

        $this->info("Generated {$generatedCount} task schedules successfully!");
        
        // Markera försenade uppgifter
        $this->markOverdueTasks();
        
        return 0;
    }

    private function generateSchedulesForTask(Task $task, Carbon $startDate, Carbon $endDate): int
    {
        $generatedCount = 0;
        $currentDate = $startDate->copy();
        $calculator = new RecurrenceCalculator();

        while ($currentDate <= $endDate) {
            if ($calculator->shouldGenerateTask($task, $currentDate)) {
                // Kontrollera om uppgiften redan är schemalagd för denna dag
                $existingSchedule = TaskSchedule::where('task_id', $task->id)
                    ->whereDate('scheduled_date', $currentDate)
                    ->first();

                if (!$existingSchedule) {
                    $dueTime = $this->calculateDueTime($task, $currentDate);

                    TaskSchedule::create([
                        'task_id' => $task->id,
                        'scheduled_date' => $currentDate->format('Y-m-d'),
                        'due_time' => $dueTime,
                        'status' => 'pending',
                    ]);

                    $generatedCount++;
                }
            }

            $currentDate->addDay();
        }

        return $generatedCount;
    }


    private function calculateDueTime(Task $task, Carbon $date): string
    {
        if ($task->default_due_time) {
            return $task->default_due_time->format('H:i:s');
        }

        // Standard due time baserat på intervall
        switch ($task->interval_type) {
            case 'daily':
                return '18:00:00'; // Dagliga uppgifter till 18:00
            case 'weekly':
                return '17:00:00'; // Veckoupgifter till 17:00
            case 'monthly':
                return '16:00:00'; // Månadsuppgifter till 16:00
            case 'yearly':
                return '15:00:00'; // Årliga uppgifter till 15:00
            case 'custom':
                return '19:00:00'; // Anpassade uppgifter till 19:00
            default:
                return '17:00:00';
        }
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
}
