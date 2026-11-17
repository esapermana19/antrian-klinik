<?php

use App\Models\Doctor;
use App\Models\PracticeSchedule;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

test('admin can update clinic default queue prefix in settings', function () {
    $admin = User::factory()->create([
        'role' => 'administrator',
    ]);

    $response = $this->actingAs($admin)->patch('/settings/clinic', [
        'clinic_name' => 'Klinik Medika Pratama',
        'clinic_address' => 'Jl. Merdeka No. 10',
        'clinic_phone' => '021-998877',
        'default_examination_fee' => 75000,
        'queue_prefix' => 'KLN',
    ]);

    $response->assertSessionHasNoErrors();
    expect(Setting::get('queue_prefix'))->toBe('KLN');
});

test('queue slots use doctor specific prefix or fallback to clinic prefix', function () {
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    Setting::set('queue_prefix', 'KLN');

    $doctorWithPrefix = Doctor::create([
        'doctor_name' => 'drg. Anisa Permata',
        'specialization' => 'Dokter Gigi',
        'queue_prefix' => 'GGI',
        'is_active' => true,
    ]);

    $today = Carbon::today();
    $dayName = strtolower($today->englishDayOfWeek);

    $schedule = PracticeSchedule::create([
        'schedule_code' => 'SCH-GGI-01',
        'doctor_id' => $doctorWithPrefix->doctor_id,
        'day' => $dayName,
        'session' => 'morning',
        'opening_hours' => '08:00:00',
        'closing_hours' => '12:00:00',
        'max_quota' => 5,
        'status' => 'open',
    ]);

    $response = $this->actingAs($user)->get('/queue?date='.$today->toDateString().'&doctor_id='.$doctorWithPrefix->doctor_id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Queues/Index')
        ->has('schedulesData', 1)
        ->where('schedulesData.0.slots.0.queue_number', 'GGI-001')
        ->where('schedulesData.0.slots.1.queue_number', 'GGI-002')
    );
});
