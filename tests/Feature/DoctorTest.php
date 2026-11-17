<?php

use App\Models\Doctor;
use App\Models\User;

test('administrator can view doctor list and create new doctor', function () {
    $admin = User::factory()->create(['role' => 'administrator']);

    $response = $this->actingAs($admin)->get('/doctor');
    $response->assertOk();

    $postResponse = $this->actingAs($admin)->post('/doctor', [
        'doctor_name' => 'dr. Budi Santoso, Sp.A',
        'specialization' => 'Spesialis Anak',
        'str_number' => '1234567890',
        'phone_number' => '08123456789',
        'is_active' => true,
    ]);

    $postResponse->assertSessionHas('success');

    expect(Doctor::where('doctor_name', 'dr. Budi Santoso, Sp.A')->exists())->toBeTrue();
});

test('administrator can update and delete doctor', function () {
    $admin = User::factory()->create(['role' => 'administrator']);
    $doctor = Doctor::create([
        'doctor_name' => 'dr. Siti Rahma',
        'specialization' => 'Dokter Umum',
        'is_active' => true,
    ]);

    $updateResponse = $this->actingAs($admin)->put("/doctor/{$doctor->doctor_id}", [
        'doctor_name' => 'dr. Siti Rahma, Sp.KK',
        'specialization' => 'Spesialis Kulit',
        'is_active' => false,
    ]);

    $updateResponse->assertSessionHas('success');
    expect($doctor->fresh()->specialization)->toBe('Spesialis Kulit');

    $deleteResponse = $this->actingAs($admin)->delete("/doctor/{$doctor->doctor_id}");
    $deleteResponse->assertSessionHas('success');
    expect(Doctor::where('doctor_id', $doctor->doctor_id)->exists())->toBeFalse();
});

test('regular user cannot access doctor management', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/doctor');
    $response->assertForbidden();
});
