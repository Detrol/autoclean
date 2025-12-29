<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskSchedule>
 */
class TaskScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = TaskSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'scheduled_date' => $this->faker->date('Y-m-d'),
            'due_time' => $this->faker->time('H:i'),
            'status' => 'pending',
            'completed_at' => null,
            'completed_by' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate this schedule is completed.
     */
    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => User::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ]);
    }

    /**
     * Indicate this schedule is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => 'overdue',
        ]);
    }
}
