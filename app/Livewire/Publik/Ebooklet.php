<?php

namespace App\Livewire\Publik;

use App\Models\Ebooklet as EbookletModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** E-booklet wisata Jember untuk diunduh wisatawan (saran dokumen konsep Pariwisata poin 5). */
#[Layout('layouts.publik')]
#[Title('E-Booklet Wisata · Disporabudpar Jember')]
class Ebooklet extends Component
{
    public function render()
    {
        return view('livewire.publik.ebooklet', [
            'daftar' => EbookletModel::orderByDesc('tahun')->latest('id_ebooklet')->get(),
        ]);
    }
}
