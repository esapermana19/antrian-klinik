<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'nik', 'patient_name', 'date_of_birth', 'gender'])]
class Patient extends Model
{
    protected $primaryKey = 'patient_id';

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    // mengambil relasi dari user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // relasi ke tabel queue
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'patient_id', 'patient_id');
    }

    // relasi ke tabel medical_records
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id', 'patient_id');
    }
}
