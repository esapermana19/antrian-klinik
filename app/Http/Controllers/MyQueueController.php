<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyQueueController extends Controller
{
    public function index(Request $request)
    {
        $myQueues = Queue::with(['patient', 'practiceSchedule'])
            ->whereHas('patient', function ($query) use ($request) {
                $query->where('user_id', $request->user()->user_id);
            })
            ->latest('queue_date')
            ->get();

        return Inertia::render('Queues/MyQueue', ['myQueues' => $myQueues]);
    }
}
