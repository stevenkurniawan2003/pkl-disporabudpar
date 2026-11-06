<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\KategoriBerita;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data CONTOH berita (isi hanya teks pengisi). Jalankan sekali:
 *   php artisan db:seed --class=BeritaSeeder
 * Setelah itu semua data bisa diubah/dihapus lewat admin.
 */
class BeritaSeeder extends Seeder
{
    public function run(): void
    {
        $contoh = [
            ['Bus Si Cinta: inovasi transportasi wisata Jember', 'Pariwisata', '2026-07-17', 'terbit', 482],
            ['Saujana Jatim: mengubah wisata menjadi aksi nyata', 'Pariwisata', '2026-07-17', 'terbit', 310],
            ['Festival Hyang Argopuro ke-9 digelar di Arjasa', 'Kebudayaan', '2025-09-12', 'terbit', 655],
            ['Jember Creative Centre disiapkan untuk pelaku ekraf', 'Ekonomi Kreatif', '2025-08-14', 'terbit', 274],
            ['Kartu induk kesenian diserahkan kepada 15 kelompok', 'Kebudayaan', '2025-10-01', 'terbit', 198],
            ['Rencana Jember Creative Centre', 'Ekonomi Kreatif', '2026-08-14', 'draf', 0],
        ];

        foreach ($contoh as [$judul, $kategori, $tanggal, $status, $dibaca]) {
            $kat = KategoriBerita::where('nama', $kategori)->first();

            if (! $kat) {
                $this->command?->warn("Lewati \"{$judul}\": jalankan KategoriSeeder dulu.");

                continue;
            }

            Berita::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($judul)],
                [
                    'kategori_berita_id' => $kat->id,
                    'judul' => $judul,
                    'ringkasan' => null,
                    'isi' => "Ini teks contoh untuk berita \"{$judul}\". Ganti dengan isi berita asli lewat panel admin.\n\nPisahkan paragraf dengan satu baris kosong.",
                    'status' => $status,
                    'tanggal_terbit' => $tanggal.' 08:00:00',
                    'jumlah_dibaca' => $dibaca,
                    'deleted_at' => null,
                ]
            );
        }

        $this->command?->info('Data contoh berita siap.');
    }
}
