<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriWisata extends Model
{
    protected $table = 'galeri_wisata';

    protected $fillable = ['wisata_id', 'gambar', 'keterangan', 'urutan'];

    public function wisata(): BelongsTo
    {
        return $this->belongsTo(Wisata::class, 'wisata_id');
    }

    public function getGambarUrlAttribute(): string
    {
        return asset('storage/'.$this->gambar);
    }
}
