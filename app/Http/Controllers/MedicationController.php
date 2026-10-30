<?php

namespace App\Http\Controllers;

use App\Http\Requests\Medications\StoreMedicationRequest;
use App\Http\Requests\Medications\UpdateMedicationRequest;
use App\Models\Medication;
use App\Models\MedicationCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $medication = Medication::query()
            ->with('medicationCategory')
            ->orderBy(MedicationCategory::select('medication_category_name')
                ->where('medication_categories.medication_category_id', 'medications.medication_category_id'), 'asc')
            ->paginate(10);

        $medicationCategory = MedicationCategory::select('medication_category_id', 'medication_category_name')
            ->orderBy('medication_category_name', 'asc')
            ->get();

        return Inertia::render('Medications/Index', [
            'medications' => $medication,
            'medication_categories' => $medicationCategory,
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
    public function store(StoreMedicationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $categoryCode = $data['medication_category_id'];
        $prefix = "MED-{$categoryCode}-";
        $lastCode = Medication::where('medication_code', 'LIKE', "$prefix%")
            ->orderBy('medication_code', 'desc')
            ->first();
        $nextNumber = 1;
        if ($lastCode) {
            $lastNumber = (int) substr($lastCode->medication_code, -4);
            $nextNumber = $lastNumber + 1;
        }
        $data['medication_code'] = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        Medication::create($data);

        return back()->with('success', 'Data obat berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Medication $medication)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Medication $medication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicationRequest $request, Medication $medication): RedirectResponse
    {
        $medication->update($request->validated());

        return back()->with('success', 'Data obat berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medication $medication): RedirectResponse
    {
        if ($medication->medicationPrescriptions()->exists()) {
            return back()->with('error', 'Data obat tidak dapat di hapus karena sudah memiliki data resep.');
        }
        $medication->delete();

        return back()->with('success', 'Data obat berhasil dihapus');
    }
}
