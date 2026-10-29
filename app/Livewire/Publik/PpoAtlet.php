<?php

namespace App\Livewire\Publik;

use App\Models\Atlet;

class PpoAtlet extends PpoDatabase
{
    protected function teks(): array
    {
        return [
            'judul' => 'Database Atlet',
            'deskripsi' => 'Jumlah dan daftar atlet Kabupaten Jember per cabang olahraga.',
            'placeholder' => 'Cari nama atlet atau cabor…',
            'title' => 'Database Atlet',
        ];
    }

    protected function daftarGrafik(): array
    {
        return Atlet::dataGrafik();
    }

    protected function kolom(): array
    {
        return ['Nama atlet', 'L/P', 'Cabor', 'Klub', 'Kecamatan', 'Status binaan', 'Status'];
    }

    protected function data(array $filter): array
    {
        $daftar = Atlet::with('kecamatan')->filter($filter)->orderBy('nama_lengkap')->paginate(15);

        return [$daftar, $daftar->getCollection()->map(fn (Atlet $a) => [
            $a->nama_lengkap, $a->jenis_kelamin, $a->nama_cabor, $a->klub, $a->kecamatan?->nama, $a->status_binaan, $a->status_label,
        ])->all()];
    }
}
