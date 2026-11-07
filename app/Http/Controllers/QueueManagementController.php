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
    public function index(): Response
    {
        $todayQueues = Queue::with(['patient', 'practiceSchedule'])
            ->whereDate('queue_date', Carbon::today())
            ->orderBy('estimate_time', 'asc')
            ->paginate(10);

        $medications = Medication::where('stock', '>', 0)
            ->orderBy('medication_name', 'asc')
            ->get();

        return Inertia::render('Queues/Manage', [
            'todayQueues' => $todayQueues,
            'medications' => $medications,
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
