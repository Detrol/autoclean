<?php

namespace Database\Factories;

use App\Models\CompletedAdditionalTask;
use App\Models\Station;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompletedAdditionalTask>
 */
class CompletedAdditionalTaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = CompletedAdditionalTask::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'user_id' => User::factory(),
            'task_template_id' => null,
            'task_name' => $this->faker->words(3, true),
            'completed_date' => $this->faker->date('Y-m-d'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
