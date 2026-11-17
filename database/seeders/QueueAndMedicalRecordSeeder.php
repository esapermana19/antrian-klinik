<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PracticeSchedule;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QueueAndMedicalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();
        $dayName = strtolower($today->englishDayOfWeek);

        // Ambil dokter dan jadwal hari ini
        $schedules = PracticeSchedule::with('doctor')
            ->where('day', $dayName)
            ->where('status', 'open')
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        // Buat beberapa user pasien contoh
        $patientsData = [
            ['name' => 'Budi Setiawan', 'email' => 'budi@example.test', 'nik' => '3271012304900001', 'gender' => 'male', 'dob' => '1990-04-23', 'address' => 'Jl. Kenanga No. 12, Bandung'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti@example.test', 'nik' => '3271014508920002', 'gender' => 'female', 'dob' => '1992-08-15', 'address' => 'Jl. Mawar No. 45, Bandung'],
            ['name' => 'Rahmat Hidayat', 'email' => 'rahmat@example.test', 'nik' => '3271011211880003', 'gender' => 'male', 'dob' => '1988-11-12', 'address' => 'Jl. Melati Blok C-3, Cimahi'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@example.test', 'nik' => '3271015602950004', 'gender' => 'female', 'dob' => '1995-02-16', 'address' => 'Komplek Griya Asri No. 8, Bandung'],
            ['name' => 'Agus Pratama', 'email' => 'agus@example.test', 'nik' => '3271011907850005', 'gender' => 'male', 'dob' => '1985-07-19', 'address' => 'Jl. Anggrek No. 22, Bandung'],
            ['name' => 'Putri Ayu Wandira', 'email' => 'putri@example.test', 'nik' => '3271016812990006', 'gender' => 'female', 'dob' => '1999-12-28', 'address' => 'Jl. Flamboyan No. 10, Bandung'],
        ];

        $medications = Medication::all();

        foreach ($patientsData as $index => $pData) {
            $user = User::firstOrCreate(
                ['email' => $pData['email']],
                [
                    'user_code' => User::generateUserCode(),
                    'username' => $pData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );

            $patient = Patient::firstOrCreate(
                ['nik' => $pData['nik']],
                [
                    'user_id' => $user->user_id,
                    'patient_name' => $pData['name'],
                    'date_of_birth' => $pData['dob'],
                    'gender' => $pData['gender'],
                    'address' => $pData['address'],
                ]
            );

            // Pilih jadwal
            $schedule = $schedules[$index % $schedules->count()];

            $doctorPrefix = $schedule->doctor?->queue_prefix ?? 'A';
            $queueNumber = $doctorPrefix.'-'.str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $estimateTime = Carbon::parse($schedule->opening_hours)->addMinutes($index * 15)->format('H:i:s');

            // Tentukan status antrean bervariasi:
            // 0 & 1: Selesai diperiksa (completed + ada rekam medis & invoice)
            // 2: Sedang diperiksa di ruangan (checking)
            // 3: Sedang dipanggil (called)
            // 4 & 5: Menunggu (waiting)
            $queueStatus = 'waiting';
            if ($index === 0 || $index === 1) {
                $queueStatus = 'completed';
            } elseif ($index === 2) {
                $queueStatus = 'checking';
            } elseif ($index === 3) {
                $queueStatus = 'called';
            }

            $queue = Queue::firstOrCreate(
                [
                    'queue_number' => $queueNumber,
                    'queue_date' => $today->toDateString(),
                ],
                [
                    'doctor_id' => $schedule->doctor_id,
                    'patient_id' => $patient->patient_id,
                    'schedule_id' => $schedule->schedule_id,
                    'session' => $schedule->session,
                    'estimate_time' => $estimateTime,
                    'queue_status' => $queueStatus,
                    'time_register' => '07:45:00',
                ]
            );

            // Jika completed, buatkan Rekam Medis, Resep, dan Invoice
            if ($queueStatus === 'completed') {
                $medrec = MedicalRecord::firstOrCreate(
                    ['queue_id' => $queue->queue_id],
                    [
                        'doctor_id' => $schedule->doctor_id,
                        'patient_id' => $patient->patient_id,
                        'medrec_code' => substr($queue->queue_number.$patient->nik, 0, 26),
                        'main_complaint' => 'Demam dan batuk pilek sejak 3 hari yang lalu',
                        'blood_pressure' => '120/80',
                        'temperature' => '38.2',
                        'weight' => 65,
                        'diagnosis' => 'ISPA (Infeksi Saluran Pernafasan Akut)',
                        'notes_on_treatment' => 'Istirahat cukup, perbanyak minum air hangat, obat diminum sesudah makan.',
                    ]
                );

                // Tambah resep obat
                $medFee = 0;
                if ($medications->isNotEmpty()) {
                    $selectedMeds = $medications->random(min(2, $medications->count()));
                    foreach ($selectedMeds as $med) {
                        $qty = 10;
                        $price = $med->unit_price;
                        $prescriptionCode = 'RSP-'.strtoupper(substr(dechex(microtime(true) * 1000 + rand(10, 99)), -8));

                        $medrec->medicationPrescriptions()->firstOrCreate(
                            ['medication_id' => $med->medication_id, 'medrec_id' => $medrec->medrec_id],
                            [
                                'prescription_code' => $prescriptionCode,
                                'dosage' => '3x1 tablet sesudah makan',
                                'qty' => $qty,
                                'unit_price' => $price,
                                'total_price' => $qty * $price,
                            ]
                        );

                        $medFee += ($qty * $price);
                    }
                }

                $examFee = 50000;
                $totalAmount = $examFee + $medFee;

                Invoice::firstOrCreate(
                    ['medrec_id' => $medrec->medrec_id],
                    [
                        'invoice_code' => 'INV-'.strtoupper(substr(uniqid(), -8)),
                        'examination_fee' => $examFee,
                        'medication_fee' => $medFee,
                        'total_amount' => $totalAmount,
                        'payment_status' => 'paid',
                        'payment_method' => 'cash',
                    ]
                );
            }
        }
    }
}
