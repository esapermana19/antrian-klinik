<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search');

        $doctors = Doctor::query()
            ->when($search, function ($query, $search) {
                $query->where('doctor_name', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            })
            ->withCount(['practiceSchedules', 'queues'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Doctors/Index', [
            'doctors' => $doctors,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_name' => ['required', 'string', 'max:255'],
            'specialization' => ['required', 'string', 'max:255'],
            'queue_prefix' => ['nullable', 'string', 'max:10'],
            'str_number' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($validated['queue_prefix'])) {
            $validated['queue_prefix'] = strtoupper(trim($validated['queue_prefix']));
        }

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('doctors', 'public');
        }

        Doctor::create($validated);

        return back()->with('success', 'Dokter berhasil ditambahkan.');
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_name' => ['required', 'string', 'max:255'],
            'specialization' => ['required', 'string', 'max:255'],
            'queue_prefix' => ['nullable', 'string', 'max:10'],
            'str_number' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($validated['queue_prefix'])) {
            $validated['queue_prefix'] = strtoupper(trim($validated['queue_prefix']));
        }

        if ($request->hasFile('photo')) {
            if ($doctor->photo_path) {
                Storage::disk('public')->delete($doctor->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('doctors', 'public');
        }

        $doctor->update($validated);

        return back()->with('success', 'Data dokter berhasil diperbarui.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        if ($doctor->photo_path) {
            Storage::disk('public')->delete($doctor->photo_path);
        }

        $doctor->delete();

        return back()->with('success', 'Dokter berhasil dihapus.');
    }
}
