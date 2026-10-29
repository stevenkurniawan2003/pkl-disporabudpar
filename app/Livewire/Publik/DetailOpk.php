<?php

namespace App\Livewire\Publik;

use App\Models\Opk;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.publik')]
class DetailOpk extends Component
{
    public Opk $opk;

    // /publik/kebudayaan/opk/{opk} memakai id_opk (tabel opk tidak punya slug).
    public function mount(Opk $opk): void
    {
        $this->opk = $opk->load('galeri');
    }

    public function render()
    {
        $o = $this->opk;

        return view('livewire.publik.detail-kebudayaan', [
            'kembali' => [route('publik.opk'), 'Objek Pemajuan Kebudayaan'],
            'label' => $o->jenis,
            'nama' => $o->nama,
            'gambar' => $o->gambar_url,
            'judulIsi' => 'Deskripsi',
            'isi' => $o->deskripsi,
            'data' => [
                ['geo-alt', 'Lokasi persebaran', $o->lokasi_persebaran],
                ['map', 'Persebaran', $o->persebaran],
                ['calendar3', 'Tahun', $o->tahun],
                ['people', 'Pelaku budaya', $o->pelaku_budaya],
                ['activity', 'Kondisi', $o->kondisi],
            ],
            'lat' => null,
            'lng' => null,
            'galeri' => $o->galeri->map(fn ($g) => ['url' => $g->gambar_url, 'keterangan' => null])->all(),
        ])->title($o->nama.' · Disporabudpar Jember');
    }
}
