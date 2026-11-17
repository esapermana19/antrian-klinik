<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Inisialisasi Pengaturan Default Klinik
        Setting::set('clinic_name', 'KLINIK SEHAT MEDIKA');
        Setting::set('clinic_address', 'Jl. Merdeka Sejahtera No. 88, Bandung');
        Setting::set('clinic_phone', '(022) 765-4321');

        // 2. Akun Administrator Default
        User::firstOrCreate(
            ['email' => 'admin@klinik.test'],
            [
                'user_code' => User::generateUserCode(),
                'username' => 'administrator',
                'password' => Hash::make('password'),
                'role' => 'administrator',
                'email_verified_at' => now(),
            ]
        );

        // 3. Akun Pasien Default (Demo User)
        $demoUser = User::firstOrCreate(
            ['email' => 'user@klinik.test'],
            [
                'user_code' => User::generateUserCode(),
                'username' => 'Samsul Maulana',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        Patient::firstOrCreate(
            ['nik' => '3201234567890001'],
            [
                'user_id' => $demoUser->user_id,
                'patient_name' => 'Samsul Maulana',
                'date_of_birth' => '1998-08-20',
                'gender' => 'male',
                'address' => 'Jl. Sukajadi No. 12, Bandung',
            ]
        );

        // 4. Jalankan seeder modul terkait
        $this->call([
            DoctorSeeder::class,
            MedicationCategorySeeder::class,
            PracticeScheduleSeeder::class,
            QueueAndMedicalRecordSeeder::class,
        ]);
    }
}
