<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriOpk extends Model
{
    protected $table = 'galeri_opk';

    protected $primaryKey = 'id_galeri_opk';

    protected $fillable = ['id_opk', 'gambar'];

    public function opk(): BelongsTo
    {
        return $this->belongsTo(Opk::class, 'id_opk', 'id_opk');
    }

    public function getGambarUrlAttribute(): string
    {
        return asset('storage/'.$this->gambar);
    }
}
