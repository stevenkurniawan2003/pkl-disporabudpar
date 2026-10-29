<?php

namespace Database\Factories;

use App\Models\CagarBudaya;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CagarBudaya>
 */
class CagarBudayaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Situs '.fake()->unique()->word(),
            'gambar_utama' => 'cagar-budaya/contoh.jpg',
            'alamat' => 'Desa Kamal, Kecamatan Arjasa',
            'latitude' => -8.1,
            'longitude' => 113.7,
            'kategori' => 'Situs',
            'tahun' => null,
            'status' => 'ODCB',
            'kondisi' => 'Terpelihara',
            'deskripsi_sejarah' => '<p>Sejarah singkat.</p>',
        ];
    }
}
