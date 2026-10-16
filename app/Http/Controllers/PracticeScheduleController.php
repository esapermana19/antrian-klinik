<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePracticeScheduleRequest;
use App\Http\Requests\UpdatePracticeScheduleRequest;
use App\Models\PracticeSchedule;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PracticeScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $schedules = PracticeSchedule::query()
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('session')
            ->paginate(10);

        return Inertia::render('PracticeSchedules/Index', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePracticeScheduleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        // ambil 3 huruf day
        $dayCode = strtoupper(substr($data['day'], 0, 3));
        // kode sesi
        $sessionNumber = $data['session'] === 'morning' ? '1' : '2';
        // prefix dasar schedule_code
        $prefix = "{$dayCode}-{$sessionNumber}-S";
        // cari no urut terakhir didatabase
        $lastSchedule = PracticeSchedule::where('schedule_code', 'LIKE', "{$prefix}%")
            ->orderBy('schedule_code', 'desc')
            ->first();
        // no urut selanjutnya
        $nextNumber = 1;
        if ($lastSchedule) {
            $lastNumber = (int) substr($lastSchedule->schedule_code, -2);
            $nextNumber = $lastNumber + 1;
        }
        // gabungan
        $data['schedule_code'] = $prefix.str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);

        PracticeSchedule::create($data);

        return back()->with('success', 'Jadwal praktik berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PracticeSchedule $practiceSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PracticeSchedule $practiceSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePracticeScheduleRequest $request, PracticeSchedule $practiceSchedule): RedirectResponse
    {
        $practiceSchedule->update($request->validated());

        return back()->with('success', 'Jadwal praktik berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PracticeSchedule $practiceSchedule): RedirectResponse
    {
        // proteksi cek jadwal sudah digunakan atau tidak
        if ($practiceSchedule->queues()->exists()) {
            return back()->with('error', 'Jadwal tidak dapat dihapus karena sudah memiliki data antrian.');
        }
        $practiceSchedule->delete();

        return back()->with('success', 'Jadwal praktik berhasil dihapus.');
    }
}
