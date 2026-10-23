<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patients\StorePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $patients = Patient::query()
            ->when($user->role !== 'administrator', function ($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })->latest()->paginate(10);

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
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
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $request->user()->patients()->create($request->validated());

        return back()->with('success', 'Data pasien berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Patient $patient): Response
    {
        if ($request->user()->role !== 'administrator' && $patient->user_id !== $request->user()->user_id) {
            abort(403, 'anda tidak memiliki kases ke pasien ini.');
        }
        // relasi queues dan medical record
        $patient->load([
            'queues.practiceSchedule',
            'medicalRecords',
        ]);

        return Inertia::render('Patients/Show', [
            'patient' => $patient,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        if ($request->user()->role !== 'administrator' && $patient->user_id !== $request->user()->user_id) {
            abort(403);
        }
        $patient->update($request->validated());

        return back()->with('success', 'Data pasien berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Patient $patient): RedirectResponse
    {
        if ($request->user()->role !== 'administrator' && $patient->user_id !== $request->user()->user_id) {
            abort(403);
        }

        if ($patient->queues()->exists() || $patient->medicalRecords()->exists()) {
            return back()->withErrors([
                'delete' => 'Pasien tidak dapat dihapus karena sudah memiliki riwayat antrean dan rekam medis',
            ]);
        }
        $patient->delete();

        return back()->with('success', 'Data pasien berhasil dihapus.');
    }
}
