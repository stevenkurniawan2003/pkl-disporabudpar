<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Ambil nilai setting berdasarkan key. Cache 1 jam untuk performa.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            return self::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Set nilai setting. Otomatis clear cache.
     */
    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    /**
     * Ambil semua data pejabat dalam satu query (untuk form edit).
     */
    public static function getPejabat(): array
    {
        $settings = self::whereIn('key', [
            'kepala_dinas_nama',
            'kepala_dinas_nip',
            'kabid_kebudayaan_nama',
            'kabid_kebudayaan_nip',
        ])->pluck('value', 'key');

        return [
            'kepala_dinas' => [
                'nama' => $settings['kepala_dinas_nama'] ?? '',
                'nip' => $settings['kepala_dinas_nip'] ?? '',
            ],
            'kabid_kebudayaan' => [
                'nama' => $settings['kabid_kebudayaan_nama'] ?? '',
                'nip' => $settings['kabid_kebudayaan_nip'] ?? '',
            ],
        ];
    }
}
