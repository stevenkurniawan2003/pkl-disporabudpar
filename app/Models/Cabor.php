<?php

namespace App\Models;

use Database\Factories\CaborFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Data cabang olahraga (Pengkab/Pengcab) di Kabupaten Jember. */
class Cabor extends Model
{
    /** @use HasFactory<CaborFactory> */
    use HasFactory;

    /**
     * 50 cabang olahraga di Jember sesuai dokumen konsep PPO: kode => [nama, induk organisasi, kepanjangan].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    public const DAFTAR = [
        'aeromodeling' => ['Aeromodeling', 'AEROMODELING', '-'],
        'anggar' => ['Anggar', 'IKASI', 'Ikatan Anggar Seluruh Indonesia'],
        'arung-jeram' => ['Arung Jeram', 'FAJI', 'Federasi Arung Jeram Indonesia'],
        'atletik' => ['Atletik', 'PASI', 'Persatuan Atletik Seluruh Indonesia'],
        'basket' => ['Basket', 'PERBASI', 'Persatuan Bola Basket Seluruh Indonesia'],
        'berkuda' => ['Berkuda', 'PORDASI', 'Persatuan Olahraga Berkuda Indonesia'],
        'biliar' => ['Biliar', 'POBSI', 'Persatuan Olahraga Biliar Seluruh Indonesia'],
        'binaraga' => ['Binaraga', 'PBFI', 'Persatuan Binaraga dan Fitness Indonesia'],
        'bola-tangan' => ['Bola Tangan', 'ABTI', 'Asosiasi Bola Tangan Indonesia'],
        'bola-voli' => ['Bola Voli', 'PBVSI', 'Persatuan Bola Voli Seluruh Indonesia'],
        'bridge' => ['Bridge', 'GABSI', 'Gabungan Bridge Seluruh Indonesia'],
        'bulu-tangkis' => ['Bulu Tangkis', 'PBSI', 'Persatuan Bulu Tangkis Indonesia'],
        'catur' => ['Catur', 'PERCASI', 'Persatuan Catur Seluruh Indonesia'],
        'dancesport' => ['Dancesport', 'IODI', 'Ikatan Olahraga Dancesport Indonesia'],
        'drum-band' => ['Drum Band', 'PDBI', 'Persatuan Drum Band Indonesia'],
        'esport' => ['Esport', 'ESI', 'Esport Indonesia'],
        'futsal' => ['Futsal', 'AF', 'Asosiasi Futsal'],
        'gantole' => ['Gantole', 'GANTOLE', '-'],
        'gate-ball' => ['Gate Ball', 'PERGATSI', 'Persatuan Gateball Seluruh Indonesia'],
        'golf' => ['Golf', 'PGI', 'Persatuan Golf Indonesia'],
        'gulat' => ['Gulat', 'PGSI', 'Persatuan Gulat Seluruh Indonesia'],
        'karate' => ['Karate', 'FORKI', 'Federasi Olahraga Karate do Indonesia'],
        'kempo' => ['Kempo', 'PERKEMI', 'Persatuan Shorinji Kempo Indonesia'],
        'kickboxing' => ['Kickboxing', 'KBI', 'Kickboxing Indonesia'],
        'kurash' => ['Kurash', 'FERKUSHI', 'Federasi Kurash Indonesia'],
        'menembak' => ['Menembak', 'PERBAKIN', 'Persatuan Menembak Indonesia'],
        'mma' => ['MMA', 'IBCA MMA', 'Indonesia Beladiri Campuran Amatir'],
        'motor' => ['Motor', 'IMI', 'Ikatan Motor Indonesia'],
        'muaythai' => ['Muaythai', 'MI', 'Muaythai Indonesia'],
        'panahan' => ['Panahan', 'PERPANI', 'Persatuan Panahan Indonesia'],
        'panjat-tebing' => ['Panjat Tebing', 'FPTI', 'Federasi Panjat Tebing Indonesia'],
        'paralayang' => ['Paralayang', 'PARALAYANG', 'Paralayang Indonesia'],
        'paramotor' => ['Paramotor', 'PARAMOTOR', 'Paramotor Indonesia'],
        'pencak-silat' => ['Pencak Silat', 'IPSI', 'Ikatan Pencak Silat Indonesia'],
        'pentaque' => ['Pentaque', 'FOPI', 'Federasi Olahraga Pentaque Indonesia'],
        'renang' => ['Renang', 'AQUATIK', 'Akuatik Indonesia'],
        'sambo' => ['Sambo', 'PERSAMBO', 'Persatuan Sambo Indonesia'],
        'selam' => ['Selam', 'POSSI', 'Persatuan Olahraga Selam Seluruh Indonesia'],
        'senam' => ['Senam', 'PERSANI', 'Persatuan Senam Indonesia'],
        'sepak-takraw' => ['Sepak Takraw', 'PSTI', 'Persatuan Sepak Takraw Indonesia'],
        'sepakbola' => ['Sepakbola', 'PSSI', 'Persatuan Sepakbola Seluruh Indonesia'],
        'sepatu-roda' => ['Sepatu Roda', 'PERSEROSI', 'Persatuan Olahraga Sepatu Roda Seluruh Indonesia'],
        'sepeda' => ['Sepeda', 'ISSI', 'Ikatan Sport Sepeda Indonesia'],
        'taekwondo' => ['Taekwondo', 'TI', 'Taekwondo Indonesia'],
        'tarung-derajat' => ['Tarung Derajat', 'KODRAT', 'Keluarga Olahraga Tarung Derajat'],
        'tenis-lapangan' => ['Tenis Lapangan', 'PELTI', 'Persatuan Lawn Tenis Indonesia'],
        'tenis-meja' => ['Tenis Meja', 'PTMSI', 'Persatuan Tenis Meja Seluruh Indonesia'],
        'tinju' => ['Tinju', 'PERTINA', 'Persatuan Tinju Amatir Indonesia'],
        'triatlon' => ['Triatlon', 'FTI', 'Federasi Triatlon Indonesia'],
        'wushu' => ['Wushu', 'WI', 'Wushu Indonesia'],
    ];

    public const STATUS = ['aktif' => 'Aktif', 'nonaktif' => 'Tidak Aktif'];

    protected $table = 'cabor';

    protected $primaryKey = 'id_cabor';

    protected $fillable = [
        'kode_cabor', 'ketua_pengkab', 'alamat_sekretariat', 'kecamatan_id', 'latitude', 'longitude',
        'prestasi_terakhir', 'kontak', 'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /** Nama cabor dari kode, misalnya "pencak-silat" -> "Pencak Silat". */
    public static function nama(?string $kode): string
    {
        return self::DAFTAR[$kode][0] ?? (string) $kode;
    }

