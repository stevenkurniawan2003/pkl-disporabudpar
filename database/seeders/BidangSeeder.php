<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidangSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $bidang = [
            ['nama' => 'Pariwisata', 'slug' => 'pariwisata', 'jenis' => 'bidang', 'urutan' => 1],
            ['nama' => 'Ekonomi Kreatif', 'slug' => 'ekonomi-kreatif', 'jenis' => 'bidang', 'urutan' => 2],
            ['nama' => 'Kebudayaan', 'slug' => 'kebudayaan', 'jenis' => 'bidang', 'urutan' => 3],
            ['nama' => 'Peningkatan Prestasi Olahraga', 'slug' => 'peningkatan-prestasi-olahraga', 'jenis' => 'bidang', 'urutan' => 4],
            ['nama' => 'Kepemudaan', 'slug' => 'kepemudaan', 'jenis' => 'bidang', 'urutan' => 5],
            ['nama' => 'UPTD Wisata Terpadu', 'slug' => 'uptd-wisata-terpadu', 'jenis' => 'uptd', 'urutan' => 6],
            ['nama' => 'UPTD Olahraga', 'slug' => 'uptd-olahraga', 'jenis' => 'uptd', 'urutan' => 7],
        ];

        foreach ($bidang as $b) {
            DB::table('bidang')->updateOrInsert(
                ['slug' => $b['slug']],
                $b + ['created_at' => $now, 'updated_at' => $now]
            );
        }

        // Struktur organisasi awal: Kepala Dinas di puncak, tiap bidang/UPTD di bawahnya.
        // Nama pejabat diisi lewat admin.
        $kepala = DB::table('struktur_organisasi')->whereNull('parent_id')->where('jabatan', 'Kepala Dinas')->first();
        $kepalaId = $kepala->id ?? DB::table('struktur_organisasi')->insertGetId([
            'jabatan' => 'Kepala Dinas', 'urutan' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach (DB::table('bidang')->orderBy('urutan')->get() as $b) {
            DB::table('struktur_organisasi')->updateOrInsert(
                ['bidang_id' => $b->id],
                [
                    'parent_id' => $kepalaId,
                    'jabatan' => ($b->jenis === 'uptd' ? 'Kepala ' : 'Kepala Bidang ').$b->nama,
                    'urutan' => $b->urutan,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
