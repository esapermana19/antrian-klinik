<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nik' => fake()->unique()->numerify('320#############'),
            'patient_name' => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years','-15 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male','female']),
        ];
    }
}
