<?php

namespace App\Livewire\Publik;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman Profil (statis, tidak dikelola dari admin). Satu komponen untuk empat halaman;
 * halaman yang dibuka diambil dari route: ->defaults('halaman', '...').
 * Isinya ada di resources/views/livewire/publik/profil/{halaman}.blade.php.
 */
#[Layout('layouts.publik')]
class Profil extends Component
{
    /** slug => [judul, keterangan singkat di banner]. Urutannya juga urutan tab di banner. */
    public const HALAMAN = [
        'tentang-kami' => ['Tentang Kami', 'Mengenal Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember.'],
        'visi-misi' => ['Visi Misi', 'Arah dan tujuan yang ingin dicapai dinas.'],
        'struktur-organisasi' => ['Struktur Organisasi', 'Susunan organisasi dan tata kerja dinas.'],
        'tugas-dan-fungsi' => ['Tugas dan Fungsi', 'Tugas pokok dan fungsi dinas sesuai peraturan yang berlaku.'],
    ];

    public string $halaman = 'tentang-kami';

    public function mount(string $halaman = 'tentang-kami'): void
    {
        abort_unless(array_key_exists($halaman, self::HALAMAN), 404);

        $this->halaman = $halaman;
    }

    public function render()
    {
        [$judul, $keterangan] = self::HALAMAN[$this->halaman];

        return view('livewire.publik.profil.'.$this->halaman, [
            'judul' => $judul,
            'keterangan' => $keterangan,
            'semuaHalaman' => self::HALAMAN,
            // Bagan struktur organisasi: cukup taruh file gambarnya di public/images/.
            'adaBagan' => is_file(public_path('images/struktur-organisasi.png')),
        ])->title($judul.' · Disporabudpar Jember');
    }
}
