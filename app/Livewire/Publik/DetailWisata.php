<?php

namespace App\Livewire\Publik;

use App\Models\Wisata;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.publik')]
class DetailWisata extends Component
{
    public Wisata $wisata;

    // $wisata diisi lewat route model binding: /publik/wisata/{wisata:slug}. Yang sudah dihapus otomatis 404.
    public function mount(Wisata $wisata): void
    {
        // Draf hanya boleh dilihat admin yang sedang login (pratinjau). Pengunjung biasa dapat 404.
        abort_unless($wisata->status === 'tayang' || auth()->check(), 404);

        $this->wisata = $wisata->load(['kategori', 'kecamatan', 'galeri']);
    }

    public function render()
    {
        $w = $this->wisata;

        return view('livewire.publik.detail-destinasi', [
            'jenis' => 'wisata',
            'nama' => $w->nama,
            'label' => $w->kategori?->nama ?? 'Wisata',
            'ikon' => $w->kategori?->ikon ?? '📍',
            'gradien' => $w->gradien,
            'gambar' => $w->gambar_url,
            'ringkas' => $w->deskripsi_singkat,
            'narasi' => $w->narasi,
            'alamat' => trim($w->alamat.($w->kecamatan ? ', Kec. '.$w->kecamatan->nama : '')),
            'telepon' => null,
            'lat' => (float) $w->latitude,
            'lng' => (float) $w->longitude,
            'linkMaps' => $w->link_google_maps,
            'linkWebsite' => null, // tabel wisata tidak punya kolom link_website
            'galeri' => $w->galeri->map(fn ($g) => ['url' => $g->gambar_url, 'keterangan' => $g->keterangan])->all(),
            'pratinjauDraf' => $w->status !== 'tayang',
        ])->title($w->nama.' · Disporabudpar Jember');
    }
}
