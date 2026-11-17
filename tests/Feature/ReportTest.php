<?php

use App\Models\User;

test('administrator can view reports page', function () {
    $admin = User::factory()->create(['role' => 'administrator']);

    $response = $this->actingAs($admin)->get('/reports');
    $response->assertOk();
});

test('administrator can export reports to excel with formatted table', function () {
    $admin = User::factory()->create(['role' => 'administrator']);

    $response = $this->actingAs($admin)->get('/reports/export-excel');
    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('administrator can view clean print report view', function () {
    $admin = User::factory()->create(['role' => 'administrator']);

    $response = $this->actingAs($admin)->get('/reports/print');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Reports/Print'));
});

test('regular user cannot access reports page', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/reports');
    $response->assertForbidden();
});
