<?php

namespace App\Models;

use App\Support\TeksKaya;
use Database\Factories\CagarBudayaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Objek Diduga Cagar Budaya (ODCB) dan Cagar Budaya (CB). */
class CagarBudaya extends Model
{
    /** @use HasFactory<CagarBudayaFactory> */
    use HasFactory;

    public const KATEGORI = ['Benda', 'Bangunan', 'Struktur', 'Kawasan', 'Situs'];

    public const STATUS = ['ODCB' => 'Objek Diduga Cagar Budaya', 'CB' => 'Cagar Budaya'];

    public const KONDISI = ['Terpelihara', 'Tidak Terpelihara'];

    protected $table = 'cagar_budaya';

    protected $primaryKey = 'id_cagar_budaya';

    protected $fillable = [
        'nama', 'gambar_utama', 'alamat', 'latitude', 'longitude', 'kategori', 'tahun',
        'status', 'kondisi', 'deskripsi_sejarah', 'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'tahun' => 'integer',
        ];
    }

    public function galeri(): HasMany
    {
        return $this->hasMany(GaleriCagarBudaya::class, 'id_cagar_budaya', 'id_cagar_budaya')->orderBy('id_galeri_cagar_budaya');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar_utama ? asset('storage/'.$this->gambar_utama) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getCuplikanAttribute(): string
    {
        return Str::limit(TeksKaya::polos($this->deskripsi_sejarah), 140);
    }
}
