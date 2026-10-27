<?php

use App\Models\Patient;
use App\Models\PracticeSchedule;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('auto-swap releases previous slot lock when locking a new slot', function () {
    $user = User::factory()->create();
    $targetDate = Carbon::now()->next('Monday')->format('Y-m-d');

    $schedule = PracticeSchedule::factory()->create([
        'day' => 'monday',
        'opening_hours' => '08:00:00',
        'max_quota' => 4,
        'status' => 'open',
    ]);

    $slot1Time = '08:00:00';
    $slot2Time = '08:15:00';

    $slot1LockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slot1Time}";
    $slot2LockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slot2Time}";
    $userActiveKey = "user_active_lock_{$user->user_id}";

    // 1. User locks slot 1
    $this->actingAs($user)->post(route('queue.lock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slot1Time,
    ])->assertRedirect();

    expect(Cache::get($slot1LockKey))->toBe($user->user_id);
    expect(Cache::get($userActiveKey))->toMatchArray([
        'schedule_id' => (string) $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slot1Time,
    ]);

    // 2. User locks slot 2 (Auto-Swap)
    $this->actingAs($user)->post(route('queue.lock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slot2Time,
    ])->assertRedirect();

    // Slot 1 must be forgotten/released
    expect(Cache::has($slot1LockKey))->toBeFalse();

    // Slot 2 must be locked by user
    expect(Cache::get($slot2LockKey))->toBe($user->user_id);

    // User active tracker must point to slot 2
    expect(Cache::get($userActiveKey))->toMatchArray([
        'schedule_id' => (string) $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slot2Time,
    ]);
});

test('conflict: another user cannot hijack or lock an already locked slot', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $targetDate = Carbon::now()->next('Tuesday')->format('Y-m-d');

    $schedule = PracticeSchedule::factory()->create([
        'day' => 'tuesday',
        'opening_hours' => '08:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    $slotTime = '08:00:00';
    $slotLockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";

    // User A locks slot
    $this->actingAs($userA)->post(route('queue.lock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ])->assertRedirect();

    // User B tries to lock the same slot
    $response = $this->actingAs($userB)->post(route('queue.lock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['lock']);

    // Lock ownership remains User A
    expect(Cache::get($slotLockKey))->toBe($userA->user_id);
});

test('past slot immunity: backend rejects locking a slot that is in the past', function () {
    $user = User::factory()->create();
    $today = Carbon::today()->format('Y-m-d');
    $dayName = strtolower(Carbon::today()->englishDayOfWeek);

    $schedule = PracticeSchedule::factory()->create([
        'day' => $dayName,
        'opening_hours' => '07:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    // Construct a time guaranteed to be in the past
    $pastTime = Carbon::now()->subHours(2)->format('H:i:s');
    $pastLockKey = "lock_slot_{$schedule->schedule_id}_{$today}_{$pastTime}";

    $response = $this->actingAs($user)->post(route('queue.lock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $today,
        'time' => $pastTime,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['lock']);

    // Cache must never be populated for a past slot
    expect(Cache::has($pastLockKey))->toBeFalse();
});

test('unlock cleanup: user canceling draft clears both slot lock and user active lock', function () {
    $user = User::factory()->create();
    $targetDate = Carbon::now()->next('Wednesday')->format('Y-m-d');

    $schedule = PracticeSchedule::factory()->create([
        'day' => 'wednesday',
        'opening_hours' => '08:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    $slotTime = '08:00:00';
    $slotLockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";
    $userActiveKey = "user_active_lock_{$user->user_id}";

    // Set locks directly or via route
    Cache::put($slotLockKey, $user->user_id, now()->addMinutes(5));
    Cache::put($userActiveKey, [
        'schedule_id' => (string) $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ], now()->addMinutes(5));

    $response = $this->actingAs($user)->post(route('queue.unlock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ]);

    $response->assertRedirect();

    // Both keys must be cleared
    expect(Cache::has($slotLockKey))->toBeFalse();
    expect(Cache::has($userActiveKey))->toBeFalse();
});

test('submit booking: one-step patient deduplication and full cache cleanup', function () {
    $user = User::factory()->create();
    $existingPatient = Patient::factory()->create([
        'nik' => '3201999988887777',
        'patient_name' => 'Pasien Lama',
    ]);

    $targetDate = Carbon::now()->next('Thursday')->format('Y-m-d');
    $schedule = PracticeSchedule::factory()->create([
        'day' => 'thursday',
        'opening_hours' => '08:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    $slotTime = '08:00:00';
    $slotLockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";
    $userActiveKey = "user_active_lock_{$user->user_id}";

    // User locks slot
    Cache::put($slotLockKey, $user->user_id, now()->addMinutes(5));
    Cache::put($userActiveKey, [
        'schedule_id' => (string) $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ], now()->addMinutes(5));

    $response = $this->actingAs($user)->post(route('queue.store'), [
        'schedule_id' => $schedule->schedule_id,
        'queue_date' => $targetDate,
        'time' => $slotTime,
        'queue_number' => 'P01',
        'nik' => '3201999988887777', // Existing NIK
        'patient_name' => 'Pasien Lama Edited',
        'date_of_birth' => '1992-04-12',
        'gender' => 'male',
        'address' => 'Jl. Sudirman No. 10, Jakarta',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Deduplication check: only 1 patient record
    expect(Patient::where('nik', '3201999988887777')->count())->toBe(1);

    // Queue created
    $queue = Queue::where('schedule_id', $schedule->schedule_id)->first();
    expect($queue)->not->toBeNull();
    expect($queue->patient_id)->toBe($existingPatient->patient_id);
    expect($queue->queue_status)->toBe('waiting');

    // Both cache keys must be completely removed
    expect(Cache::has($slotLockKey))->toBeFalse();
    expect(Cache::has($userActiveKey))->toBeFalse();
});
