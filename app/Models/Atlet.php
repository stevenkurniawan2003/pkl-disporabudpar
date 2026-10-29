<?php

namespace App\Models;

use App\Models\Concerns\BiodataOlahraga;
use Database\Factories\AtletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Database Atlet (PPO). */
class Atlet extends Model
{
    use BiodataOlahraga;

    /** @use HasFactory<AtletFactory> */
    use HasFactory;

    public const STATUS_BINAAN = ['Pemula', 'Junior', 'Senior', 'Elite'];

    protected $table = 'atlet';

    protected $primaryKey = 'id_atlet';

    protected $fillable = [
        'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'kecamatan_id', 'no_hp',
        'kode_cabor', 'klub', 'prestasi_terakhir', 'status_binaan', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date'];
    }

    /** Grafik jumlah atlet per cabor (putra/putri). */
    public static function dataGrafik(): array
    {
        $rekap = Cabor::rekapPerCabor(static::query(), 'jenis_kelamin', ['L' => 'Putra', 'P' => 'Putri']);

        return [[
            'id' => 'grafik-atlet',
            'judul' => 'Jumlah atlet Kabupaten Jember per cabor (total '.number_format(static::count(), 0, ',', '.').')',
            'satuan' => 'atlet',
            ...$rekap,
        ]];
    }
}
