<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['queue_id', 'patient_id', 'medrec_code', 'main_complaint', 'blood_pressure', 'temperature', 'weight', 'diagnosis', 'notes_on_treatment'])]
class MedicalRecord extends Model
{
    protected $primaryKey = 'medrec_id';

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
        ];
    }

    // mengambil relasi queues
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'queue_id', 'queue_id');
    }

    // mengambil relasi patients
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    // relasi ke medication_prescriptions
    public function medicationPrescriptions(): HasMany
    {
        return $this->hasMany(MedicationPrescription::class, 'medrec_id', 'medrec_id');
    }

    // relasi ke invoices
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'medrec_id', 'medrec_id');
    }
}
