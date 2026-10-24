<?php

namespace App\Livewire\Publik;

use App\Models\Ekraf;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.publik')]
class DetailEkraf extends Component
{
    public Ekraf $ekraf;

    // $ekraf diisi lewat route model binding: /publik/ekonomi-kreatif/{ekraf} (id_ekraf, tabel tidak punya slug).
    public function mount(Ekraf $ekraf): void
    {
        $this->ekraf = $ekraf;
    }

    public function render()
    {
        return view('livewire.publik.detail-ekraf', [
            // Usaha lain dari subsektor yang sama, untuk lanjut menjelajah.
            'serupa' => Ekraf::where('subsektor', $this->ekraf->subsektor)
                ->whereKeyNot($this->ekraf->getKey())
                ->orderBy('nama_usaha')
                ->limit(4)
                ->get(),
        ])->title($this->ekraf->nama_usaha.' · Disporabudpar Jember');
    }
}
