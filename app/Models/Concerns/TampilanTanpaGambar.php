<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Accessor tampilan publik untuk data yang tidak punya gambar (kesenian, ekraf):
 * gradien kartu, cuplikan deskripsi, dan deskripsi per paragraf. Model wajib punya kolom "deskripsi".
 */
trait TampilanTanpaGambar
{
    /** Gradien kartu publik, bergantian supaya deretan kartu tidak monoton (sama seperti kartu wisata/berita). */
    public function getGradienAttribute(): string
    {
        $daftar = [
            'linear-gradient(135deg,#E8487F,#F7A1C0)',
            'linear-gradient(135deg,#C93A74,#F28BB0)',
            'linear-gradient(135deg,#F06A9A,#FBC4D8)',
            'linear-gradient(135deg,#D9487F,#EE9CBA)',
        ];

        return $daftar[(int) $this->getKey() % count($daftar)];
    }

    /** Cuplikan deskripsi ±150 karakter tanpa tag HTML. */
    public function getCuplikanAttribute(): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->deskripsi))), 150);
    }

    /** Deskripsi dipecah per paragraf (baris kosong). Tampilkan dengan e() supaya aman dari XSS. */
    public function getParagrafAttribute(): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R{2,}/', (string) $this->deskripsi)),
            fn ($p) => $p !== ''
        ));
    }
}
