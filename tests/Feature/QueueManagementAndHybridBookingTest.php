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

// Helper function to create Queue since Queue model does not use HasFactory
function createQueue(array $attributes = []): Queue
{
    $schedule = $attributes['schedule_id'] ?? null
        ? PracticeSchedule::find($attributes['schedule_id'])
        : PracticeSchedule::factory()->create();

    $patientId = $attributes['patient_id'] ?? null;
    if (! $patientId) {
        $patient = Patient::factory()->create();
        $patientId = $patient->patient_id;
    }

    return Queue::create(array_merge([
        'patient_id' => $patientId,
        'schedule_id' => $schedule->schedule_id,
        'queue_number' => '281026-P01',
        'queue_date' => Carbon::today()->format('Y-m-d'),
        'session' => 'morning',
        'estimate_time' => '08:00:00',
        'queue_status' => 'waiting',
        'time_register' => '08:00:00',
    ], $attributes));
}

/*
|--------------------------------------------------------------------------
| 1. Role-Based Access Control (RoleMiddleware) Tests
|--------------------------------------------------------------------------
*/
describe('RoleMiddleware and Queue Management Access', function () {
    test('guest redirected to login when accessing queue management', function () {
        $response = $this->get(route('queue-management.index'));
        $response->assertRedirect(route('login'));
    });

    test('non-admin user gets 403 Forbidden when accessing queue-management index', function () {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('queue-management.index'));
        $response->assertStatus(403);
    });

    test('non-admin user gets 403 Forbidden when attempting to update queue status via queue-management', function () {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $queue = createQueue();

        $response = $this->actingAs($user)->patch(route('queue-management.status', $queue), [
            'queue_status' => 'called',
        ]);
        $response->assertStatus(403);
    });

    test('administrator user can access queue-management index (200 OK)', function () {
        $admin = User::factory()->create([
            'role' => 'administrator',
        ]);

        $response = $this->actingAs($admin)->get(route('queue-management.index'));
        $response->assertOk();
    });

    test('administrator user can update queue status to valid status cycle', function () {
        $admin = User::factory()->create([
            'role' => 'administrator',
        ]);
        $queue = createQueue([
            'queue_status' => 'waiting',
        ]);

        $response = $this->actingAs($admin)->patch(route('queue-management.status', $queue), [
            'queue_status' => 'called',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        expect($queue->fresh()->queue_status)->toBe('called');
    });

    test('status transition rejects invalid status value outside allowed enum', function () {
        $admin = User::factory()->create([
            'role' => 'administrator',
        ]);
        $queue = createQueue([
            'queue_status' => 'waiting',
        ]);

        $response = $this->actingAs($admin)->patch(route('queue-management.status', $queue), [
            'queue_status' => 'invalid_status_enum',
        ]);

        $response->assertSessionHasErrors('queue_status');
        expect($queue->fresh()->queue_status)->toBe('waiting');
    });
});

/*
|--------------------------------------------------------------------------
| 2. Hybrid Booking & IDOR Prevention Tests
|--------------------------------------------------------------------------
*/
describe('Hybrid Patient Selection & Booking Flow', function () {
    test('existing patient: booking with valid own patient_id does not require biodata and does not create new patient', function () {
        $user = User::factory()->create();
        $patient = Patient::factory()->create([
            'user_id' => $user->user_id,
        ]);

        $targetDate = Carbon::now()->next('Monday')->format('Y-m-d');
        $schedule = PracticeSchedule::factory()->create([
            'day' => 'monday',
            'opening_hours' => '08:00:00',
            'status' => 'open',
            'session' => 'morning',
        ]);
        $slotTime = '08:00:00';
        $queueNumber = Carbon::parse($targetDate)->format('dmy').'-P01';

        $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";
        Cache::put($lockKey, $user->user_id, now()->addMinutes(5));

        $initialPatientCount = Patient::count();

        // Submit booking with patient_id only (no nik, name, dob, gender, address)
        $response = $this->actingAs($user)->post(route('queue.store'), [
            'schedule_id' => $schedule->schedule_id,
            'queue_date' => $targetDate,
            'time' => $slotTime,
            'queue_number' => $queueNumber,
            'patient_id' => $patient->patient_id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        expect(Patient::count())->toBe($initialPatientCount);

        $this->assertDatabaseHas('queues', [
            'queue_number' => $queueNumber,
            'patient_id' => $patient->patient_id,
            'schedule_id' => $schedule->schedule_id,
            'queue_status' => 'waiting',
        ]);
    });

    test('IDOR prevention: booking with patient_id belonging to another user must be rejected', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Patient owned by User B
        $victimPatient = Patient::factory()->create([
            'user_id' => $userB->user_id,
        ]);

        $targetDate = Carbon::now()->next('Tuesday')->format('Y-m-d');
        $schedule = PracticeSchedule::factory()->create([
            'day' => 'tuesday',
            'opening_hours' => '08:00:00',
            'status' => 'open',
            'session' => 'morning',
        ]);
        $slotTime = '08:00:00';
        $queueNumber = Carbon::parse($targetDate)->format('dmy').'-P01';

        $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";
        Cache::put($lockKey, $userA->user_id, now()->addMinutes(5));

        // User A attempts booking with victimPatient's id and dummy biodata to see if authorization catches it
        $response = $this->actingAs($userA)->post(route('queue.store'), [
            'schedule_id' => $schedule->schedule_id,
            'queue_date' => $targetDate,
            'time' => $slotTime,
            'queue_number' => $queueNumber,
            'patient_id' => $victimPatient->patient_id,
            'nik' => '3201123456780001',
            'patient_name' => 'Dummy Name',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Dummy Address',
        ]);

        // Expect ModelNotFound (404) or Authorization rejection (403)
        expect(in_array($response->status(), [403, 404]))->toBeTrue();

        $this->assertDatabaseMissing('queues', [
            'queue_number' => $queueNumber,
            'patient_id' => $victimPatient->patient_id,
        ]);
    });

    test('new patient: booking without patient_id creates new patient record tied to auth user', function () {
        $user = User::factory()->create();
        $targetDate = Carbon::now()->next('Wednesday')->format('Y-m-d');
        $schedule = PracticeSchedule::factory()->create([
            'day' => 'wednesday',
            'opening_hours' => '08:00:00',
            'status' => 'open',
            'session' => 'morning',
        ]);
        $slotTime = '08:00:00';
        $queueNumber = Carbon::parse($targetDate)->format('dmy').'-P01';
        $nik = '3201123456789999';

        $lockKey = "lock_slot_{$schedule->schedule_id}_{$targetDate}_{$slotTime}";
        Cache::put($lockKey, $user->user_id, now()->addMinutes(5));

        $response = $this->actingAs($user)->post(route('queue.store'), [
            'schedule_id' => $schedule->schedule_id,
            'queue_date' => $targetDate,
            'time' => $slotTime,
            'queue_number' => $queueNumber,
            'nik' => $nik,
            'patient_name' => 'Pasien Baru Mandiri',
            'date_of_birth' => '1998-08-17',
            'gender' => 'female',
            'address' => 'Jl. Merdeka No. 45',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'nik' => $nik,
            'user_id' => $user->user_id,
            'patient_name' => 'Pasien Baru Mandiri',
        ]);

        $newPatient = Patient::where('nik', $nik)->first();
        expect($newPatient->user_id)->toBe($user->user_id);

        $this->assertDatabaseHas('queues', [
            'queue_number' => $queueNumber,
            'patient_id' => $newPatient->patient_id,
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| 3. Queue Number Generation & Format Tests
|--------------------------------------------------------------------------
*/
describe('Queue Number Format and Session Prefix', function () {
    test('queue number generated follows 10-char format DDMMYY-P01 for morning session and DDMMYY-S01 for afternoon session', function () {
        $user = User::factory()->create();
        $fixedDate = Carbon::create(2026, 10, 29); // 291026, Thursday
        $dateStr = $fixedDate->format('Y-m-d');
        $expectedDatePrefix = $fixedDate->format('dmy'); // '291026'

        $morningSchedule = PracticeSchedule::factory()->create([
            'day' => 'thursday',
            'opening_hours' => '08:00:00',
            'session' => 'morning',
            'max_quota' => 5,
            'status' => 'open',
        ]);

        $afternoonSchedule = PracticeSchedule::factory()->create([
            'day' => 'thursday',
            'opening_hours' => '14:00:00',
            'session' => 'afternoon',
            'max_quota' => 5,
            'status' => 'open',
        ]);

        $indexResponse = $this->actingAs($user)->get(route('queue.index', ['date' => $dateStr]));
        $indexResponse->assertOk();

        $schedulesData = $indexResponse->viewData('page')['props']['schedulesData'];

        $morningData = collect($schedulesData)->firstWhere('schedule.schedule_id', $morningSchedule->schedule_id);
        $afternoonData = collect($schedulesData)->firstWhere('schedule.schedule_id', $afternoonSchedule->schedule_id);

        // Verify morning session queue numbers
        expect($morningData['slots'][0]['queue_number'])->toBe("{$expectedDatePrefix}-P01");
        expect(strlen($morningData['slots'][0]['queue_number']))->toBe(10);
        expect($morningData['slots'][1]['queue_number'])->toBe("{$expectedDatePrefix}-P02");

        // Verify afternoon session queue numbers
        expect($afternoonData['slots'][0]['queue_number'])->toBe("{$expectedDatePrefix}-S01");
        expect(strlen($afternoonData['slots'][0]['queue_number']))->toBe(10);
        expect($afternoonData['slots'][1]['queue_number'])->toBe("{$expectedDatePrefix}-S02");
    });
});

/*
|--------------------------------------------------------------------------
| 4. Data Isolation on MyQueue Tests
|--------------------------------------------------------------------------
*/
describe('MyQueue Isolation Tests', function () {
    test('my-queues endpoint returns only queues belonging to current user patients', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $patientA = Patient::factory()->create(['user_id' => $userA->user_id]);
        $patientB = Patient::factory()->create(['user_id' => $userB->user_id]);

        $queueA = createQueue([
            'patient_id' => $patientA->patient_id,
            'queue_number' => '291026-P01',
        ]);

        $queueB = createQueue([
            'patient_id' => $patientB->patient_id,
            'queue_number' => '291026-P02',
        ]);

        $responseA = $this->actingAs($userA)->get(route('my-queue.index'));
        $responseA->assertOk();

        // MyQueueController returns view('my-queues.index', compact('myQueues'))
        $myQueuesA = $responseA->viewData('myQueues');
        $queueIdsA = collect($myQueuesA)->pluck('queue_id')->all();
        expect($queueIdsA)->toContain($queueA->queue_id);
        expect($queueIdsA)->not->toContain($queueB->queue_id);

        $responseB = $this->actingAs($userB)->get(route('my-queue.index'));
        $responseB->assertOk();

        $myQueuesB = $responseB->viewData('myQueues');
        $queueIdsB = collect($myQueuesB)->pluck('queue_id')->all();
        expect($queueIdsB)->toContain($queueB->queue_id);
        expect($queueIdsB)->not->toContain($queueA->queue_id);
    });
});
