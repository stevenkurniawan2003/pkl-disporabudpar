<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriCagarBudaya extends Model
{
    protected $table = 'galeri_cagar_budaya';

    protected $primaryKey = 'id_galeri_cagar_budaya';

    protected $fillable = ['id_cagar_budaya', 'gambar'];

    public function cagarBudaya(): BelongsTo
    {
        return $this->belongsTo(CagarBudaya::class, 'id_cagar_budaya', 'id_cagar_budaya');
    }

    public function getGambarUrlAttribute(): string
    {
        return asset('storage/'.$this->gambar);
    }
}
