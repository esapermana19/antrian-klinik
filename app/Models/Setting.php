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
        $logoPath = static::get('clinic_logo');
        $bannerPath = static::get('clinic_banner');

        return [
            'clinic_name' => static::get('clinic_name', 'KLINIK PRATAMA SEHAT'),
            'clinic_address' => static::get('clinic_address', 'Jl. Kesehatan No. 123, Kota Sejahtera'),
            'clinic_phone' => static::get('clinic_phone', '(021) 555-0123'),
            'clinic_logo' => $logoPath,
            'clinic_logo_url' => $logoPath ? asset('storage/'.$logoPath) : null,
            'clinic_banner' => $bannerPath,
            'clinic_banner_url' => $bannerPath ? asset('storage/'.$bannerPath) : asset('images/backround_display.jpg'),
            'default_examination_fee' => (float) static::get('default_examination_fee', '50000'),
            'queue_prefix' => static::get('queue_prefix', 'A'),
        ];
    }

    /**
     * Get landing CMS settings as an associative array
     *
     * @return array<string, mixed>
     */
    public static function getLandingSettings(): array
    {
        return [
            'hero_badge' => static::get('landing_hero_badge', 'Pelayanan Kesehatan Modern & Ramah'),
            'hero_title' => static::get('landing_hero_title', 'Perawatan Medis Terpercaya untuk Keluarga Anda'),
            'hero_subtitle' => static::get('landing_hero_subtitle', 'Sistem antrean cerdas terintegrasi, dokter spesialis berpengalaman, dan fasilitas klinik modern untuk kenyamanan berobat tanpa antre berlama-lama.'),
            'cta_primary_text' => static::get('landing_cta_primary_text', 'Daftar Antrean Sekarang'),
            'cta_secondary_text' => static::get('landing_cta_secondary_text', 'Lihat Jadwal Dokter'),
            'features_section_title' => static::get('landing_features_title', 'Mengapa Memilih Klinik Kami?'),
            'features_section_subtitle' => static::get('landing_features_subtitle', 'Standar pelayanan medis terbaik dengan kemudahan pemantauan antrean secara real-time.'),
            'doctors_section_title' => static::get('landing_doctors_title', 'Tim Dokter & Tenaga Medis'),
            'doctors_section_subtitle' => static::get('landing_doctors_subtitle', 'Dokter berdedikasi tinggi dengan keahlian profesional di berbagai bidang spesialisasi.'),
            'schedules_section_title' => static::get('landing_schedules_title', 'Jadwal Praktik Mingguan'),
            'schedules_section_subtitle' => static::get('landing_schedules_subtitle', 'Pilih waktu konsultasi yang sesuai dengan kenyamanan aktivitas harian Anda.'),
            'emergency_contact' => static::get('landing_emergency_contact', 'Hubungi Hotline Darurat: (021) 555-0123'),
        ];
    }
}
