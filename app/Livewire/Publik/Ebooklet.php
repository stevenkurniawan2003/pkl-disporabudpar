<?php

namespace App\Livewire\Publik;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** E-booklet wisata Jember: halaman statis dengan 1 link ke Google Drive (tidak ada CRUD). */
#[Layout('layouts.publik')]
#[Title('E-Booklet Wisata · Disporabudpar Jember')]
class Ebooklet extends Component
{
    public function render()
    {
        return view('livewire.publik.ebooklet', [
            'linkEbooklet' => config('site.ebooklet_url', ''),
            'gambarEbooklet' => config('site.ebooklet_gambar', ''),
        ]);
    }
}
