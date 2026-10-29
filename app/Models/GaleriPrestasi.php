<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Galeri Prestasi (Sorotan Prestasi PPO): dokumentasi foto atau video prestasi olahraga. */
class GaleriPrestasi extends Model
{
    protected $table = 'galeri_prestasi';

    protected $primaryKey = 'id_galeri_prestasi';

    protected $fillable = ['judul', 'keterangan', 'jenis', 'file', 'tahun'];

    protected function casts(): array
    {
        return ['tahun' => 'integer'];
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/'.$this->file);
    }
}
