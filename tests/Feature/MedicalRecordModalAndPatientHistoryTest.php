<?php

use App\Models\Medication;
use App\Models\Patient;
use App\Models\PracticeSchedule;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('queue management index passes medications with stock > 0', function () {
    $admin = User::factory()->create([
        'role' => 'administrator',
    ]);

    Medication::create([
        'medication_code' => 'MED-001',
        'medication_name' => 'Amoxicillin 500mg',
        'stock' => 10,
        'unit_price' => 15000,
    ]);

    Medication::create([
        'medication_code' => 'MED-002',
        'medication_name' => 'Paracetamol 500mg',
        'stock' => 0, // Habis
        'unit_price' => 5000,
    ]);

    $response = $this->actingAs($admin)->get(route('queue-management.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Queues/Manage')
        ->has('todayQueues')
        ->has('medications', 1)
        ->where('medications.0.medication_name', 'Amoxicillin 500mg')
    );
});

test('store medical record reduces medication stock, completes queue, and redirects back', function () {
    $admin = User::factory()->create([
        'role' => 'administrator',
    ]);

    $patient = Patient::create([
        'user_id' => $admin->user_id,
        'nik' => '3201012345678901',
        'patient_name' => 'Budi Santoso',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'address' => 'Jl. Merdeka No. 1',
    ]);

    $schedule = PracticeSchedule::factory()->create();

    $queue = Queue::create([
        'patient_id' => $patient->patient_id,
        'schedule_id' => $schedule->schedule_id,
        'queue_number' => 'A-01',
        'queue_date' => Carbon::today()->toDateString(),
        'time_register' => '07:30:00',
        'estimate_time' => '08:00:00',
        'session' => 'morning',
        'queue_status' => 'checking',
    ]);

    $medication = Medication::create([
        'medication_code' => 'MED-003',
        'medication_name' => 'Paracetamol 500mg',
        'stock' => 20,
        'unit_price' => 5000,
    ]);

    $payload = [
        'queue_id' => $queue->queue_id,
        'patient_id' => $patient->patient_id,
        'main_complaint' => 'Demam tinggi dan pusing sejak kemarin',
        'blood_pressure' => '120/80',
        'temperature' => '38.5',
        'weight' => 65,
        'diagnosis' => 'Febris Akut',
        'notes_on_treatment' => 'Istirahat yang cukup',
        'prescriptions' => [
            [
                'medication_id' => $medication->medication_id,
                'qty' => 2,
                'dosage' => '3x1 setelah makan',
            ],
        ],
    ];

    $response = $this->actingAs($admin)
        ->from(route('queue-management.index'))
        ->post(route('medical-records.store', $queue->queue_id), $payload);

    $response->assertRedirect(route('queue-management.index'));
    $response->assertSessionHas('success');

    // Cek status antrean
    expect($queue->fresh()->queue_status)->toBe('completed');

    // Cek pengurangan stok
    expect($medication->fresh()->stock)->toBe(18);

    // Cek MedicalRecord tersimpan dengan kode format queue_number + nik
    $this->assertDatabaseHas('medical_records', [
        'patient_id' => $patient->patient_id,
        'queue_id' => $queue->queue_id,
        'medrec_code' => 'A-013201012345678901',
        'diagnosis' => 'Febris Akut',
    ]);

    // Cek resep tersimpan
    $this->assertDatabaseHas('medication_prescriptions', [
        'medication_id' => $medication->medication_id,
        'qty' => 2,
        'unit_price' => 5000,
        'total_price' => 10000,
    ]);
});

test('store medical record rolls back and returns error when stock is insufficient', function () {
    $admin = User::factory()->create([
        'role' => 'administrator',
    ]);

    $patient = Patient::create([
        'user_id' => $admin->user_id,
        'nik' => '3201012345678902',
        'patient_name' => 'Siti Aminah',
        'date_of_birth' => '1995-05-15',
        'gender' => 'female',
        'address' => 'Jl. Kebon Sirih No. 5',
    ]);

    $schedule = PracticeSchedule::factory()->create();

    $queue = Queue::create([
        'patient_id' => $patient->patient_id,
        'schedule_id' => $schedule->schedule_id,
        'queue_number' => 'A-02',
        'queue_date' => Carbon::today()->toDateString(),
        'time_register' => '07:35:00',
        'estimate_time' => '08:15:00',
        'session' => 'morning',
        'queue_status' => 'checking',
    ]);

    $medication = Medication::create([
        'medication_code' => 'MED-004',
        'medication_name' => 'Amoxicillin 500mg',
        'stock' => 1,
        'unit_price' => 15000,
    ]);

    $payload = [
        'queue_id' => $queue->queue_id,
        'patient_id' => $patient->patient_id,
        'main_complaint' => 'Batuk dan radang',
        'diagnosis' => 'Faringitis',
        'prescriptions' => [
            [
                'medication_id' => $medication->medication_id,
                'qty' => 5, // melebihi stok (1)
                'dosage' => '3x1 sesudah makan',
            ],
        ],
    ];

    $response = $this->actingAs($admin)
        ->from(route('queue-management.index'))
        ->post(route('medical-records.store', $queue->queue_id), $payload);

    $response->assertRedirect(route('queue-management.index'));
    $response->assertSessionHasErrors(['error_transaction']);

    // Status queue tetap checking
    expect($queue->fresh()->queue_status)->toBe('checking');

    // Stok obat tidak terpotong
    expect($medication->fresh()->stock)->toBe(1);

    // MedicalRecord tidak dibuat
    $this->assertDatabaseMissing('medical_records', [
        'queue_id' => $queue->queue_id,
    ]);
});

test('patient medical records endpoint returns JSON history with relations', function () {
    $admin = User::factory()->create([
        'role' => 'administrator',
    ]);

    $patient = Patient::create([
        'user_id' => $admin->user_id,
        'nik' => '3201012345678903',
        'patient_name' => 'Joko Tarub',
        'date_of_birth' => '1985-10-20',
        'gender' => 'male',
        'address' => 'Jl. Melati',
    ]);

    $schedule = PracticeSchedule::factory()->create();

    $queue = Queue::create([
        'patient_id' => $patient->patient_id,
        'schedule_id' => $schedule->schedule_id,
        'queue_number' => 'B-01',
        'queue_date' => Carbon::today()->toDateString(),
        'time_register' => '08:00:00',
        'estimate_time' => '09:00:00',
        'session' => 'morning',
        'queue_status' => 'completed',
    ]);

    $medication = Medication::create([
        'medication_code' => 'MED-005',
        'medication_name' => 'Antasida Doen',
        'stock' => 50,
        'unit_price' => 3000,
    ]);

    $record = $patient->medicalRecords()->create([
        'queue_id' => $queue->queue_id,
        'medrec_code' => 'B-013201012345678903',
        'main_complaint' => 'Nyeri lambung',
        'blood_pressure' => '120/80',
        'temperature' => '36.8',
        'weight' => 70,
        'diagnosis' => 'Dispepsia',
        'notes_on_treatment' => 'Minum obat teratur',
    ]);

    $record->prescriptions()->create([
        'medication_id' => $medication->medication_id,
        'prescription_code' => 'RSP-TEST-01',
        'qty' => 1,
        'unit_price' => 3000,
        'total_price' => 3000,
        'dosage' => '3x1 sebelum makan',
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('patients.medical-records', $patient->patient_id));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonPath('0.medrec_code', 'B-013201012345678903');
    $response->assertJsonPath('0.prescriptions.0.medication.medication_name', 'Antasida Doen');
    $response->assertJsonPath('0.queue.queue_number', 'B-01');
});
