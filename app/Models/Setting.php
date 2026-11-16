<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with optional default
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting && $setting->value !== null ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, ?string $value): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all clinic settings as an associative array
     *
     * @return array<string, string>
     */
    public static function getClinicSettings(): array
    {
        return [
            'clinic_name' => static::get('clinic_name', 'KLINIK PRATAMA SEHAT'),
            'clinic_address' => static::get('clinic_address', 'Jl. Kesehatan No. 123, Kota Sejahtera'),
            'clinic_phone' => static::get('clinic_phone', '(021) 555-0123'),
        ];
    }
}
