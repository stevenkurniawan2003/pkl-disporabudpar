<?php

namespace App\Livewire\Publik;

use App\Models\CagarBudaya;
use App\Models\Kecamatan;
use App\Models\Kesenian;
use App\Models\Opk;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Dashboard Kebudayaan untuk publik (dokumen konsep Kebudayaan): kartu rincian data kesenian,
 * grafik OPK per jenis, grafik cagar budaya per kategori, dan grafik kesenian per jenis & per kecamatan.
 * Grafik publik wajib grafik garis. Semua angka dihitung di database (COUNT ... GROUP BY).
 */
#[Layout('layouts.publik')]
#[Title('Kebudayaan · Disporabudpar Jember')]
class Kebudayaan extends Component
{
    public function render()
    {
        $perJenis = Kesenian::query()->selectRaw('kode_jenis, COUNT(*) AS jumlah')->groupBy('kode_jenis')->pluck('jumlah', 'kode_jenis');
        $perKecamatan = Kesenian::query()->selectRaw('kecamatan_id, COUNT(*) AS jumlah')->groupBy('kecamatan_id')->pluck('jumlah', 'kecamatan_id');
        $perOpk = Opk::query()->selectRaw('jenis, COUNT(*) AS jumlah')->groupBy('jenis')->pluck('jumlah', 'jenis');
        $perCagar = CagarBudaya::query()->selectRaw('kategori, status, COUNT(*) AS jumlah')->groupBy('kategori', 'status')->toBase()->get();

        // Kartu ringkasan per kelompok jenis (1.x Seni Musik, 2.x Seni Pertunjukan, 3.x Seni Sastra Tradisi).
        $perKelompok = collect(Kesenian::KELOMPOK)->map(fn ($nama, $awalan) => [
            'nama' => $nama,
            'jumlah' => (int) $perJenis->filter(fn ($n, $kode) => strtok((string) $kode, '.') === (string) $awalan)->sum(),
        ])->values();

        $kecamatan = Kecamatan::orderBy('nama')->get(['id', 'nama']);
        $angka = fn ($kumpulan, $kunci) => (int) ($kumpulan[$kunci] ?? 0);

        return view('livewire.publik.kebudayaan', [
            'perKelompok' => $perKelompok,
            'totalKesenian' => (int) $perJenis->sum(),
            'totalAktif' => Kesenian::aktif()->count(),
            'totalOpk' => (int) $perOpk->sum(),
            'totalCagar' => (int) $perCagar->sum('jumlah'),
            'daftarGrafik' => [
                [
                    'id' => 'grafik-opk',
                    'judul' => 'Grafik Objek Pemajuan Kebudayaan per jenis',
                    'satuan' => 'objek',
                    'label' => Opk::JENIS,
                    'seri' => [['label' => 'Jumlah OPK', 'warna' => '--seri-1', 'data' => array_map(fn ($j) => $angka($perOpk, $j), Opk::JENIS)]],
                ],
                [
                    'id' => 'grafik-cagar-budaya',
                    'judul' => 'Grafik data cagar budaya per kategori',
                    'satuan' => 'objek',
                    'label' => CagarBudaya::KATEGORI,
                    'seri' => collect(CagarBudaya::STATUS)->map(fn ($nama, $kode) => [
                        'label' => $kode.' ('.$nama.')',
                        'warna' => $kode === 'CB' ? '--seri-1' : '--seri-2',
                        'data' => array_map(fn ($k) => (int) $perCagar->where('kategori', $k)->where('status', $kode)->sum('jumlah'), CagarBudaya::KATEGORI),
                    ])->values()->all(),
                ],
                [
                    'id' => 'grafik-jenis-kesenian',
                    'judul' => 'Grafik rincian data per jenis kesenian (total '.number_format($perJenis->sum(), 0, ',', '.').')',
                    'satuan' => 'kelompok',
                    'label' => array_map(fn ($kode) => $kode.' '.trim(strrchr(Kesenian::JENIS[$kode], '-') ?: Kesenian::JENIS[$kode], '- '), array_keys(Kesenian::JENIS)),
                    'seri' => [['label' => 'Jumlah kelompok kesenian', 'warna' => '--seri-1', 'data' => array_map(fn ($kode) => $angka($perJenis, $kode), array_keys(Kesenian::JENIS))]],
                ],
                [
                    'id' => 'grafik-kecamatan-kesenian',
                    'judul' => 'Grafik rincian data kesenian per kecamatan',
                    'satuan' => 'kelompok',
                    'label' => $kecamatan->pluck('nama')->all(),
                    'seri' => [['label' => 'Jumlah kelompok kesenian', 'warna' => '--seri-2', 'data' => $kecamatan->map(fn ($k) => $angka($perKecamatan, $k->id))->all()]],
                ],
            ],
        ]);
    }
}
