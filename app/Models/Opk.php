<?php

namespace App\Models;

use App\Support\TeksKaya;
use Database\Factories\OpkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Objek Pemajuan Kebudayaan (OPK). */
class Opk extends Model
{
    /** @use HasFactory<OpkFactory> */
    use HasFactory;

    /** 10 objek pemajuan kebudayaan (UU No. 5 Tahun 2017), urutan sesuai dokumen konsep. */
    public const JENIS = [
        'Bahasa', 'Tradisi Lisan', 'Manuskrip', 'Seni', 'Adat Istiadat', 'Ritus',
        'Pengetahuan Tradisional', 'Teknologi Tradisional', 'Permainan Tradisional', 'Olahraga Tradisional',
    ];

    public const KONDISI = ['Baru', 'Bertahan', 'Berkembang', 'Terancam Punah', 'Punah'];

    protected $table = 'opk';

    protected $primaryKey = 'id_opk';

    protected $fillable = [
        'nama', 'gambar_utama', 'lokasi_persebaran', 'persebaran', 'jenis', 'tahun',
        'pelaku_budaya', 'kondisi', 'deskripsi', 'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return ['tahun' => 'integer'];
    }

    public function galeri(): HasMany
    {
        return $this->hasMany(GaleriOpk::class, 'id_opk', 'id_opk')->orderBy('id_galeri_opk');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar_utama ? asset('storage/'.$this->gambar_utama) : null;
    }

    public function getCuplikanAttribute(): string
    {
        return Str::limit(TeksKaya::polos($this->deskripsi), 140);
    }
}
