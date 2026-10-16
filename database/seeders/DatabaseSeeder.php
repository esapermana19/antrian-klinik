<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\PracticeSchedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Sequence;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'username' => 'administrator',
            'email' => 'admin@klinik.test',
            'password' => Hash::make('password'),
            'role' => 'administrator'
        ]);

        $user = User::factory()->create([
            'username' => 'Samsul Maulana',
            'email' => 'samsul@klinik.test',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        Patient::firstOrCreate(
            ['user_id' => $user->user_id],
            [
                'nik' => '3201234567890001',
                'patient_name' => $user->username,
                'date_of_birth' => '1998-08-20',
                'gender' => 'male',
            ]
        );

        //jadwal praktek
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        PracticeSchedule::factory()
            ->count(count($days))
            ->sequence(fn(Sequence $sequence) => [
                'schedule_code' => sprintf('P%02d', $sequence->index + 1),
                'day' => $days[$sequence->index],
                'session' => 'morning',
                'opening_hours' => '08:00:00',
                'closing_hours' => '12:00:00',
                'max_quota' => 25,
                'status' => 'open',
            ])
            ->create();
        Medication::factory()->count(12)->create();

        // // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
