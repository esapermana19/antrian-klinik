<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $queue_id
 * @property int $patient_id
 * @property int $schedule_id
 * @property string $queue_number
 * @property CarbonImmutable $queue_date
 * @property string $session
 * @property string $estimate_time
 * @property string $queue_status
 * @property string $time_register
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read MedicalRecord|null $medicalRecord
 * @property-read Patient $patient
 * @property-read PracticeSchedule $practiceSchedule
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereEstimateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereQueueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereQueueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereQueueNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereQueueStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereTimeRegister($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Queue whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['patient_id', 'schedule_id', 'queue_number', 'queue_date', 'session', 'estimate_time', 'queue_status', 'time_register'])]
class Queue extends Model
{
    protected $primaryKey = 'queue_id';

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
        ];
    }

    // mengambil relasi patients
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    // mengambil relasi practice_schedules
    public function practiceSchedule(): BelongsTo
    {
        return $this->belongsTo(PracticeSchedule::class, 'schedule_id', 'schedule_id');
    }

    // relasi ke medical_records
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'queue_id', 'queue_id');
    }
}
