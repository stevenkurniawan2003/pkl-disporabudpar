<?php

namespace Database\Factories;

use App\Models\Atlet;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Atlet>
 */
class AtletFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_lengkap' => fake()->name(),
            'tempat_lahir' => 'Jember',
            'tanggal_lahir' => '2006-08-17',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Kalimantan No. 10',
            'kecamatan_id' => fn () => Kecamatan::firstOrCreate(['nama' => 'Sumbersari'])->id,
            'no_hp' => '081234567890',
            'kode_cabor' => 'atletik',
            'klub' => null,
            'prestasi_terakhir' => null,
            'status_binaan' => 'Junior',
            'status' => 'aktif',
        ];
    }

    public function cabor(string $kode): static
    {
        return $this->state(['kode_cabor' => $kode]);
    }

    public function nonaktif(): static
    {
        return $this->state(['status' => 'nonaktif']);
    }
}
