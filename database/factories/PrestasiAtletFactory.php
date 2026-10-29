<?php

namespace Database\Factories;

use App\Models\PrestasiAtlet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrestasiAtlet>
 */
class PrestasiAtletFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_atlet' => fake()->name(),
            'jenis_medali' => 'Emas',
            'kode_cabor' => 'atletik',
            'nomor_pertandingan' => 'Lari 100 m putra',
            'nama_kejuaraan' => null,
            'tahun' => 2025,
        ];
    }
}
