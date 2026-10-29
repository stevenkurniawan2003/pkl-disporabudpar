<?php

namespace App\Models;

use App\Models\Concerns\BiodataOlahraga;
use Database\Factories\PelatihFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Database Pelatih (PPO). */
class Pelatih extends Model
{
    use BiodataOlahraga;

    /** @use HasFactory<PelatihFactory> */
    use HasFactory;

    protected $table = 'pelatih';

    protected $primaryKey = 'id_pelatih';

    protected $fillable = [
        'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'kecamatan_id', 'no_hp',
        'kode_cabor', 'punya_lisensi', 'nomor_lisensi', 'file_lisensi', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'punya_lisensi' => 'boolean',
        ];
    }

    /** Grafik jumlah pelatih per cabor (punya/tidak punya sertifikat/lisensi). */
    public static function dataGrafik(): array
    {
        $rekap = Cabor::rekapPerCabor(static::query(), 'punya_lisensi', [1 => 'Punya sertifikat/lisensi', 0 => 'Tidak punya']);

        return [[
            'id' => 'grafik-pelatih',
            'judul' => 'Jumlah pelatih Kabupaten Jember per cabor (total '.number_format(static::count(), 0, ',', '.').')',
            'satuan' => 'pelatih',
            ...$rekap,
        ]];
    }

    public function getFileLisensiUrlAttribute(): ?string
    {
        return $this->file_lisensi ? asset('storage/'.$this->file_lisensi) : null;
    }

    public function getLisensiLabelAttribute(): string
    {
        return $this->punya_lisensi ? 'Punya'.($this->nomor_lisensi ? ' ('.$this->nomor_lisensi.')' : '') : 'Tidak Punya';
    }
}
