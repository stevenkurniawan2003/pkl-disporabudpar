<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganWisata extends Model
{
    public const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    protected $table = 'kunjungan_wisata';

    protected $fillable = ['wisata_id', 'tahun', 'bulan', 'domestik', 'mancanegara', 'keterangan', 'dicatat_oleh'];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'integer',
            'domestik' => 'integer',
            'mancanegara' => 'integer',
        ];
    }

    /** withTrashed: data kunjungan tetap menampilkan nama wisata walau wisatanya sudah dihapus (soft delete). */
    public function wisata(): BelongsTo
    {
        return $this->belongsTo(Wisata::class, 'wisata_id')->withTrashed();
    }

    /**
     * Hanya kunjungan wisata yang tampil di publik (tayang, belum dihapus),
     * supaya nama maupun angka wisata draf/dihapus tidak bocor ke halaman publik.
     */
    public function scopeTampilPublik(Builder $query): Builder
    {
        return $query->whereHas('wisata', fn ($q) => $q->withoutTrashed()->where('status', 'tayang'));
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function getNamaBulanAttribute(): string
    {
        return self::BULAN[$this->bulan] ?? (string) $this->bulan;
    }

    public function getTotalAttribute(): int
    {
        return $this->domestik + $this->mancanegara;
    }
}
