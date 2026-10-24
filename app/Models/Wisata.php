<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wisata extends Model
{
    use SoftDeletes;

    protected $table = 'wisata';

    protected $fillable = [
        'kategori_id', 'kecamatan_id', 'nama', 'slug', 'deskripsi_singkat', 'narasi', 'alamat',
        'latitude', 'longitude', 'link_google_maps', 'gambar_utama', 'unggulan', 'status', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'unggulan' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriWisata::class, 'kategori_id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /** Foto-foto tambahan. Foto utama tetap disimpan di kolom gambar_utama. */
    public function galeri(): HasMany
    {
        return $this->hasMany(GaleriWisata::class, 'wisata_id')->orderBy('urutan')->orderBy('id');
    }

    /** Jumlah pengunjung per bulan (domestik dan mancanegara). */
    public function kunjungan(): HasMany
    {
        return $this->hasMany(KunjunganWisata::class, 'wisata_id');
    }

    /** Hanya yang berstatus "tayang" boleh muncul di halaman publik. */
    public function scopeTayang(Builder $query): Builder
    {
        return $query->where('status', 'tayang');
    }

    public function scopeUnggulan(Builder $query): Builder
    {
        return $query->where('unggulan', true);
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar_utama ? asset('storage/'.$this->gambar_utama) : null;
    }

    /** Gradien cadangan (dipakai kartu publik kalau belum ada foto). */
    public function getGradienAttribute(): string
    {
        $daftar = [
            'linear-gradient(135deg,#E8487F,#F7A1C0)',
            'linear-gradient(135deg,#C93A74,#F28BB0)',
            'linear-gradient(135deg,#F06A9A,#FBC4D8)',
        ];

        return $daftar[$this->id % count($daftar)];
    }
}
