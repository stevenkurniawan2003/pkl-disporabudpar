<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class KunjunganSitus extends Model
{
    public const KUNCI_CACHE = 'statistik_kunjungan_situs';

    protected $table = 'kunjungan_situs';

    protected $fillable = ['session_id', 'ip_address', 'user_agent', 'tanggal'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    /**
     * Jumlah pengunjung hari ini, bulan ini, dan total dalam SATU query, di-cache beberapa menit.
     *
     * @return array{hari_ini: int, bulan_ini: int, total: int}
     */
    public static function statistik(): array
    {
        return Cache::remember(self::KUNCI_CACHE, config('site.cache_pengunjung', 300), function () {
            $hariIni = now()->toDateString();
            $awalBulan = now()->startOfMonth()->toDateString();

            $baris = static::query()
                ->selectRaw('COUNT(*) AS total')
                ->selectRaw('COALESCE(SUM(CASE WHEN tanggal = ? THEN 1 ELSE 0 END), 0) AS hari_ini', [$hariIni])
                ->selectRaw('COALESCE(SUM(CASE WHEN tanggal >= ? THEN 1 ELSE 0 END), 0) AS bulan_ini', [$awalBulan])
                ->toBase()
                ->first();

            return [
                'hari_ini' => (int) $baris->hari_ini,
                'bulan_ini' => (int) $baris->bulan_ini,
                'total' => (int) $baris->total,
            ];
        });
    }
}
