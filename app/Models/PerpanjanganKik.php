<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pengajuan perpanjangan Kartu Induk Kesenian. Setiap pengajuan menghasilkan kartu PDF baru. */
class PerpanjanganKik extends Model
{
    protected $table = 'perpanjangan_kik';

    protected $primaryKey = 'id_perpanjangan';

    protected $fillable = ['id_kesenian', 'tanggal_pengajuan', 'berlaku_sampai', 'foto_ketua', 'dicatat_oleh'];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'berlaku_sampai' => 'date',
        ];
    }

    public function kesenian(): BelongsTo
    {
        return $this->belongsTo(Kesenian::class, 'id_kesenian', 'id_kesenian');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function getFotoKetuaUrlAttribute(): string
    {
        return asset('storage/'.$this->foto_ketua);
    }

    public function getMasihBerlakuAttribute(): bool
    {
        return $this->berlaku_sampai->endOfDay()->isFuture();
    }
}
