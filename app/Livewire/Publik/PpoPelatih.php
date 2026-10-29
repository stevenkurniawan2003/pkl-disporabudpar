<?php

namespace App\Livewire\Publik;

use App\Models\Pelatih;

class PpoPelatih extends PpoDatabase
{
    protected function teks(): array
    {
        return [
            'judul' => 'Database Pelatih',
            'deskripsi' => 'Jumlah dan daftar pelatih Kabupaten Jember per cabang olahraga.',
            'placeholder' => 'Cari nama pelatih atau cabor…',
            'title' => 'Database Pelatih',
        ];
    }

    protected function daftarGrafik(): array
    {
        return Pelatih::dataGrafik();
    }

    protected function kolom(): array
    {
        return ['Nama pelatih', 'L/P', 'Cabor', 'Kecamatan', 'Sertifikat/lisensi', 'Status'];
    }

    protected function data(array $filter): array
    {
        $daftar = Pelatih::with('kecamatan')->filter($filter)->orderBy('nama_lengkap')->paginate(15);

        return [$daftar, $daftar->getCollection()->map(fn (Pelatih $p) => [
            $p->nama_lengkap, $p->jenis_kelamin, $p->nama_cabor, $p->kecamatan?->nama, $p->punya_lisensi ? 'Punya' : 'Tidak Punya', $p->status_label,
        ])->all()];
    }
}
