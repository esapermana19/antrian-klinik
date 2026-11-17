<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingSettingController extends Controller
{
    /**
     * Show landing page settings
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Landing', [
            'landing' => Setting::getLandingSettings(),
        ]);
    }

    /**
     * Update landing page settings
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_badge' => ['nullable', 'string', 'max:150'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['required', 'string', 'max:1000'],
            'cta_primary_text' => ['nullable', 'string', 'max:100'],
            'cta_secondary_text' => ['nullable', 'string', 'max:100'],
            'features_section_title' => ['nullable', 'string', 'max:200'],
            'features_section_subtitle' => ['nullable', 'string', 'max:500'],
            'doctors_section_title' => ['nullable', 'string', 'max:200'],
            'doctors_section_subtitle' => ['nullable', 'string', 'max:500'],
            'schedules_section_title' => ['nullable', 'string', 'max:200'],
            'schedules_section_subtitle' => ['nullable', 'string', 'max:500'],
            'emergency_contact' => ['nullable', 'string', 'max:200'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set('landing_'.$key, $value ?? '');
        }

        return back()->with('success', 'Pengaturan landing page berhasil disimpan.');
    }
}
