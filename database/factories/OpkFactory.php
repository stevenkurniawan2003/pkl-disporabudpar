<?php

namespace Database\Factories;

use App\Models\Opk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opk>
 */
class OpkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'OPK '.fake()->unique()->word(),
            'gambar_utama' => 'opk/contoh.jpg',
            'lokasi_persebaran' => 'Kecamatan Wuluhan',
            'persebaran' => null,
            'jenis' => 'Seni',
            'tahun' => 1900,
            'pelaku_budaya' => null,
            'kondisi' => 'Bertahan',
            'deskripsi' => '<p>Deskripsi objek pemajuan kebudayaan.</p>',
        ];
    }
}
