<?php

namespace Database\Factories;

use App\Models\Kecamatan;
use App\Models\Pelatih;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pelatih>
 */
class PelatihFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_lengkap' => fake()->name(),
            'tempat_lahir' => 'Jember',
            'tanggal_lahir' => '1985-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Jawa No. 5',
            'kecamatan_id' => fn () => Kecamatan::firstOrCreate(['nama' => 'Sumbersari'])->id,
            'no_hp' => null,
            'kode_cabor' => 'atletik',
            'punya_lisensi' => false,
            'nomor_lisensi' => null,
            'file_lisensi' => null,
            'status' => 'aktif',
        ];
    }

    public function cabor(string $kode): static
    {
        return $this->state(['kode_cabor' => $kode]);
    }
}
