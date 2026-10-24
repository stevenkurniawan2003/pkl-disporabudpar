<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganHotel extends Model
{
    protected $table = 'kunjungan_hotel';

    protected $fillable = ['hotel_id', 'tahun', 'bulan', 'domestik', 'mancanegara', 'keterangan', 'dicatat_oleh'];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'integer',
            'domestik' => 'integer',
            'mancanegara' => 'integer',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id_hotel');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /** Nama bulan memakai daftar yang sama dengan kunjungan wisata. */
    public function getNamaBulanAttribute(): string
    {
        return KunjunganWisata::BULAN[$this->bulan] ?? (string) $this->bulan;
    }

    public function getTotalAttribute(): int
    {
        return $this->domestik + $this->mancanegara;
    }
}
