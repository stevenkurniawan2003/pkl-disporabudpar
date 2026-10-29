<?php

namespace Database\Factories;

use App\Models\Desa;
use App\Models\Ekraf;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ekraf>
 */
class EkrafFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_subsektor' => '1.8',
            'nomor_urut' => fake()->unique()->numberBetween(1, 999),
            'nomor_ekraf' => fn (array $a) => Ekraf::formatNomor($a['kode_subsektor'], $a['nomor_urut']),
            'nama_usaha' => 'Usaha '.fake()->unique()->lastName(),
            'alamat' => 'Jl. Dusun Krajan RT 003/RW 004',
            'kabupaten' => 'Jember',
            'kecamatan_id' => fn () => Kecamatan::firstOrCreate(['nama' => 'Mayang'])->id,
            'desa_id' => fn (array $a) => Desa::firstOrCreate(['kecamatan_id' => $a['kecamatan_id'], 'nama' => 'Tegalwaru'])->id,
            'omzet' => 3000000,
            'no_telepon' => '081335550178',
            'status' => 'aktif',
        ];
    }

    /** Subsektor tertentu, misalnya '1.9' (Kuliner). */
    public function subsektor(string $kode): static
    {
        return $this->state(['kode_subsektor' => $kode]);
    }

    public function nonaktif(): static
    {
        return $this->state(['status' => 'nonaktif']);
    }
}
