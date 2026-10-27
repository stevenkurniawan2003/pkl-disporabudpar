<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriBerita extends Model
{
    protected $table = 'galeri_berita';

    protected $primaryKey = 'id_galeri_berita';

    protected $fillable = ['berita_id', 'gambar'];

    public function berita(): BelongsTo
    {
        return $this->belongsTo(Berita::class, 'berita_id');
    }

    public function getGambarUrlAttribute(): string
    {
        return asset('storage/'.$this->gambar);
    }
}
