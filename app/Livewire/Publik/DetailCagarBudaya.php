<?php

namespace App\Livewire\Publik;

use App\Models\CagarBudaya;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.publik')]
class DetailCagarBudaya extends Component
{
    public CagarBudaya $cagarBudaya;

    // /publik/kebudayaan/cagar-budaya/{cagarBudaya} memakai id_cagar_budaya (tabel tidak punya slug).
    public function mount(CagarBudaya $cagarBudaya): void
    {
        $this->cagarBudaya = $cagarBudaya->load('galeri');
    }

    public function render()
    {
        $c = $this->cagarBudaya;

        return view('livewire.publik.detail-kebudayaan', [
            'kembali' => [route('publik.cagar-budaya'), 'Cagar Budaya'],
            'label' => $c->kategori.' · '.$c->status_label,
            'nama' => $c->nama,
            'gambar' => $c->gambar_url,
            'judulIsi' => 'Sejarah',
            'isi' => $c->deskripsi_sejarah,
            'data' => [
                ['geo-alt', 'Alamat', $c->alamat],
                ['bookmark', 'Status', $c->status.' ('.$c->status_label.')'],
                ['calendar3', 'Tahun', $c->tahun],
                ['activity', 'Kondisi', $c->kondisi],
            ],
            'lat' => (float) $c->latitude,
            'lng' => (float) $c->longitude,
            'galeri' => $c->galeri->map(fn ($g) => ['url' => $g->gambar_url, 'keterangan' => null])->all(),
        ])->title($c->nama.' · Disporabudpar Jember');
    }
}
