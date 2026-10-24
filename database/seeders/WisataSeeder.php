<?php

namespace Database\Seeders;

use App\Models\KategoriWisata;
use App\Models\Kecamatan;
use App\Models\Wisata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data CONTOH untuk mencoba tampilan. Boleh dijalankan sekali:
 *   php artisan db:seed --class=WisataSeeder
 * Setelah itu semua data bisa diubah/dihapus lewat admin.
 */
class WisataSeeder extends Seeder
{
    public function run(): void
    {
        $contoh = [
            ['Pantai Tanjung Papuma', 'Pantai', 'Wuluhan', 'Lojejer', -8.4286, 113.5469, true, 'Pantai berpasir putih dengan tebing dan hutan lindung di sekelilingnya.'],
            ['Pantai Bandealit', 'Pantai', 'Tempurejo', 'Andongrejo', -8.3950, 113.6490, true, null],
            ['Wisata Rembangan', 'Pegunungan', 'Arjasa', 'Rembangan', -8.1500, 113.7000, true, null],
            ['Agro Wisata Cerutu', 'Agro', 'Kaliwates', 'Kaliwates', -8.1700, 113.7000, false, null],
            ['Museum Tembakau', 'Museum', 'Sumbersari', 'Sumbersari', -8.1690, 113.7150, false, null],
            ['Rengganis Forest Park', 'Pegunungan', 'Panti', 'Panti', -8.1100, 113.6200, false, null],
        ];

        foreach ($contoh as [$nama, $kategori, $kecamatan, $alamat, $lat, $lng, $unggulan, $narasi]) {
            $kat = KategoriWisata::where('nama', $kategori)->first();
            $kec = Kecamatan::where('nama', $kecamatan)->first();

            if (! $kat || ! $kec) {
                $this->command?->warn("Lewati {$nama}: jalankan KategoriSeeder dan KecamatanSeeder dulu.");
                continue;
            }

            Wisata::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($nama)],
                [
                    'kategori_id' => $kat->id,
                    'kecamatan_id' => $kec->id,
                    'nama' => $nama,
                    'narasi' => $narasi,
                    'alamat' => $alamat,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'unggulan' => $unggulan,
                    'status' => 'tayang',
                    'deleted_at' => null,
                ]
            );
        }

        $this->command?->info('Data contoh wisata siap.');
    }
}