    /** Pilihan dropdown: kode => "Nama (INDUK)". */
    public static function pilihan(): array
    {
        return array_map(fn ($c) => $c[0].' ('.$c[1].')', self::DAFTAR);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('status', 'aktif');
    }

    /**
     * Jumlah atlet & pelatih aktif dihitung langsung dari tabel atlet/pelatih (selalu sinkron dengan update datanya).
     * Hasil: kolom jumlah_atlet_aktif dan jumlah_pelatih_aktif.
     */
    public function scopeDenganJumlah(Builder $query): void
    {
        $query->select('cabor.*')
            ->selectSub(Atlet::query()->selectRaw('COUNT(*)')->whereColumn('atlet.kode_cabor', 'cabor.kode_cabor')->where('atlet.status', 'aktif'), 'jumlah_atlet_aktif')
            ->selectSub(Pelatih::query()->selectRaw('COUNT(*)')->whereColumn('pelatih.kode_cabor', 'cabor.kode_cabor')->where('pelatih.status', 'aktif'), 'jumlah_pelatih_aktif');
    }

    /**
     * Grafik Database Cabor: jumlah atlet, pelatih (aktif), dan total per cabor yang sudah terdaftar.
     *
     * @return list<array<string, mixed>>
     */
    public static function dataGrafik(): array
    {
        $cabor = static::query()->denganJumlah()->get()->sortBy(fn ($c) => $c->nama_cabor)->values();

        return [[
            'id' => 'grafik-cabor',
            'judul' => 'Database Cabor: jumlah atlet dan pelatih aktif per cabor ('.$cabor->count().' cabor terdaftar)',
            'satuan' => 'orang',
            'label' => $cabor->pluck('nama_cabor')->all(),
            'seri' => [
                ['label' => 'Jumlah Atlet', 'warna' => '--seri-1', 'data' => $cabor->map(fn ($c) => (int) $c->jumlah_atlet_aktif)->all()],
                ['label' => 'Jumlah Pelatih', 'warna' => '--seri-2', 'data' => $cabor->map(fn ($c) => (int) $c->jumlah_pelatih_aktif)->all()],
                ['label' => 'Total', 'warna' => '--seri-3', 'data' => $cabor->map(fn ($c) => (int) $c->jumlah_atlet_aktif + (int) $c->jumlah_pelatih_aktif)->all()],
            ],
        ]];
    }

    /**
     * Jumlah per cabor (hanya cabor yang punya data) dipecah per nilai satu kolom, untuk grafik atlet/pelatih.
     *
     * @param  array<string, string>  $seri  nilai kolom => label garis, misalnya ['L' => 'Putra', 'P' => 'Putri']
     * @return array{label: list<string>, seri: list<array{label: string, warna: string, data: list<int>}>}
     */
    public static function rekapPerCabor(Builder $query, string $kolom, array $seri): array
    {
        $baris = $query->selectRaw("kode_cabor, {$kolom} AS nilai, COUNT(*) AS jumlah")->groupBy('kode_cabor', $kolom)->toBase()->get();
        $kode = $baris->pluck('kode_cabor')->unique()->sortBy(fn ($k) => self::nama($k))->values();
        $warna = ['--seri-1', '--seri-2', '--seri-3'];

        return [
            'label' => $kode->map(fn ($k) => self::nama($k))->all(),
            'seri' => collect($seri)->values()->map(fn ($label, $i) => [
                'label' => $label,
                'warna' => $warna[$i] ?? '--seri-1',
                'data' => $kode->map(fn ($k) => (int) $baris->where('kode_cabor', $k)->where('nilai', array_keys($seri)[$i])->sum('jumlah'))->all(),
            ])->all(),
        ];
    }

    public function getNamaCaborAttribute(): string
    {
        return self::nama($this->kode_cabor);
    }

    public function getIndukOrganisasiAttribute(): string
    {
        return self::DAFTAR[$this->kode_cabor][1] ?? '-';
    }

    public function getKepanjanganIndukAttribute(): string
    {
        return self::DAFTAR[$this->kode_cabor][2] ?? '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
