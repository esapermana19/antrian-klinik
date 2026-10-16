<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $patient_id
 * @property int $user_id
 * @property string $nik
 * @property string $patient_name
 * @property CarbonImmutable $date_of_birth
 * @property string $gender
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, MedicalRecord> $medicalRecords
 * @property-read int|null $medical_records_count
 * @property-read Collection<int, Queue> $queues
 * @property-read int|null $queues_count
 * @property-read User $user
 *
 * @method static \Database\Factories\PatientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient wherePatientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['user_id', 'nik', 'patient_name', 'date_of_birth', 'gender'])]
class Patient extends Model
{
    use HasFactory;

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
