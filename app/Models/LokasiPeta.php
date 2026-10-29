<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Titik Peta Wisata yang belum punya tabel sendiri: Restoran & Rumah Makan, Pusat Oleh-oleh, dan Lainnya
 * (dokumen konsep Pariwisata poin 1 beserta sarannya).
 */
class LokasiPeta extends Model
{
    public const KATEGORI = [
        'restoran' => 'Restoran & Rumah Makan',
        'oleh-oleh' => 'Pusat Oleh-oleh',
        'lainnya' => 'Lainnya',
    ];

    protected $table = 'lokasi_peta';

    protected $primaryKey = 'id_lokasi';

    protected $fillable = ['nama', 'kategori', 'alamat', 'latitude', 'longitude', 'deskripsi_singkat', 'no_telepon', 'gambar'];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[$this->kategori] ?? $this->kategori;
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? asset('storage/'.$this->gambar) : null;
    }
}
