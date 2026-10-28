<?php

namespace App\Http\Controllers;

use App\Http\Requests\Queues\StoreQueueRequest;
use App\Models\Patient;
use App\Models\PracticeSchedule;
use App\Models\Queue;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        // 1. Ambil tanggal (default today)
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));

        // Konversi tanggal menjadi nama hari b. inggris
        $dayName = strtolower(Carbon::parse($selectedDate)->englishDayOfWeek);

        // 2. Ambil jadwal dokter untuk hari yang di pilih
        $schedules = PracticeSchedule::where('day', $dayName)->where('status', 'open')->get();

        // 3. Ambil data antrean yang sudah booked pada tanggal tersebut
        $bookedQueues = Queue::whereDate('queue_date', $selectedDate)->get()->keyBy(function ($queue) {
            return $queue->schedule_id . '_' . Carbon::parse($queue->estimate_time)->format('H:i:s');
        });

        // 4. data pasien dalam modal
        $myPatients = $user->patients()
            ->select(['patient_id', 'nik', 'patient_name', 'date_of_birth', 'gender', 'address'])
            ->get();

        // 5. Generate kartu antrian
        $generateSchedules = [];
        foreach ($schedules as $schedule) {
            $openingTime = Carbon::parse($schedule->opening_hours);
            $slots = [];

            // looping sebanyak kuota
            for ($i = 0; $i < $schedule->max_quota; $i++) {
                $time = $openingTime->copy()->addMinute($i * 15)->format('H:i:s');
                $datePrefix = Carbon::parse($selectedDate)->format('dmy');
                $sessionPrefix = $schedule->session === 'morning' ? 'P' : 'S';
                $queueNumber = $datePrefix . '-' . $sessionPrefix . str_pad($i + 1, 2, 0, STR_PAD_LEFT);

                // cache locking
                $lockKey = "lock_slot_{$schedule->schedule_id}_{$selectedDate}_{$time}";

                // filter jam sudah lewat
                $slotDateTime = Carbon::parse($selectedDate . ' ' . $time);

                // penentuan status kartu
                $status = 'available';
                if ($bookedQueues->has("{$schedule->schedule_id}_{$time}")) {
                    $status = 'booked';
                } elseif ($slotDateTime->isPast()) {
                    $status = 'passed';
                } elseif (Cache::has($lockKey)) {
                    $lockedBy = Cache::get($lockKey);
                    if ($lockedBy === $user->user_id) {
                        $status = 'locked_by_me';
                    } else {
                        $status = 'locked';
                    }
                }

                $slots[] = [
                    'time' => $time,
                    'queue_number' => $queueNumber,
                    'status' => $status,
                    'queue_status' => $status,
                ];
            }
            $generateSchedules[] = [
                'schedule' => $schedule,
                'schedules' => $schedule,
                'slots' => $slots,
            ];
        }

        return Inertia::render('Queues/Index', [
            'date' => $selectedDate,
            'schedulesData' => $generateSchedules,
            'patients' => $myPatients,
        ]);
    }

    /**
     * Endpoit API untuk mengunci slot sementara
     */
    public function lockSlot(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required',
            'date' => 'required|date',
            'time' => 'required',
        ]);

        $slotDateTime = Carbon::parse("{$request->date} {$request->time}");
        if ($slotDateTime->isPast()) {
            return back()->withErrors(['lock' => 'Slot waktu ini sudah terlewat dan tidak dapat dibooking.']);
        }

        $alreadyBooked = Queue::where('schedule_id', $request->schedule_id)
            ->whereDate('queue_date', $request->date)
            ->where('estimate_time', $request->time)
            ->exists();
        if ($alreadyBooked) {
            return back()->withErrors(['lock' => 'Slot antrean ini sudah dipesan orang lain.']);
        }

        $user = $request->user();
        $newLockKey = "lock_slot_{$request->schedule_id}_{$request->date}_{$request->time}";
        $userLockKey = "user_active_lock_{$user->user_id}";

        // cek apakah sudah di lock orang lain?
        if (Cache::has($newLockKey) && Cache::get($newLockKey) !== $user->user_id) {
            return back()->withErrors(['lock' => 'Antrian ini baru saja diambil orang lain']);
        }

        // melepas lock
        if (Cache::has($userLockKey)) {
            $previousLock = Cache::get($userLockKey);
            $previousLockKey = "lock_slot_{$previousLock['schedule_id']}_{$previousLock['date']}_{$previousLock['time']}";
            Cache::forget($previousLockKey);
        }

        // simpan lock
        Cache::put($newLockKey, $user->user_id, now()->addMinute(5));

        // catat slot aktif milik user ini
        Cache::put($userLockKey, [
            'schedule_id' => $request->schedule_id,
            'date' => $request->date,
            'time' => $request->time,
        ], now()->addMinute(5));

        return back();
    }

    /**
     * Endpoit API untuk melepas kunci slot
     */
    public function unlockSlot(Request $request)
    {
        $user = $request->user();
        $userLockKey = "user_active_lock_{$user->user_id}";

        // hanya di lepas jika yang mengunci diri sendiri
        if (Cache::has($userLockKey)) {
            $currentLock = Cache::get($userLockKey);
            Cache::forget("lock_slot_{$currentLock['schedule_id']}_{$currentLock['date']}_{$currentLock['time']}");
            Cache::forget($userLockKey);
        }

        return back();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQueueRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $schedule = PracticeSchedule::findOrFail($request->schedule_id);

            // 1. Validasi kunci slot
            $lockKey = "lock_slot_{$request->schedule_id}_{$request->queue_date}_{$request->time}";
            if (Cache::get($lockKey) !== $request->user()->user_id) {
                return back()->withErrors(['message' => 'Sesi booking Anda telah habis. Silakan pilih slot lagi.']);
            }

            // 2. ONE-STEP PATIENT CREATION/PILIH PASIEN
            if ($request->filled('patient_id')) {
                $patient = $user->patients()->findOrFail($request->patient_id);
            } else {
                $patient = Patient::firstOrCreate(
                    ['nik' => $request->nik, 'user_id' => $request->user()->user_id],
                    [
                        'user_id' => $request->user()->user_id,
                        'patient_name' => $request->patient_name,
                        'date_of_birth' => $request->date_of_birth,
                        'gender' => $request->gender,
                        'address' => $request->address,
                    ]
                );
            }


            // 3. Simpan Antrean
            Queue::create([
                'patient_id' => $patient->patient_id,
                'schedule_id' => $schedule->schedule_id,
                'queue_number' => $request->queue_number,
                'queue_date' => $request->queue_date,
                'session' => $schedule->session,
                'estimate_time' => $request->time,
                'queue_status' => 'waiting',
                'time_register' => Carbon::now()->format('H:i:s'),
            ]);

            Cache::forget($lockKey);
            Cache::forget("user_active_lock_{$request->user()->user_id}");

            return back()->with('success', "Berhasil mengambil antrean nomor $request->queue_number");
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Queue $queue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Queue $queue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Queue $queue)
    {
        //
    }
}
