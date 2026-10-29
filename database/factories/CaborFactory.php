<?php

namespace Database\Factories;

use App\Models\Cabor;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cabor>
 */
class CaborFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_cabor' => fake()->unique()->randomElement(array_keys(Cabor::DAFTAR)),
            'ketua_pengkab' => fake()->name(),
            'alamat_sekretariat' => 'Komplek GOR PKPSO',
            'kecamatan_id' => fn () => Kecamatan::firstOrCreate(['nama' => 'Kaliwates'])->id,
            'latitude' => null,
            'longitude' => null,
            'prestasi_terakhir' => null,
            'kontak' => '0331 123456',
            'status' => 'aktif',
        ];
    }
}
