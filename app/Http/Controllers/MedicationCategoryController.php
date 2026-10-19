<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicationCategories\StoreMedicationCategoryRequest;
use App\Http\Requests\MedicationCategories\UpdateMedicationCategoryRequest;
use App\Models\MedicationCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MedicationCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $medicationCategories = MedicationCategory::query()
            ->orderBy('medication_category_code', 'asc')
            ->paginate(10);

        return Inertia::render('MedicationCategories/Index', [
            'medication_categories' => $medicationCategories,
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
    public function store(StoreMedicationCategoryRequest $request): RedirectResponse
    {
        MedicationCategory::create($request->validated());

        return back()->with('success', 'Kategori obat berhasil ditambahkan.');

    }

    /**
     * Display the specified resource.
     */
    public function show(MedicationCategory $medicationCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicationCategory $medicationCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicationCategoryRequest $request, MedicationCategory $medicationCategory): RedirectResponse
    {
        $medicationCategory->update($request->validated());

        return back()->with('success', 'Kategori obat berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationCategory $medicationCategory)
    {
        if ($medicationCategory->medications()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan data obat.');
        }
        $medicationCategory->delete();

        return back()->with('success', 'Kategori obat berhasil dihapus.');
    }
}
