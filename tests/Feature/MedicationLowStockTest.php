<?php

use App\Models\Medication;
use App\Models\MedicationCategory;
use App\Models\User;

test('admin can see medications and filter by low stock', function () {
    $admin = User::factory()->create([
        'role' => 'administrator',
    ]);

    $category = MedicationCategory::create([
        'medication_category_code' => 'A',
        'medication_category_name' => 'Antibiotik',
    ]);

    $medSafe = Medication::create([
        'medication_category_id' => $category->medication_category_id,
        'medication_code' => 'MED-A-001',
        'medication_name' => 'Paracetamol Safe',
        'stock' => 50,
        'min_stock' => 10,
        'unit_price' => 5000,
    ]);

    $medLow = Medication::create([
        'medication_category_id' => $category->medication_category_id,
        'medication_code' => 'MED-A-002',
        'medication_name' => 'Amoxicillin Menipis',
        'stock' => 5,
        'min_stock' => 10,
        'unit_price' => 12000,
    ]);

    $response = $this->actingAs($admin)->get('/medication?low_stock=1');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Medications/Index')
        ->where('filters.low_stock', true)
        ->has('medications.data', 1)
        ->where('medications.data.0.medication_name', 'Amoxicillin Menipis')
    );
});

test('admin can store and update medication with min_stock threshold', function () {
    $admin = User::factory()->create([
        'role' => 'administrator',
    ]);

    $category = MedicationCategory::create([
        'medication_category_code' => 'A',
        'medication_category_name' => 'Antibiotik',
    ]);

    $response = $this->actingAs($admin)->post('/medication', [
        'medication_category_id' => $category->medication_category_id,
        'medication_name' => 'Vitamin C 500mg',
        'stock' => 20,
        'min_stock' => 15,
        'unit_price' => 3000,
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('medications', [
        'medication_name' => 'Vitamin C 500mg',
        'stock' => 20,
        'min_stock' => 15,
    ]);
});
