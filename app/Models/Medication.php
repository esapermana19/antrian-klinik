<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $medication_id
 * @property string $medication_code
 * @property string $medication_name
 * @property int $stock
 * @property numeric $unit_price
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicationPrescription> $medicationPrescriptions
 * @property-read int|null $medication_prescriptions_count
 * @method static \Database\Factories\MedicationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication whereMedicationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication whereMedicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication whereMedicationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medication whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['medication_code', 'medication_name', 'stock', 'unit_price'])]
class Medication extends Model
{
    use HasFactory;
    protected $primaryKey = 'medication_id';

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function medicationPrescriptions(): HasMany
    {
        return $this->hasMany(MedicationPrescription::class, 'medication_id', 'medication_id');
    }
}
