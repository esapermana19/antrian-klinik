<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $medrec_id
 * @property int $queue_id
 * @property int $patient_id
 * @property string $medrec_code
 * @property string $main_complaint
 * @property string $blood_pressure
 * @property string $temperature
 * @property int $weight
 * @property string $diagnosis
 * @property string $notes_on_treatment
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Invoice|null $invoice
 * @property-read Collection<int, MedicationPrescription> $medicationPrescriptions
 * @property-read int|null $medication_prescriptions_count
 * @property-read Patient $patient
 * @property-read Queue $queue
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereBloodPressure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereDiagnosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereMainComplaint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereMedrecCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereMedrecId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereNotesOnTreatment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereQueueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicalRecord whereWeight($value)
 *
 * @mixin \Eloquent
 */
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
