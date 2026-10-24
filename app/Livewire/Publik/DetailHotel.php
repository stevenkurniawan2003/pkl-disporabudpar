<?php

namespace App\Livewire\Publik;

use App\Models\Hotel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.publik')]
class DetailHotel extends Component
{
    public Hotel $hotel;

    // $hotel diisi lewat route model binding: /publik/hotel/{hotel} (id_hotel, tabel hotel tidak punya slug).
    public function mount(Hotel $hotel): void
    {
        $this->hotel = $hotel->load('galeri');
    }

    public function render()
    {
        $h = $this->hotel;

        return view('livewire.publik.detail-destinasi', [
            'jenis' => 'hotel',
            'nama' => $h->nama_hotel,
            'label' => 'Hotel',
            'ikon' => '🏨',
            'gradien' => 'linear-gradient(135deg,#C93A74,#F28BB0)',
            'gambar' => $h->gambar_url,
            'ringkas' => $h->deskripsi_singkat,
            'narasi' => $h->narasi,
            'alamat' => $h->alamat,
            'telepon' => $h->no_telepon,
            'lat' => (float) $h->latitude,
            'lng' => (float) $h->longitude,
            'linkMaps' => $h->link_google_maps,
            'linkWebsite' => $h->link_website,
            'galeri' => $h->galeri->map(fn ($g) => ['url' => $g->gambar_url, 'keterangan' => null])->all(),
            'pratinjauDraf' => false,
        ])->title($h->nama_hotel.' · Disporabudpar Jember');
    }
}
