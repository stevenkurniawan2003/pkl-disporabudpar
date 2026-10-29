<?php

namespace App\Models;

use Database\Factories\PrestasiAtletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Data Atlet Berprestasi (Sorotan Prestasi PPO): satu baris = satu medali. */
class PrestasiAtlet extends Model
{
    /** @use HasFactory<PrestasiAtletFactory> */
    use HasFactory;

    public const MEDALI = ['Emas', 'Perak', 'Perunggu'];

    protected $table = 'prestasi_atlet';

    protected $primaryKey = 'id_prestasi';

    protected $fillable = ['nama_atlet', 'jenis_medali', 'kode_cabor', 'nomor_pertandingan', 'nama_kejuaraan', 'tahun'];

    protected function casts(): array
    {
        return ['tahun' => 'integer'];
    }

    /** Grafik jumlah atlet berprestasi tiap tahun, dipecah per jenis medali + total. */
    public static function dataGrafik(): array
    {
        $baris = static::query()->selectRaw('tahun, jenis_medali, COUNT(*) AS jumlah')->groupBy('tahun', 'jenis_medali')->toBase()->get();
        $tahun = $baris->pluck('tahun')->unique()->sort()->values();
        $warna = ['Emas' => '--seri-3', 'Perak' => '--seri-5', 'Perunggu' => '--seri-4'];
        $jumlah = fn ($t, ?string $m = null) => (int) $baris->where('tahun', $t)->when($m, fn ($c) => $c->where('jenis_medali', $m))->sum('jumlah');

        return [[
            'id' => 'grafik-prestasi',
            'judul' => 'Jumlah total atlet berprestasi tiap tahun',
            'satuan' => 'medali',
            'label' => $tahun->map(fn ($t) => (string) $t)->all(),
            'seri' => [
                ...array_map(fn ($m) => ['label' => $m, 'warna' => $warna[$m], 'data' => $tahun->map(fn ($t) => $jumlah($t, $m))->all()], self::MEDALI),
                ['label' => 'Total', 'warna' => '--seri-1', 'data' => $tahun->map(fn ($t) => $jumlah($t))->all()],
            ],
        ]];
    }

    public function getNamaCaborAttribute(): string
    {
        return Cabor::nama($this->kode_cabor);
    }
}
