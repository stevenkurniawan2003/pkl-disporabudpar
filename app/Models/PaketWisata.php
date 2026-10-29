<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Paket wisata Jember terbaru beserta flyer dan contact person (saran dokumen konsep Pariwisata poin 5). */
class PaketWisata extends Model
{
    protected $table = 'paket_wisata';

    protected $primaryKey = 'id_paket';

    protected $fillable = ['nama_paket', 'flyer', 'deskripsi', 'harga', 'durasi', 'nama_cp', 'no_cp', 'tayang'];

    protected function casts(): array
    {
        return ['tayang' => 'boolean'];
    }

    public function scopeTayang(Builder $query): void
    {
        $query->where('tayang', true);
    }

    public function getFlyerUrlAttribute(): string
    {
        return asset('storage/'.$this->flyer);
    }

    /** Tautan WhatsApp ke contact person: 0812... -> https://wa.me/62812... */
    public function getTautanWaAttribute(): string
    {
        $nomor = preg_replace('/[^0-9]/', '', $this->no_cp);

        return 'https://wa.me/'.(str_starts_with($nomor, '0') ? '62'.substr($nomor, 1) : $nomor);
    }
}
