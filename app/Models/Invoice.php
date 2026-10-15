<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['medrec_id', 'invoice_code', 'examination_fee', 'medication_fee', 'total_amount', 'payment_status', 'payment_method'])]
class Invoice extends Model
{
    protected $primaryKey = 'invoice_id';

    protected function casts(): array
    {
        return [
            'examination_fee' => 'decimal:2',
            'medication_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // mengambil relasi medical_records
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medrec_id', 'medrec_id');
    }
}
