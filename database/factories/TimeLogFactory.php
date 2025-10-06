<?php

namespace Database\Factories;

use App\Models\TimeLog;
use App\Models\User;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeLog>
 */
class TimeLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = TimeLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::parse($this->faker->date('Y-m-d'));
        $clockIn = $date->copy()->setTime(8, 0, 0);
        $minutes = $this->faker->numberBetween(60, 540);
        $clockOut = $clockIn->copy()->addMinutes($minutes);

        return [
            'user_id' => User::factory(),
            'station_id' => Station::factory(),
            'is_oncall' => false,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'date' => $date,
            'total_minutes' => $minutes,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate this is an on-call log.
     */
    public function oncall(): static
    {
        return $this->state(fn () => [
            'is_oncall' => true,
        ]);
    }

    /**
     * Indicate an incomplete log (no clock_out / total_minutes).
     */
    public function incomplete(): static
    {
        return $this->state(fn () => [
            'clock_out' => null,
            'total_minutes' => null,
        ]);
    }
}
