<?php

namespace App\Livewire\Publik;

use App\Models\Berita;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.publik')]
class DetailBerita extends Component
{
    public Berita $berita;

    // $berita diisi otomatis lewat route model binding: /publik/berita/{berita:slug}
    public function mount(Berita $berita): void
    {
        // Draf / terjadwal hanya boleh dilihat admin yang sedang login (pratinjau).
        // Pengunjung biasa dapat 404.
        abort_unless($berita->sedangTayang() || auth()->check(), 404);

        // Hitung pembaca hanya untuk berita yang sudah tayang, sekali per sesi.
        if ($berita->sedangTayang() && ! session()->has("dibaca.{$berita->id}")) {
            $berita->increment('jumlah_dibaca');
            session()->put("dibaca.{$berita->id}", true);
        }

        $this->berita = $berita->load(['kategori', 'penulis', 'galeri']);
    }

    public function render()
    {
        return view('livewire.publik.detail-berita', [
            'galeri' => $this->berita->galeri->map(fn ($g) => ['url' => $g->gambar_url, 'keterangan' => null])->all(),
            // 4 berita tayang terbaru selain yang sedang dibuka.
            'lainnya' => Berita::with('kategori')
                ->terbit()
                ->whereKeyNot($this->berita->getKey())
                ->latest('tanggal_terbit')
                ->latest('id')
                ->limit(4)
                ->get(),
        ])->title($this->berita->judul.' · Disporabudpar Jember');
    }
}
