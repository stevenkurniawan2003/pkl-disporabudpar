<?php

namespace Database\Factories;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kesenian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kesenian>
 */
class KesenianFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_jenis' => '2.5',
            'nomor_urut' => fake()->unique()->numberBetween(1, 999),
            'nomor_induk' => fn (array $a) => Kesenian::formatNomorInduk($a['kode_jenis'], $a['nomor_urut']),
            'nama_kesenian' => 'Sanggar '.fake()->unique()->lastName(),
            'nama_pemilik' => fake()->name(),
            'alamat' => 'Dusun Krajan RT 001 / RW 001',
            'kabupaten' => 'Jember',
            'kecamatan_id' => fn () => Kecamatan::firstOrCreate(['nama' => 'Kaliwates'])->id,
            'desa_id' => fn (array $a) => Desa::firstOrCreate(['kecamatan_id' => $a['kecamatan_id'], 'nama' => 'Kepatihan'])->id,
            'jumlah_anggota' => fake()->numberBetween(5, 120),
            'no_telepon' => '081234567890',
            'status' => 'aktif',
        ];
    }

    /** Jenis kesenian tertentu, misalnya '1.2' (Musik Patrol). */
    public function jenis(string $kode): static
    {
        return $this->state(['kode_jenis' => $kode]);
    }

    public function nonaktif(): static
    {
        return $this->state(['status' => 'nonaktif']);
    }
}
