<?php

namespace App\Models;

use Database\Factories\KesenianFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Data master Kartu Induk Kesenian (sanggar/komunitas/lembaga kesenian).
 * Nomor induk = kode jenis + nomor urut per jenis, contoh "2.5-048".
 */
class Kesenian extends Model
{
    /** @use HasFactory<KesenianFactory> */
    use HasFactory;

    /** Jenis kesenian sesuai dokumen konsep Kebudayaan. Kode menjadi awalan nomor induk. */
    public const JENIS = [
        '1.1' => 'Seni Musik - Musik Gendungan',
        '1.2' => 'Seni Musik - Musik Patrol',
        '1.3' => 'Seni Musik - Campursari',
        '1.4' => 'Seni Musik - Hadrah',
        '1.5' => 'Seni Musik - Kendang Kempul',
        '1.6' => 'Seni Musik - Karawitan',
        '1.7' => 'Seni Musik - Keroncong',
        '1.8' => 'Seni Musik - Mocopat/Mamaca',
        '1.9' => 'Seni Musik - Musik Glundengan',
        '2.1' => 'Seni Pertunjukan - Pedalangan',
        '2.2' => 'Seni Pertunjukan - Jaranan',
        '2.3' => "Seni Pertunjukan - Ta'butaan",
        '2.4' => 'Seni Pertunjukan - Can Macanan Kadhuk',
        '2.5' => 'Seni Pertunjukan - Reog',
        '2.6' => 'Seni Pertunjukan - Tari',
        '2.7' => 'Seni Pertunjukan - Ludruk',
        '2.8' => 'Seni Pertunjukan - Janger',
        '2.9' => 'Seni Pertunjukan - Pencak Silat',
        '2.10' => 'Seni Pertunjukan - Barongsai',
        '2.11' => 'Seni Pertunjukan - Jaran Kencak',
        '2.12' => 'Seni Pertunjukan - Kuda Lumping',
        '2.13' => 'Seni Pertunjukan - Cemeti',
        '2.14' => 'Seni Pertunjukan - Pegon',
        '3.1' => 'Seni Sastra Tradisi - Manuskrip',
    ];

    /** Kelompok jenis (angka sebelum titik pada kode), untuk kartu ringkasan di halaman publik. */
    public const KELOMPOK = [
        '1' => 'Seni Musik',
        '2' => 'Seni Pertunjukan',
        '3' => 'Seni Sastra Tradisi',
    ];

    public const STATUS = ['aktif' => 'Aktif', 'nonaktif' => 'Non Aktif'];

    protected $table = 'kesenian';

    protected $primaryKey = 'id_kesenian';

    protected $fillable = [
        'nomor_induk', 'kode_jenis', 'nomor_urut', 'nama_kesenian', 'nama_pemilik', 'alamat', 'kabupaten',
        'kecamatan_id', 'desa_id', 'jumlah_anggota', 'no_telepon', 'dokumentasi', 'status', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'nomor_urut' => 'integer',
            'jumlah_anggota' => 'integer',
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

    public function perpanjangan(): HasMany
    {
        return $this->hasMany(PerpanjanganKik::class, 'id_kesenian', 'id_kesenian')->latest('tanggal_pengajuan');
    }

    /** Perpanjangan terbaru, menentukan masa berlaku kartu saat ini. */
    public function perpanjanganTerakhir(): HasOne
    {
        return $this->hasOne(PerpanjanganKik::class, 'id_kesenian', 'id_kesenian')->latestOfMany('tanggal_pengajuan');
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('status', 'aktif');
    }

    /**
     * Filter menu Cetak (dan unduhan PDF-nya). Nilai kosong diabaikan.
     *
     * @param  array{kecamatan?: int|string|null, desa?: int|string|null, jenis?: string|null, status?: string|null, tahun?: int|string|null}  $filter
     */
    public function scopeFilterCetak(Builder $query, array $filter): void
    {
        $query
            ->when(! empty($filter['kecamatan']), fn ($q) => $q->where('kecamatan_id', (int) $filter['kecamatan']))
            ->when(! empty($filter['desa']), fn ($q) => $q->where('desa_id', (int) $filter['desa']))
            ->when(! empty($filter['jenis']), fn ($q) => $q->where('kode_jenis', $filter['jenis']))
            ->when(! empty($filter['status']), fn ($q) => $q->where('status', $filter['status']))
            ->when(! empty($filter['tahun']), fn ($q) => $q->whereYear('created_at', (int) $filter['tahun']));
    }

    /** Nomor urut berikutnya untuk satu kode jenis (nomor terbesar + 1). */
    public static function nomorUrutBerikutnya(string $kodeJenis, ?int $kecuali = null): int
    {
        return (int) static::query()
            ->where('kode_jenis', $kodeJenis)
            ->when($kecuali, fn ($q) => $q->whereKeyNot($kecuali))
            ->max('nomor_urut') + 1;
    }

    public static function formatNomorInduk(string $kodeJenis, int $nomorUrut): string
    {
        return sprintf('%s-%03d', $kodeJenis, $nomorUrut);
    }

    /** "2.5 Seni Pertunjukan - Reog" */
    public function getJenisLengkapAttribute(): string
    {
        return $this->kode_jenis.' '.(self::JENIS[$this->kode_jenis] ?? '');
    }

    public function getKelompokAttribute(): string
    {
        return self::KELOMPOK[strtok($this->kode_jenis, '.')] ?? 'Lainnya';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getDokumentasiUrlAttribute(): ?string
    {
        return $this->dokumentasi ? asset('storage/'.$this->dokumentasi) : null;
    }

    /** Alamat lengkap untuk kartu dan cetakan: alamat, desa, kecamatan, kabupaten. */
    public function getAlamatLengkapAttribute(): string
    {
        return collect([
            $this->alamat,
            $this->desa ? 'Desa '.$this->desa->nama : null,
            $this->kecamatan ? 'Kec. '.$this->kecamatan->nama : null,
            'Kab. '.$this->kabupaten,
        ])->filter()->implode(', ');
    }
}
