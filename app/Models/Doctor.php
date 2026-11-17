<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['doctor_name', 'specialization', 'queue_prefix', 'str_number', 'phone_number', 'photo_path', 'is_active'])]
#[Table(name: 'doctors', key: 'doctor_id')]
class Doctor extends Model
{
    use HasFactory;

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function practiceSchedules(): HasMany
    {
        return $this->hasMany(PracticeSchedule::class, 'doctor_id', 'doctor_id');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'doctor_id', 'doctor_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id', 'doctor_id');
    }
}
