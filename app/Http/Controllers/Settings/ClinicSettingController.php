<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClinicSettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan klinik
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Clinic', [
            'settings' => Setting::getClinicSettings(),
        ]);
    }

    /**
     * Simpan pengaturan klinik
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_address' => ['required', 'string', 'max:500'],
            'clinic_phone' => ['nullable', 'string', 'max:50'],
        ]);

        Setting::set('clinic_name', $validated['clinic_name']);
        Setting::set('clinic_address', $validated['clinic_address']);
        Setting::set('clinic_phone', $validated['clinic_phone'] ?? '');

        return back()->with('success', 'Pengaturan informasi klinik berhasil disimpan.');
    }
}
