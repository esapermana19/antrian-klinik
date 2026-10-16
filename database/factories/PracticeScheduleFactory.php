<?php

namespace Database\Factories;

use App\Models\PracticeSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticeSchedule>
 */
class PracticeScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = PracticeSchedule::class;

    public function definition(): array
    {
        $session = fake()->randomElement(['morning', 'afternoon']);

        return [
            'schedule_code' => 'P'.str_pad((string) fake()->unique()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'day' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            'session' => $session,
            'opening_hours' => $session === 'morning' ? '08:00:00' : '13:00:00',
            'closing_hours' => $session === 'morning' ? '12:00:00' : '17:00:00',
            'max_quota' => fake()->numberBetween(20, 30),
            'status' => 'open',
        ];
    }
}
