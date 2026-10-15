<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['patient_id','schedule_id','queue_number','queue_date','session','estimate_time','queue_status','time_register'])]
class Queue extends Model
{
    protected $primaryKey = 'queue_id';

    protected function casts():array
    {
        return [
            'queue_date' => 'date'
        ];
    }

    //mengambil relasi patients
    public function patient():BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id','patient_id');
    }

    //mengambil relasi practice_schedules
    public function practiceSchedule():BelongsTo
    {
        return $this->belongsTo(PracticeSchedule::class, 'schedule_id','schedule_id');
    }

    //relasi ke medical_records
    public function medicalRecord():HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'queue_id','queue_id');
    }
}
