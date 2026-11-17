<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'clinic_logo' => ['nullable', 'image', 'max:2048'],
            'default_examination_fee' => ['nullable', 'numeric', 'min:0'],
            'queue_prefix' => ['nullable', 'string', 'max:10'],
        ]);

        Setting::set('clinic_name', $validated['clinic_name']);
        Setting::set('clinic_address', $validated['clinic_address']);
        Setting::set('clinic_phone', $validated['clinic_phone'] ?? '');
        Setting::set('default_examination_fee', isset($validated['default_examination_fee']) ? (string) $validated['default_examination_fee'] : '50000');
        Setting::set('queue_prefix', ! empty($validated['queue_prefix']) ? strtoupper(trim($validated['queue_prefix'])) : 'A');

        if ($request->hasFile('clinic_logo')) {
            $oldLogo = Setting::get('clinic_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('clinic_logo')->store('clinic', 'public');
            Setting::set('clinic_logo', $path);
        }

        return back()->with('success', 'Pengaturan informasi klinik berhasil disimpan.');
    }
}
