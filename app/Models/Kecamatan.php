<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';

    public $timestamps = false;

    protected $fillable = ['nama'];

    public function wisata(): HasMany
    {
        return $this->hasMany(Wisata::class, 'kecamatan_id');
    }

    public function desa(): HasMany
    {
        return $this->hasMany(Desa::class, 'kecamatan_id')->orderBy('nama');
    }
}
