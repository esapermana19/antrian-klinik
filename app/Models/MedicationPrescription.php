<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $prescription_id
 * @property int $medrec_id
 * @property int $medication_id
 * @property string $prescription_code
 * @property numeric $unit_price
 * @property numeric $total_price
 * @property int $qty
 * @property string $dosage
 * @property-read MedicalRecord $medicalRecord
 * @property-read Medication $medication
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription whereMedicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription whereMedrecId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription wherePrescriptionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription wherePrescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicationPrescription whereUnitPrice($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['medrec_id', 'medication_id', 'prescription_code', 'qty', 'unit_price', 'total_price', 'dosage'])]
class MedicationPrescription extends Model
{
    protected $primaryKey = 'prescription_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    // mengambil relasi medical_records
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medrec_id', 'medrec_id');
    }

    // mengambil relasi medications
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'medication_id', 'medication_id');
    }
}
