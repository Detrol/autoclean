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
    protected $signature = 'tasks:generate {--days=7 : Number of days to generate schedules for} {--date= : Start date for generation (default: today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate task schedules based on task intervals (moving overdue tasks is now handled by tasks:rollover-overdue)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        // Använd custom date eller default till idag (nu när rollover är separat)
        $startDateInput = $this->option('date');
        $startDate = $startDateInput ?
            \Carbon\Carbon::parse($startDateInput)->startOfDay() :
            now()->startOfDay();

        $endDate = $startDate->copy()->addDays($days)->endOfDay();

        $this->info("Generating task schedules from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        $tasks = Task::active()->get();
        $generatedCount = 0;

        foreach ($tasks as $task) {
            $generated = $this->generateSchedulesForTask($task, $startDate->copy(), $endDate->copy());
            $generatedCount += $generated;
        }

        $this->info("Generated {$generatedCount} task schedules successfully!");

        return 0;
    }

    private function generateSchedulesForTask(Task $task, Carbon $startDate, Carbon $endDate): int
    {
        $generatedCount = 0;
        $currentDate = $startDate->copy();
        $calculator = new RecurrenceCalculator;

        while ($currentDate <= $endDate) {
            if ($calculator->shouldGenerateTask($task, $currentDate)) {
                // Kontrollera om uppgiften redan är schemalagd för denna dag
                $existingSchedule = TaskSchedule::where('task_id', $task->id)
                    ->whereDate('scheduled_date', $currentDate)
                    ->first();

                if (! $existingSchedule) {
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
}
