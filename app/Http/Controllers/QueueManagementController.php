<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $selectedDate = $request->query('date', Carbon::today()->toDateString());

        $todayQueues = Queue::with(['patient', 'practiceSchedule'])
            ->whereDate('queue_date', $selectedDate)
            ->orderBy('estimate_time', 'asc')
            ->paginate(10)
            ->withQueryString();

        $medications = Medication::where('stock', '>', 0)
            ->orderBy('medication_name', 'asc')
            ->get();

        return Inertia::render('Queues/Manage', [
            'todayQueues' => $todayQueues,
            'medications' => $medications,
            'filters' => [
                'date' => $selectedDate,
            ],
        ]);
    }

    public function updateStatus(Request $request, Queue $queue): RedirectResponse
    {
        $validated = $request->validate([
            'queue_status' => ['required', 'in:waiting,called,checking,completed,cancel'],
        ]);
        $queue->update([
            'queue_status' => $validated['queue_status'],
        ]);

        return back()->with('success', 'Status antrean berhasil diperbarui.');
    }
}
