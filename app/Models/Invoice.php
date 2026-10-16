<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $invoice_id
 * @property int $medrec_id
 * @property string $invoice_code
 * @property numeric $examination_fee
 * @property numeric $medication_fee
 * @property numeric $total_amount
 * @property string $payment_status
 * @property string $payment_method
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read MedicalRecord $medicalRecord
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereExaminationFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMedicationFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMedrecId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
