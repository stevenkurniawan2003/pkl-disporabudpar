<?php

namespace App\Models;

use App\Models\Concerns\TampilanTanpaGambar;
use Illuminate\Database\Eloquent\Model;

class Ekraf extends Model
{
    use TampilanTanpaGambar;

    /** Pilihan subsektor di form admin. Tambahkan di sini kalau perlu subsektor baru. */
    public const SUBSEKTOR = [
        'Kuliner', 'Kriya', 'Fesyen', 'Seni pertunjukan', 'Aplikasi dan game', 'Fotografi',
        'Musik', 'Film, animasi, dan video', 'Desain', 'Penerbitan', 'Lainnya',
    ];

    /** Ikon per subsektor untuk halaman publik (data ekraf tidak punya gambar). */
    public const IKON = [
        'Kuliner' => '🍜',
        'Kriya' => '🏺',
        'Fesyen' => '👗',
        'Seni pertunjukan' => '🎭',
        'Aplikasi dan game' => '🎮',
        'Fotografi' => '📷',
        'Musik' => '🎵',
        'Film, animasi, dan video' => '🎬',
        'Desain' => '🎨',
        'Penerbitan' => '📚',
        'Lainnya' => '💡',
    ];

    protected $table = 'ekraf';

    protected $primaryKey = 'id_ekraf';

    protected $fillable = [
        'nama_usaha', 'nama_pemilik', 'subsektor', 'alamat', 'no_telepon', 'media_sosial', 'deskripsi',
    ];

    public function getIkonAttribute(): string
    {
        return self::IKON[$this->subsektor] ?? '💡';
    }

    /**
     * Nama dan ikon (Bootstrap Icons) platform dari link media sosial, misalnya Instagram.
     *
     * @return array{nama: string, ikon: string}|null null kalau link kosong
     */
    public function getPlatformSosmedAttribute(): ?array
    {
        if (blank($this->media_sosial)) {
            return null;
        }

        $host = strtolower((string) parse_url($this->media_sosial, PHP_URL_HOST));

        foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'youtube' => 'YouTube', 'wa.me' => 'WhatsApp', 'whatsapp' => 'WhatsApp', 'shopee' => 'Shopee', 'tokopedia' => 'Tokopedia'] as $kunci => $nama) {
            if (str_contains($host, $kunci)) {
                $ikon = match ($nama) {
                    'Shopee', 'Tokopedia' => 'bag',
                    default => strtolower($nama),
                };

                return ['nama' => $nama, 'ikon' => $ikon];
            }
        }

        return ['nama' => 'Media sosial', 'ikon' => 'link-45deg'];
    }
}
