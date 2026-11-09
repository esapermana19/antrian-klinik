<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisplayQueueController extends Controller
{
    public function index(): Response
    {
        $data = $this->getQueueData();

        return Inertia::render('Queues/DisplayQueue', [
            'currentQueue' => $data['currentQueue'],
            'nextQueues' => $data['nextQueues'],
        ]);
    }

    /**
     * Endpoint API JSON ringan untuk polling realtime tanpa overhead
     */
    public function data()
    {
        return response()->json($this->getQueueData());
    }

    private function getQueueData(): array
    {
        $today = Carbon::today();

        $currentQueue = Queue::with('patient')
            ->whereDate('queue_date', $today)
            ->whereIn('queue_status', ['called', 'checking'])
            ->orderBy('updated_at', 'desc')
            ->first();

        $nextQueuesQuery = Queue::with('patient')
            ->whereDate('queue_date', $today)
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
