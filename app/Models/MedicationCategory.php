<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['medication_category_code', 'medication_category_name'])]
class MedicationCategory extends Model
{
    protected $primaryKey = 'medication_category_id';

    //relasi ke tabel medications
    public function medications():HasMany
    {
        return $this->hasMany(Medication::class, 'medication_category_id', 'medication_category_id');
    }
}
