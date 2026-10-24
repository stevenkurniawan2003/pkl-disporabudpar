<?php

namespace App\Models;

use App\Models\Concerns\TampilanTanpaGambar;
use Illuminate\Database\Eloquent\Model;

class Kesenian extends Model
{
    use TampilanTanpaGambar;

    /** Pilihan kategori di form admin. Tambahkan di sini kalau perlu kategori baru. */
    public const KATEGORI = ['Tari', 'Musik', 'Teater', 'Upacara adat', 'Kerajinan', 'Lainnya'];

    /** Ikon per kategori untuk halaman publik (data kesenian tidak punya gambar). */
    public const IKON = [
        'Tari' => '💃',
        'Musik' => '🥁',
        'Teater' => '🎭',
        'Upacara adat' => '🪔',
        'Kerajinan' => '🧵',
        'Lainnya' => '🎨',
    ];

    protected $table = 'kesenian';

    protected $primaryKey = 'id_kesenian';

    protected $fillable = ['nama_kesenian', 'kategori', 'asal_daerah', 'deskripsi'];

    public function getIkonAttribute(): string
    {
        return self::IKON[$this->kategori] ?? '🎨';
    }
}
