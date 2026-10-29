<?php

namespace App\Livewire\Publik;

use App\Models\Ekraf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Data EKRAF Jember (dokumen konsep Ekraf poin 3): jumlah pelaku aktif & non aktif,
 * grafik per 17 subsektor dan per kecamatan (grafik garis), serta tombol "Lihat Rincian" ke halaman pencarian.
 */
#[Layout('layouts.publik')]
#[Title('Data EKRAF Jember · Disporabudpar Jember')]
class EkonomiKreatif extends Component
{
    public function render()
    {
        $perStatus = Ekraf::query()->selectRaw('status, COUNT(*) AS jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return view('livewire.publik.ekonomi-kreatif', [
            'jumlahAktif' => (int) ($perStatus['aktif'] ?? 0),
            'jumlahNonaktif' => (int) ($perStatus['nonaktif'] ?? 0),
            'daftarGrafik' => Ekraf::dataGrafik(),
        ]);
    }
}
