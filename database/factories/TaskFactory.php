<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'interval_type' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'interval_value' => $this->faker->numberBetween(1, 7),
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'recurrence_pattern' => [],
            'end_date' => null,
            'occurrences' => null,
            'default_due_time' => $this->faker->time('H:i'),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the task is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a daily task.
     */
    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'interval_type' => 'daily',
            'interval_value' => 1,
        ]);
    }

    /**
     * Create a weekly task.
     */
    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'interval_type' => 'weekly',
            'interval_value' => 1,
        ]);
    }

    /**
     * Create a monthly task.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'interval_type' => 'monthly',
            'interval_value' => 1,
        ]);
    }
}
