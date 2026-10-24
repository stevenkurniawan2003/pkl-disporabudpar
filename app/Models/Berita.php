<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Berita extends Model
{
    use SoftDeletes;

    protected $table = 'berita';

    protected $fillable = [
        'kategori_berita_id', 'judul', 'slug', 'ringkasan', 'isi', 'gambar_sampul',
        'status', 'tanggal_terbit', 'jumlah_dibaca', 'penulis_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'datetime',
            'jumlah_dibaca' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_berita_id');
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }

    /** Tampil di publik: status terbit DAN tanggal terbitnya sudah tiba (tanggal depan = terjadwal). */
    public function scopeTerbit(Builder $query): Builder
    {
        return $query->where('status', 'terbit')
            ->whereNotNull('tanggal_terbit')
            ->where('tanggal_terbit', '<=', now());
    }

    public function sedangTayang(): bool
    {
        return $this->status === 'terbit'
            && $this->tanggal_terbit !== null
            && $this->tanggal_terbit->lte(now());
    }

    public function getTerjadwalAttribute(): bool
    {
        return $this->status === 'terbit' && $this->tanggal_terbit !== null && $this->tanggal_terbit->isFuture();
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar_sampul ? asset('storage/'.$this->gambar_sampul) : null;
    }

    public function getGradienAttribute(): string
    {
        $daftar = [
            'linear-gradient(135deg,#C93A74,#F28BB0)',
            'linear-gradient(135deg,#F06A9A,#FBC4D8)',
            'linear-gradient(135deg,#D9487F,#EE9CBA)',
            'linear-gradient(135deg,#E8487F,#F7A1C0)',
        ];

        return $daftar[$this->id % count($daftar)];
    }

    public function getTanggalTeksAttribute(): string
    {
        return $this->tanggal_terbit?->locale('id')->translatedFormat('j F Y') ?? '';
    }

    /** Ringkasan dari admin; kalau kosong, cuplikan ±150 karakter dari isi tanpa tag HTML. */
    public function getRingkasanTampilAttribute(): string
    {
        return $this->ringkasan
            ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($this->isi))), 150);
    }

    /** Isi berita dipecah per paragraf (baris kosong). Tampilkan dengan e() supaya aman dari XSS. */
    public function getParagrafAttribute(): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R{2,}/', (string) $this->isi)),
            fn ($p) => $p !== ''
        ));
    }
}
