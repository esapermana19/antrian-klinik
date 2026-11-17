<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        // Jika akun ber-role 'user' (pasien), arahkan langsung ke halaman Ambil Antrean
        if ($request->user() && $request->user()->role === 'user') {
            return redirect()->route('queue.index');
        }

        $today = Carbon::today()->toDateString();

        $totalPatients = Patient::count();
        $todayQueuesCount = Queue::whereDate('queue_date', $today)->count();
        $completedToday = Queue::whereDate('queue_date', $today)
            ->where('queue_status', 'completed')
            ->count();
        $waitingToday = Queue::whereDate('queue_date', $today)
            ->whereIn('queue_status', ['waiting', 'called', 'checking'])
            ->count();

        // Recent Today Queues
        $statusOrderCase = "CASE queue_status 
            WHEN 'checking' THEN 1 
            WHEN 'called' THEN 2 
            WHEN 'waiting' THEN 3 
            WHEN 'completed' THEN 4 
            ELSE 5 END";

        $recentQueues = Queue::with('patient')
            ->whereDate('queue_date', $today)
            ->orderByRaw($statusOrderCase)
            ->orderBy('queue_number', 'asc')
            ->take(6)
            ->get();

        // Low stock medications alert (menggunakan scope lowStock dinamis berbasis min_stock)
        $lowStockMedications = Medication::query()
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalPatients' => $totalPatients,
                'todayQueuesCount' => $todayQueuesCount,
                'completedToday' => $completedToday,
                'waitingToday' => $waitingToday,
            ],
            'recentQueues' => $recentQueues,
            'lowStockMedications' => $lowStockMedications,
        ]);
    }
}
