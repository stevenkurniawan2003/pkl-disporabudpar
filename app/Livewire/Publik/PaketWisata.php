<?php

namespace App\Livewire\Publik;

use App\Models\PaketWisata as PaketModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/** Paket wisata Jember terbaru beserta flyer dan contact person (saran dokumen konsep Pariwisata poin 5). */
#[Layout('layouts.publik')]
#[Title('Paket Wisata · Disporabudpar Jember')]
class PaketWisata extends Component
{
    use WithPagination;

    public function render()
    {
        $daftar = PaketModel::tayang()->latest('id_paket')->paginate(9);

        return view('livewire.publik.paket-wisata', [
            'daftar' => $daftar,
            'flyer' => $daftar->getCollection()->map(fn ($p) => ['url' => $p->flyer_url, 'keterangan' => $p->nama_paket])->all(),
        ]);
    }
}
