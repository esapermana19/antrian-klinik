<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisplayQueueController extends Controller
{
    public function index(Request $request): Response
    {
        $doctorId = $request->query('doctor_id');
        $data = $this->getQueueData($doctorId);
        $doctors = Doctor::where('is_active', true)->get();

        return Inertia::render('Queues/DisplayQueue', [
            'currentQueue' => $data['currentQueue'],
            'nextQueues' => $data['nextQueues'],
            'doctors' => $doctors,
            'selectedDoctorId' => $doctorId,
        ]);
    }

    /**
     * Endpoint API JSON ringan untuk polling realtime tanpa overhead
     */
    public function data(Request $request)
    {
        $doctorId = $request->query('doctor_id');

        return response()->json($this->getQueueData($doctorId));
    }

    private function getQueueData(?string $doctorId = null): array
    {
        $today = Carbon::today();

        $currentQueue = Queue::with(['patient', 'doctor'])
            ->whereDate('queue_date', $today)
            ->when($doctorId, function ($query, $doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->whereIn('queue_status', ['called', 'checking'])
            ->orderBy('updated_at', 'desc')
            ->first();

        $nextQueuesQuery = Queue::with(['patient', 'doctor'])
            ->whereDate('queue_date', $today)
            ->when($doctorId, function ($query, $doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->where('queue_status', 'waiting')
            ->orderBy('estimate_time', 'asc');

        if ($currentQueue) {
            $nextQueuesQuery->where('estimate_time', '>=', $currentQueue->estimate_time)
                ->where('queue_id', '!=', $currentQueue->queue_id);
        }

        $nextQueues = $nextQueuesQuery->take(5)->get();

        return [
            'currentQueue' => $currentQueue,
            'nextQueues' => $nextQueues,
        ];
    }
}
