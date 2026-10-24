<?php

namespace App\Livewire\Publik;

use App\Models\KunjunganWisata as KunjunganModel;
use App\Models\Wisata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.publik')]
class KunjunganWisata extends HalamanKunjungan
{
    #[Url(as: 'tahun', except: 0)]
    public int $tahun = 0;

    #[Url(as: 'wisata', except: 0)]
    public int $pilihan = 0;

    protected function queryPublik(): Builder
    {
        return KunjunganModel::query()->tampilPublik();
    }

    protected function kolomTempat(): string
    {
        return 'wisata_id';
    }

    protected function daftarPilihan(): Collection
    {
        return Wisata::tayang()->orderBy('nama')->get(['id', 'nama']);
    }

    protected function rekapPerTempat(Builder $query): Collection
    {
        return $query
            ->join('wisata', 'wisata.id', '=', 'kunjungan_wisata.wisata_id')
            ->groupBy('wisata.id', 'wisata.nama', 'wisata.slug')
            ->selectRaw('wisata.nama, wisata.slug, SUM(domestik) AS domestik, SUM(mancanegara) AS mancanegara, SUM(domestik + mancanegara) AS total')
            ->orderByDesc('total')
            ->orderBy('wisata.nama')
            ->toBase()
            ->get()
            ->map(fn ($r) => (object) [
                'nama' => $r->nama,
                'url' => route('publik.detail-wisata', $r->slug),
                'domestik' => (int) $r->domestik,
                'mancanegara' => (int) $r->mancanegara,
                'total' => (int) $r->total,
            ]);
    }

    protected function teks(): array
    {
        return [
            'judul' => 'Data kunjungan wisata',
            'deskripsi' => 'Per destinasi, per bulan, wisatawan domestik dan mancanegara.',
            'semua' => 'Semua destinasi',
            'kolom' => 'Destinasi wisata',
            'orang' => 'Wisatawan',
            'title' => 'Data Kunjungan Wisata · Disporabudpar Jember',
        ];
    }
}
