<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
