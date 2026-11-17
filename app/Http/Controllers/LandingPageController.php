<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PracticeSchedule;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    /**
     * Display clinic landing page
     */
    public function index(): Response
    {
        $dayOrderCase = "CASE LOWER(day) 
            WHEN 'senin' THEN 1 
            WHEN 'monday' THEN 1 
            WHEN 'selasa' THEN 2 
            WHEN 'tuesday' THEN 2 
            WHEN 'rabu' THEN 3 
            WHEN 'wednesday' THEN 3 
            WHEN 'kamis' THEN 4 
            WHEN 'thursday' THEN 4 
            WHEN 'jumat' THEN 5 
            WHEN 'friday' THEN 5 
            WHEN 'sabtu' THEN 6 
            WHEN 'saturday' THEN 6 
            WHEN 'minggu' THEN 7 
            WHEN 'sunday' THEN 7 
            ELSE 8 END";

        $doctors = Doctor::query()
            ->where('is_active', true)
            ->with(['practiceSchedules' => function ($query) use ($dayOrderCase) {
                $query->whereIn('status', ['open', 'active'])
                    ->orderByRaw($dayOrderCase)
                    ->orderBy('opening_hours');
            }])
            ->orderBy('doctor_name')
            ->get();

        $allSchedules = PracticeSchedule::query()
            ->whereIn('status', ['open', 'active'])
            ->with('doctor')
            ->orderByRaw($dayOrderCase)
            ->orderBy('opening_hours')
            ->get();

        $specialties = Doctor::query()
            ->where('is_active', true)
            ->pluck('specialization')
            ->unique()
            ->values();

        $stats = [
            'total_patients' => Patient::count(),
            'total_doctors' => $doctors->count(),
            'total_specialties' => $specialties->count(),
            'total_schedules' => $allSchedules->count(),
        ];

        return Inertia::render('Welcome', [
            'clinic' => Setting::getClinicSettings(),
            'landing' => Setting::getLandingSettings(),
            'doctors' => $doctors,
            'schedules' => $allSchedules,
            'specialties' => $specialties,
            'stats' => $stats,
        ]);
    }
}
