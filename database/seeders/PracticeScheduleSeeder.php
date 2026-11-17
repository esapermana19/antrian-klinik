<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\PracticeSchedule;
use Illuminate\Database\Seeder;

class PracticeScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();
        if ($doctors->isEmpty()) {
            return;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Distribusikan jadwal ke dokter-dokter secara realistis
        $scheduleIndex = 1;
        foreach ($doctors as $docIdx => $doctor) {
            foreach ($days as $day) {
                // Tiap dokter praktik pada hari tertentu
                $session = ($docIdx % 2 === 0) ? 'morning' : 'afternoon';
                $openTime = $session === 'morning' ? '08:00:00' : '13:00:00';
                $closeTime = $session === 'morning' ? '12:00:00' : '17:00:00';

                PracticeSchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctor->doctor_id,
                        'day' => $day,
                        'session' => $session,
                    ],
                    [
                        'schedule_code' => sprintf('SCH-%03d', $scheduleIndex++),
                        'opening_hours' => $openTime,
                        'closing_hours' => $closeTime,
                        'max_quota' => 20,
                        'status' => 'open',
                    ]
                );
            }
        }
    }
}
