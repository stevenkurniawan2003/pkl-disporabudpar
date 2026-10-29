<?php

namespace App\Livewire\Publik;

use App\Models\Wisata;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Desa wisata Kabupaten Jember beserta nomor SK Bupati penetapannya (saran dokumen konsep Pariwisata poin 2). */
#[Layout('layouts.publik')]
#[Title('Desa Wisata · Disporabudpar Jember')]
class DesaWisata extends Component
{
    public function render()
    {
        return view('livewire.publik.desa-wisata', [
            'daftar' => Wisata::with(['kategori', 'kecamatan'])
                ->tayang()
                ->whereHas('kategori', fn ($q) => $q->where('slug', 'desa-wisata'))
                ->orderBy('nama')
                ->get(),
        ]);
    }
}
