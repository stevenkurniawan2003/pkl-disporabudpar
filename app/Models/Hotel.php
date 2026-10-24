<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    protected $table = 'hotel';

    protected $primaryKey = 'id_hotel';

    protected $fillable = [
        'nama_hotel', 'gambar_utama', 'deskripsi_singkat', 'narasi', 'alamat',
        'latitude', 'longitude', 'link_google_maps', 'link_website', 'no_telepon',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /** Foto-foto tambahan. Foto utama tetap disimpan di kolom gambar_utama. */
    public function galeri(): HasMany
    {
        return $this->hasMany(GaleriHotel::class, 'id_hotel', 'id_hotel')->orderBy('id_galeri_hotel');
    }

    /** Jumlah tamu per bulan (domestik dan mancanegara). */
    public function kunjungan(): HasMany
    {
        return $this->hasMany(KunjunganHotel::class, 'hotel_id', 'id_hotel');
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar_utama ? asset('storage/'.$this->gambar_utama) : null;
    }
}
