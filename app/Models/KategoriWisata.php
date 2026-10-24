<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriWisata extends Model
{
    protected $table = 'kategori_wisata';

    public $timestamps = false;

    protected $fillable = ['nama', 'slug'];

    public function wisata(): HasMany
    {
        return $this->hasMany(Wisata::class, 'kategori_id');
    }

    /** Ikon kecil untuk kartu tanpa foto. */
    public function getIkonAttribute(): string
    {
        return match ($this->slug) {
            'pantai' => '🏖',
            'pegunungan' => '⛰',
            'agro' => '🌱',
            'museum' => '🏛',
            'desa-wisata' => '🏡',
            'kuliner' => '🍜',
            default => '📍',
        };
    }
}
