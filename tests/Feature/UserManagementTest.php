<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('administrator can view users list and create new user', function () {
    $admin = User::factory()->create(['role' => 'administrator']);

    $response = $this->actingAs($admin)->get('/users');
    $response->assertOk();

    $postResponse = $this->actingAs($admin)->post('/users', [
        'username' => 'staf_klinik',
        'email' => 'staf@klinik.test',
        'role' => 'administrator',
        'password' => 'password123',
    ]);

    $postResponse->assertSessionHas('success');
    expect(User::where('email', 'staf@klinik.test')->exists())->toBeTrue();
});

test('administrator can reset password and update user', function () {
    $admin = User::factory()->create(['role' => 'administrator']);
    $user = User::factory()->create([
        'username' => 'pasien_lama',
        'role' => 'user',
    ]);

    $updateResponse = $this->actingAs($admin)->put("/users/{$user->user_id}", [
        'username' => 'pasien_baru',
        'email' => $user->email,
        'role' => 'user',
    ]);

    $updateResponse->assertSessionHas('success');
    expect($user->fresh()->username)->toBe('pasien_baru');

    $resetResponse = $this->actingAs($admin)->patch("/users/{$user->user_id}/reset-password", [
        'new_password' => 'newpassword123',
    ]);

    $resetResponse->assertSessionHas('success');
    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

test('administrator cannot delete self', function () {
    $admin = User::factory()->create(['role' => 'administrator']);

    $response = $this->actingAs($admin)->delete("/users/{$admin->user_id}");
    $response->assertSessionHasErrors('message');
    expect(User::where('user_id', $admin->user_id)->exists())->toBeTrue();
});

test('regular user cannot access user management', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/users');
    $response->assertForbidden();
});
