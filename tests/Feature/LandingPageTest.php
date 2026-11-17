<?php

use App\Models\Doctor;
use App\Models\PracticeSchedule;
use App\Models\Setting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('landing page loads successfully with dynamic doctor and schedule data', function () {
    $doctor = Doctor::create([
        'doctor_name' => 'dr. Sarah Spesialis',
        'specialization' => 'Spesialis Kulit',
        'is_active' => true,
    ]);

    PracticeSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'schedule_code' => 'SCH-001',
        'day' => 'Senin',
        'session' => 'Pagi',
        'opening_hours' => '08:00:00',
        'closing_hours' => '12:00:00',
        'max_quota' => 20,
        'status' => 'active',
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->has('clinic')
        ->has('landing')
        ->has('doctors', 1)
        ->has('schedules', 1)
        ->where('doctors.0.doctor_name', 'dr. Sarah Spesialis')
    );
});

test('administrator can update landing cms settings', function () {
    $admin = User::factory()->create([
        'role' => 'administrator',
    ]);

    $response = $this->actingAs($admin)->patch('/settings/landing', [
        'hero_title' => 'Klinik Modern Paling Terpercaya',
        'hero_subtitle' => 'Pelayanan ramah terpadu untuk Anda.',
        'hero_badge' => 'Badge Baru',
        'cta_primary_text' => 'Ambil Antrean',
    ]);

    $response->assertSessionHas('success');
    expect(Setting::get('landing_hero_title'))->toBe('Klinik Modern Paling Terpercaya');
    expect(Setting::get('landing_hero_badge'))->toBe('Badge Baru');
});

test('non-administrator cannot update landing cms settings', function () {
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    $response = $this->actingAs($user)->patch('/settings/landing', [
        'hero_title' => 'Judul Ilegal',
        'hero_subtitle' => 'Deskripsi Ilegal',
    ]);

    $response->assertForbidden();
});
