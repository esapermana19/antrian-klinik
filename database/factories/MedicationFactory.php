<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medication>
 */
class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        $medications = [
            'Paracetamol 500mg',
            'Amoxicillin 500mg',
            'Ibuprofen 400mg',
            'Cetirizine 10mg',
            'Omeprazole 20mg',
            'Metformin 500mg',
            'Amlodipine 10mg',
            'Vitamin C 500mg',
            'Antasida Doen',
            'Dexamethasone 0.5mg',
            'Ciprofloxacin 500mg',
            'Salbutamol 2mg',
            'Lansoprazole 30mg',
            'Mefenamic Acid 500mg',
            'Loratadine 10mg',
        ];

        return [
            'medication_code' => 'MED-' . fake()->unique()->numberBetween(100, 999),
            'medication_name' => fake()->unique()->randomElement($medications),
            'stock' => fake()->numberBetween(50, 100),
            // Harga kelipatan 1.000 antara Rp10.000 - Rp50.000
            'unit_price' => fake()->numberBetween(10, 50) * 1000,
        ];
    }
}
