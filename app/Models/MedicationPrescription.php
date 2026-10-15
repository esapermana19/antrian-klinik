<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['medrec_id', 'medication_id', 'prescription_code', 'qty', 'unit_price', 'total_price', 'dosage'])]
class MedicationPrescription extends Model
{
    protected $primaryKey = 'prescription_id';
    public $timestamps = false;

    protected function casts():array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2'
        ];
    }

    //mengambil relasi medical_records
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medrec_id','medrec_id');
    }

    //mengambil relasi medications
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'medication_id','medication_id');
    }
}
