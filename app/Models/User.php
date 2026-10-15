<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_code', 'username', 'email', 'password', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    public function getForeignKey(): string
    {
        return 'user_id';
    }

    protected $primaryKey = 'user_id';

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'user_id', 'user_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->user_code)) {
                $user->user_code = static::generateUserCode();
            }
        });
    }

    public static function generateUserCode(): string
    {
        $prefix = now()->format('Ymd'); // Hasil: 20261015 (8 digit)

        // Cari user_code terakhir di tanggal yang sama
        $latestUser = static::where('user_code', 'like', "{$prefix}%")
            ->orderByDesc('user_code')
            ->first();

        $nextNumber = 1;

        if ($latestUser && $latestUser->user_code) {
            // Ambil 3 digit terakhir dan tambah 1
            $lastSequence = (int) substr($latestUser->user_code, 8);
            $nextNumber = $lastSequence + 1;
        }

        // Format jadi 3 digit dengan padding nol (001, 002, dst.)
        return sprintf('%s%03d', $prefix, $nextNumber);
    }
}
