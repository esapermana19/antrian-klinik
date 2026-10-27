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

test('happy path: user can lock slot and complete one-step booking for a new patient', function () {
    $user = User::factory()->create();
    $targetDate = Carbon::now()->next('Monday')->format('Y-m-d');

    $schedule = PracticeSchedule::factory()->create([
        'day' => 'monday',
        'opening_hours' => '08:00:00',
        'closing_hours' => '12:00:00',
        'max_quota' => 4,
        'status' => 'open',
    ]);

    $slotTime = '08:00:00';
    $queueNumber = 'P01';
    $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";

    // 1. User locks the slot
    $lockResponse = $this->actingAs($user)->post(route('queue.lock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ]);

    $lockResponse->assertRedirect();
    expect(Cache::get($lockKey))->toBe($user->user_id);

    // 2. User submits one-step booking
    $storeResponse = $this->actingAs($user)->post(route('queue.store'), [
        'schedule_id' => $schedule->schedule_id,
        'queue_date' => $targetDate,
        'time' => $slotTime,
        'queue_number' => $queueNumber,
        'nik' => '3201123456780001',
        'patient_name' => 'Budi Santoso',
        'date_of_birth' => '1995-05-15',
        'gender' => 'male',
    ]);

    $storeResponse->assertRedirect();
    $storeResponse->assertSessionHas('success');

    // 3. Verify Patient created
    $this->assertDatabaseHas('patients', [
        'nik' => '3201123456780001',
        'patient_name' => 'Budi Santoso',
        'gender' => 'male',
    ]);

    $patient = Patient::where('nik', '3201123456780001')->first();

    // 4. Verify Queue created with waiting status
    $queue = Queue::where('queue_number', $queueNumber)->first();
    expect($queue)->not->toBeNull();
    expect($queue->patient_id)->toBe($patient->patient_id);
    expect($queue->schedule_id)->toBe($schedule->schedule_id);
    expect($queue->queue_date->format('Y-m-d'))->toBe($targetDate);
    expect($queue->estimate_time)->toBe($slotTime);
    expect($queue->queue_status)->toBe('waiting');

    // 5. Verify cache lock is released
    expect(Cache::has($lockKey))->toBeFalse();

    // 6. Verify slot status becomes 'booked' on index page
    $indexResponse = $this->actingAs($user)->get(route('queue.index', ['date' => $targetDate]));
    $indexResponse->assertOk();

    $schedulesData = $indexResponse->viewData('page')['props']['schedulesData'];
    $firstSlot = collect($schedulesData[0]['slots'])->firstWhere('time', $slotTime);
    expect($firstSlot['status'])->toBe('booked');
});

test('patient deduplication: booking with an existing NIK reuses existing patient record', function () {
    $user = User::factory()->create();
    $existingPatient = Patient::factory()->create([
        'nik' => '3201123456789999',
        'patient_name' => 'Siti Aminah',
    ]);

    $targetDate = Carbon::now()->next('Tuesday')->format('Y-m-d');
    $schedule = PracticeSchedule::factory()->create([
        'day' => 'tuesday',
        'opening_hours' => '08:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    $slotTime = '08:00:00';
    $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";
    Cache::put($lockKey, $user->user_id, now()->addMinutes(5));

    $response = $this->actingAs($user)->post(route('queue.store'), [
        'schedule_id' => $schedule->schedule_id,
        'queue_date' => $targetDate,
        'time' => $slotTime,
        'queue_number' => 'P01',
        'nik' => '3201123456789999', // Existing NIK
        'patient_name' => 'Siti Aminah Updated',
        'date_of_birth' => '1990-01-01',
        'gender' => 'female',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Ensure no duplicate patient was created
    expect(Patient::where('nik', '3201123456789999')->count())->toBe(1);

    // Queue must be associated with the original patient_id
    $queue = Queue::where('schedule_id', $schedule->schedule_id)->first();
    expect($queue)->not->toBeNull();
    expect($queue->patient_id)->toBe($existingPatient->patient_id);
    expect($queue->queue_date->format('Y-m-d'))->toBe($targetDate);
});

test('locking conflict: user B cannot lock a slot already locked by user A', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $targetDate = Carbon::now()->next('Wednesday')->format('Y-m-d');
    $schedule = PracticeSchedule::factory()->create([
        'day' => 'wednesday',
        'opening_hours' => '09:00:00',
        'max_quota' => 3,
        'status' => 'open',
    ]);

    $slotTime = '09:15:00';
    $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";

    // User A locks slot successfully
    $this->actingAs($userA)->post(route('queue.lock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ])->assertRedirect();

    expect(Cache::get($lockKey))->toBe($userA->user_id);

    // User B tries to lock the same slot -> should be rejected
    $conflictResponse = $this->actingAs($userB)->post(route('queue.lock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ]);

    $conflictResponse->assertRedirect();
    $conflictResponse->assertSessionHasErrors(['lock']);
    expect(Cache::get($lockKey))->toBe($userA->user_id);
});

test('cancel booking: user can unlock their own slot and make it available again', function () {
    $user = User::factory()->create();
    $targetDate = Carbon::now()->next('Thursday')->format('Y-m-d');
    $schedule = PracticeSchedule::factory()->create([
        'day' => 'thursday',
        'opening_hours' => '08:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    $slotTime = '08:00:00';
    $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";
    Cache::put($lockKey, $user->user_id, now()->addMinutes(5));

    $response = $this->actingAs($user)->post(route('queue.unlock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ]);

    $response->assertRedirect();
    expect(Cache::has($lockKey))->toBeFalse();
});

test('lock hijacking prevention: user B cannot unlock or submit booking on user A slot', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $targetDate = Carbon::now()->next('Friday')->format('Y-m-d');
    $schedule = PracticeSchedule::factory()->create([
        'day' => 'friday',
        'opening_hours' => '10:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    $slotTime = '10:00:00';
    $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";
    Cache::put($lockKey, $userA->user_id, now()->addMinutes(5));

    // User B attempts to unlock User A's slot
    $this->actingAs($userB)->post(route('queue.unlock'), [
        'schedule_id' => $schedule->schedule_id,
        'date' => $targetDate,
        'time' => $slotTime,
    ]);

    // Key must still belong to User A
    expect(Cache::get($lockKey))->toBe($userA->user_id);

    // User B attempts to store/book User A's slot
    $submitResponse = $this->actingAs($userB)->post(route('queue.store'), [
        'schedule_id' => $schedule->schedule_id,
        'queue_date' => $targetDate,
        'time' => $slotTime,
        'queue_number' => 'P01',
        'nik' => '3201123456780002',
        'patient_name' => 'Penyerobot',
        'date_of_birth' => '1998-03-20',
        'gender' => 'male',
    ]);

    $submitResponse->assertRedirect();
    $submitResponse->assertSessionHasErrors(['message']);
    $this->assertDatabaseMissing('queues', [
        'estimate_time' => $slotTime,
        'queue_date' => $targetDate,
    ]);
});

test('expired lock: booking is rejected when session/cache lock has expired', function () {
    $user = User::factory()->create();
    $targetDate = Carbon::now()->next('Saturday')->format('Y-m-d');
    $schedule = PracticeSchedule::factory()->create([
        'day' => 'saturday',
        'opening_hours' => '08:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    $slotTime = '08:00:00';
    $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";

    // Lock is expired (absent from cache)
    Cache::forget($lockKey);

    $response = $this->actingAs($user)->post(route('queue.store'), [
        'schedule_id' => $schedule->schedule_id,
        'queue_date' => $targetDate,
        'time' => $slotTime,
        'queue_number' => 'P01',
        'nik' => '3201123456780003',
        'patient_name' => 'Pasien Terlambat',
        'date_of_birth' => '1994-08-10',
        'gender' => 'female',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
    $this->assertDatabaseMissing('queues', [
        'queue_date' => $targetDate,
        'estimate_time' => $slotTime,
    ]);
});

test('validation: rejected on invalid NIK, future birth date, or past queue date', function () {
    $user = User::factory()->create();
    $schedule = PracticeSchedule::factory()->create([
        'day' => 'monday',
        'status' => 'open',
    ]);

    // 1. NIK less than 16 digits
    $response = $this->actingAs($user)->post(route('queue.store'), [
        'schedule_id' => $schedule->schedule_id,
        'queue_date' => Carbon::now()->format('Y-m-d'),
        'time' => '08:00:00',
        'queue_number' => 'P01',
        'nik' => '12345', // Invalid
        'patient_name' => 'Tester',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
    ]);
    $response->assertSessionHasErrors(['nik']);

    // 2. Future date of birth
    $response = $this->actingAs($user)->post(route('queue.store'), [
        'schedule_id' => $schedule->schedule_id,
        'queue_date' => Carbon::now()->format('Y-m-d'),
        'time' => '08:00:00',
        'queue_number' => 'P01',
        'nik' => '3201123456789999',
        'patient_name' => 'Tester',
        'date_of_birth' => Carbon::tomorrow()->format('Y-m-d'), // Invalid (future)
        'gender' => 'male',
    ]);
    $response->assertSessionHasErrors(['date_of_birth']);

    // 3. Past queue date
    $response = $this->actingAs($user)->post(route('queue.store'), [
        'schedule_id' => $schedule->schedule_id,
        'queue_date' => Carbon::yesterday()->format('Y-m-d'), // Invalid (past)
        'time' => '08:00:00',
        'queue_number' => 'P01',
        'nik' => '3201123456789999',
        'patient_name' => 'Tester',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
    ]);
    $response->assertSessionHasErrors(['queue_date']);
});

test('slot generator: accurately converts date to english day name and filters active schedules', function () {
    $user = User::factory()->create();

    // Create a schedule for Monday and Tuesday
    $mondaySchedule = PracticeSchedule::factory()->create([
        'day' => 'monday',
        'session' => 'morning',
        'opening_hours' => '08:00:00',
        'closing_hours' => '09:00:00',
        'max_quota' => 2,
        'status' => 'open',
    ]);

    $tuesdaySchedule = PracticeSchedule::factory()->create([
        'day' => 'tuesday',
        'session' => 'afternoon',
        'status' => 'open',
    ]);

    $nextMonday = Carbon::now()->next('Monday')->format('Y-m-d');

    $response = $this->actingAs($user)->get(route('queue.index', ['date' => $nextMonday]));
    $response->assertOk();

    $schedulesData = $response->viewData('page')['props']['schedulesData'];

    // Only Monday schedule should be retrieved
    expect($schedulesData)->toHaveCount(1);
    expect($schedulesData[0]['schedule']['schedule_id'])->toBe($mondaySchedule->schedule_id);
    expect($schedulesData[0]['slots'])->toHaveCount(2);
    expect($schedulesData[0]['slots'][0]['time'])->toBe('08:00:00');
    expect($schedulesData[0]['slots'][1]['time'])->toBe('08:15:00');
});
