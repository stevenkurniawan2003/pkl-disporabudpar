<?php

namespace App\Models;

use Database\Factories\EkrafFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Data Master pelaku Ekonomi Kreatif Jember.
 * ID usaha/pelaku = kode subsektor + nomor urut per subsektor, contoh "1.8-047" (Kriya).
 */
class Ekraf extends Model
{
    /** @use HasFactory<EkrafFactory> */
    use HasFactory;

    /** 17 subsektor ekonomi kreatif sesuai dokumen konsep. Kode menjadi awalan ID usaha/pelaku. */
    public const SUBSEKTOR = [
        '1.1' => 'Arsitektur',
        '1.2' => 'Desain Interior',
        '1.3' => 'Desain Komunikasi Visual',
        '1.4' => 'Desain Produk',
        '1.5' => 'Fesyen',
        '1.6' => 'Film, Animasi, & Video',
        '1.7' => 'Fotografi',
        '1.8' => 'Kriya',
        '1.9' => 'Kuliner',
        '1.10' => 'Musik',
        '1.11' => 'Aplikasi',
        '1.12' => 'Pengembang Permainan',
        '1.13' => 'Penerbitan',
        '1.14' => 'Periklanan',
        '1.15' => 'TV & Radio',
        '1.16' => 'Seni Pertunjukan',
        '1.17' => 'Seni Rupa',
    ];

    public const STATUS = ['aktif' => 'Aktif', 'nonaktif' => 'Non Aktif'];

    protected $table = 'ekraf';

    protected $primaryKey = 'id_ekraf';

    protected $fillable = [
        'nomor_ekraf', 'kode_subsektor', 'nomor_urut', 'nama_usaha', 'alamat', 'kabupaten',
        'kecamatan_id', 'desa_id', 'omzet', 'no_telepon', 'status', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'nomor_urut' => 'integer',
            'omzet' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('status', 'aktif');
    }

    /**
     * Filter pencarian publik dan menu Cetak. Nilai kosong diabaikan.
     *
     * @param  array{kecamatan?: int|string|null, desa?: int|string|null, subsektor?: string|null, status?: string|null}  $filter
     */
    public function scopeFilter(Builder $query, array $filter): void
    {
        $query
            ->when(! empty($filter['kecamatan']), fn ($q) => $q->where('kecamatan_id', (int) $filter['kecamatan']))
            ->when(! empty($filter['desa']), fn ($q) => $q->where('desa_id', (int) $filter['desa']))
            ->when(! empty($filter['subsektor']), fn ($q) => $q->where('kode_subsektor', $filter['subsektor']))
            ->when(! empty($filter['status']), fn ($q) => $q->where('status', $filter['status']));
    }

    /** Nomor urut berikutnya untuk satu subsektor (nomor terbesar + 1). */
    public static function nomorUrutBerikutnya(string $kodeSubsektor): int
    {
        return (int) static::query()->where('kode_subsektor', $kodeSubsektor)->max('nomor_urut') + 1;
    }

    public static function formatNomor(string $kodeSubsektor, int $nomorUrut): string
    {
        return sprintf('%s-%03d', $kodeSubsektor, $nomorUrut);
    }

    /**
     * Data dua grafik garis (per 17 subsektor dan per kecamatan) untuk partial grafik-garis.
     * Dipakai halaman publik Data EKRAF Jember dan halaman admin Data Master EKRAF. Dihitung di database (GROUP BY).
     *
     * @return list<array{id: string, judul: string, satuan: string, label: list<string>, seri: list<array{label: string, warna: string, data: list<int>}>}>
     */
    public static function dataGrafik(): array
    {
        $perSubsektor = static::query()->selectRaw('kode_subsektor, COUNT(*) AS jumlah')->groupBy('kode_subsektor')->pluck('jumlah', 'kode_subsektor');
        $perKecamatan = static::query()->selectRaw('kecamatan_id, COUNT(*) AS jumlah')->groupBy('kecamatan_id')->pluck('jumlah', 'kecamatan_id');
        $kecamatan = Kecamatan::orderBy('nama')->get(['id', 'nama']);
        $total = number_format((int) $perSubsektor->sum(), 0, ',', '.');

        return [
            [
                'id' => 'grafik-ekraf-subsektor',
                'judul' => "Grafik rincian data per subsektor (total {$total})",
                'satuan' => 'pelaku',
                'label' => array_map(fn ($kode) => $kode.' '.self::SUBSEKTOR[$kode], array_keys(self::SUBSEKTOR)),
                'seri' => [['label' => 'Jumlah pelaku ekraf', 'warna' => '--seri-1', 'data' => array_map(fn ($kode) => (int) ($perSubsektor[$kode] ?? 0), array_keys(self::SUBSEKTOR))]],
            ],
            [
                'id' => 'grafik-ekraf-kecamatan',
                'judul' => "Grafik rincian data per kecamatan (total {$total})",
                'satuan' => 'pelaku',
                'label' => $kecamatan->pluck('nama')->all(),
                'seri' => [['label' => 'Jumlah pelaku ekraf', 'warna' => '--seri-2', 'data' => $kecamatan->map(fn ($k) => (int) ($perKecamatan[$k->id] ?? 0))->all()]],
            ],
        ];
    }

    /** "1.8 Kriya" */
    public function getSubsektorLengkapAttribute(): string
    {
        return $this->kode_subsektor.' '.(self::SUBSEKTOR[$this->kode_subsektor] ?? '');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /** "Rp 8.000.000" */
    public function getOmzetRupiahAttribute(): string
    {
        return 'Rp '.number_format($this->omzet, 0, ',', '.');
    }
}
