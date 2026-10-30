<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class QueueManagementController extends Controller
{
    public function index()
    {
        $todayQueues = Queue::with(['patient', 'practiceSchedule'])
            ->whereDate('queue_date', Carbon::today())
            ->orderBy('estimate_time', 'asc')
            ->paginate(10);
        return Inertia::render('Queues/Manage', ['todayQueues' => $todayQueues]);
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
