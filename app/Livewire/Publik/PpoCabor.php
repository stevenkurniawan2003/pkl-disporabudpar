<?php

namespace App\Livewire\Publik;

use App\Models\Cabor;
use Illuminate\Pagination\LengthAwarePaginator;

class PpoCabor extends PpoDatabase
{
    protected function teks(): array
    {
        return [
            'judul' => 'Database Cabor',
            'deskripsi' => 'Cabang olahraga Kabupaten Jember beserta jumlah atlet dan pelatih aktifnya.',
            'placeholder' => 'Cari nama cabor…',
            'title' => 'Database Cabor',
        ];
    }

    protected function daftarGrafik(): array
    {
        return Cabor::dataGrafik();
    }

    protected function kolom(): array
    {
        return ['Cabang olahraga', 'Induk organisasi', 'Ketua Pengkab', 'Alamat sekretariat', 'Kontak', 'Atlet aktif', 'Pelatih aktif', 'Prestasi terakhir'];
    }

    protected function data(array $filter): array
    {
        $kata = mb_strtolower(trim($filter['cari']));

        // Maksimal 50 cabor: disaring di PHP (nama cabor ada di konstanta, bukan kolom tabel), dipotong per halaman.
        $semua = Cabor::with('kecamatan')->denganJumlah()
            ->when($filter['cabor'] !== '', fn ($q) => $q->where('kode_cabor', $filter['cabor']))
            ->get()
            ->filter(fn (Cabor $c) => $kata === '' || str_contains(mb_strtolower($c->nama_cabor), $kata))
            ->sortBy('nama_cabor')->values();

        $halaman = LengthAwarePaginator::resolveCurrentPage();
        $daftar = new LengthAwarePaginator($semua->forPage($halaman, 15)->values(), $semua->count(), 15, $halaman);

        return [$daftar, $daftar->getCollection()->map(fn (Cabor $c) => [
            $c->nama_cabor, $c->induk_organisasi, $c->ketua_pengkab,
            $c->alamat_sekretariat.($c->kecamatan ? ', Kec. '.$c->kecamatan->nama : ''),
            $c->kontak, (int) $c->jumlah_atlet_aktif, (int) $c->jumlah_pelatih_aktif, $c->prestasi_terakhir,
        ])->all()];
    }
}
