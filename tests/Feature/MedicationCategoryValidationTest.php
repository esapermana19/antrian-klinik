<?php

use App\Models\MedicationCategory;
use App\Models\User;

test('it can store a medication category with up to 10 characters code', function () {
    $user = User::factory()->create([
        'role' => 'administrator',
    ]);

    $response = $this->actingAs($user)->post('/medication-category', [
        'medication_category_code' => 'ABX',
        'medication_category_name' => 'Antibiotik',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('medication_categories', [
        'medication_category_code' => 'ABX',
        'medication_category_name' => 'Antibiotik',
    ]);
});

test('it can update a medication category code with up to 10 characters', function () {
    $user = User::factory()->create([
        'role' => 'administrator',
    ]);

    $category = MedicationCategory::create([
        'medication_category_code' => 'A',
        'medication_category_name' => 'Analgesik',
    ]);

    $response = $this->actingAs($user)->put("/medication-category/{$category->medication_category_id}", [
        'medication_category_code' => 'ANAL',
        'medication_category_name' => 'Analgesik & Antipiretik',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('medication_categories', [
        'medication_category_id' => $category->medication_category_id,
        'medication_category_code' => 'ANAL',
    ]);
});

test('it fails validation when medication category code exceeds 10 characters', function () {
    $user = User::factory()->create([
        'role' => 'administrator',
    ]);

    $response = $this->actingAs($user)->post('/medication-category', [
        'medication_category_code' => 'ABCDEFGHIJK',
        'medication_category_name' => 'Kategori Panjang',
    ]);

    $response->assertSessionHasErrors(['medication_category_code']);
});
