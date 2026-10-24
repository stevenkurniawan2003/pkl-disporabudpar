<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriHotel extends Model
{
    protected $table = 'galeri_hotel';

    protected $primaryKey = 'id_galeri_hotel';

    protected $fillable = ['id_hotel', 'gambar'];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'id_hotel', 'id_hotel');
    }

    public function getGambarUrlAttribute(): string
    {
        return asset('storage/'.$this->gambar);
    }
}
