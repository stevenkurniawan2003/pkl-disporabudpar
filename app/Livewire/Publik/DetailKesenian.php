<?php

namespace App\Livewire\Publik;

use App\Models\Kesenian;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.publik')]
class DetailKesenian extends Component
{
    public Kesenian $kesenian;

    // $kesenian diisi lewat route model binding: /publik/kebudayaan/{kesenian} (id_kesenian, tabel tidak punya slug).
    public function mount(Kesenian $kesenian): void
    {
        $this->kesenian = $kesenian;
    }

    public function render()
    {
        return view('livewire.publik.detail-kesenian', [
            // Kesenian lain dari kategori yang sama, untuk lanjut menjelajah.
            'serupa' => Kesenian::where('kategori', $this->kesenian->kategori)
                ->whereKeyNot($this->kesenian->getKey())
                ->orderBy('nama_kesenian')
                ->limit(4)
                ->get(),
        ])->title($this->kesenian->nama_kesenian.' · Disporabudpar Jember');
    }
}
