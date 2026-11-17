<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            [
                'doctor_name' => 'dr. Budi Santoso, Sp.A',
                'specialization' => 'Spesialis Anak',
                'queue_prefix' => 'ANK',
                'str_number' => '31.1.1.100.1.21.123456',
                'phone_number' => '081234567891',
                'is_active' => true,
            ],
            [
                'doctor_name' => 'dr. Siti Rahmawati, Sp.PD',
                'specialization' => 'Penyakit Dalam',
                'queue_prefix' => 'INT',
                'str_number' => '31.1.1.100.2.22.654321',
                'phone_number' => '081234567892',
                'is_active' => true,
            ],
            [
                'doctor_name' => 'drg. Anisa Permata',
                'specialization' => 'Dokter Gigi',
                'queue_prefix' => 'GGI',
                'str_number' => '31.2.1.200.1.20.789101',
                'phone_number' => '081234567893',
                'is_active' => true,
            ],
            [
                'doctor_name' => 'dr. Hendra Wijaya',
                'specialization' => 'Dokter Umum',
                'queue_prefix' => 'UMM',
                'str_number' => '31.1.1.100.3.19.112233',
                'phone_number' => '081234567894',
                'is_active' => true,
            ],
        ];

        foreach ($doctors as $doc) {
            Doctor::firstOrCreate(
                ['doctor_name' => $doc['doctor_name']],
                $doc
            );
        }
    }
}
