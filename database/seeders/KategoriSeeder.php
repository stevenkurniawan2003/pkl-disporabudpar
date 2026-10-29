<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kategori_wisata')->insertOrIgnore([
            ['nama' => 'Pantai', 'slug' => 'pantai'],
            ['nama' => 'Pegunungan', 'slug' => 'pegunungan'],
            ['nama' => 'Agro', 'slug' => 'agro'],
            ['nama' => 'Museum', 'slug' => 'museum'],
            ['nama' => 'Desa wisata', 'slug' => 'desa-wisata'],
            ['nama' => 'Kuliner', 'slug' => 'kuliner'],
        ]);
        DB::table('kategori_berita')->insertOrIgnore([
            ['nama' => 'Pariwisata', 'slug' => 'pariwisata'],
            ['nama' => 'Ekonomi Kreatif', 'slug' => 'ekonomi-kreatif'],
            ['nama' => 'Kebudayaan', 'slug' => 'kebudayaan'],
            ['nama' => 'Olahraga', 'slug' => 'olahraga'],
            ['nama' => 'Kepemudaan', 'slug' => 'kepemudaan'],
        ]);
    }
}
