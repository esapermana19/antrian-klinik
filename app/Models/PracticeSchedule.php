<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['schedule_code', 'day', 'session', 'opening_hours', 'closing_hours', 'max_quota', 'status'])]
class PracticeSchedule extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'max_quota' => 'integer',
        ];
    }

    protected $table = 'practice_schedules';

    protected $primaryKey = 'schedule_id';

    // relasi ke queue
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'schedule_id', 'schedule_id');
    }
}
