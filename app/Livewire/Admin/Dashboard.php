<?php

namespace App\Livewire\Admin;

use App\Models\Berita;
use App\Models\Ekraf;
use App\Models\Hotel;
use App\Models\Kesenian;
use App\Models\KunjunganHotel;
use App\Models\KunjunganWisata;
use App\Models\Wisata;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard · Admin Disporabudpar Jember')]
class Dashboard extends Component
{
    /** Tahun grafik kunjungan hotel. Default tahun berjalan. */
    #[Url(as: 'tahun', except: 0)]
    public int $tahun = 0;

    public function mount(): void
    {
        $this->rapikanTahun();
    }

    /** Ganti tahun: kirim data baru ke grafik (canvas-nya wire:ignore, jadi tidak ikut dirender ulang). */
    public function updatedTahun(): void
    {
        $this->rapikanTahun();
        $this->dispatch('grafik-kunjungan-hotel', data: $this->dataGrafik());
    }

    /** Tahun kosong atau tidak masuk akal (misalnya dari URL) -> tahun berjalan. */
    private function rapikanTahun(): void
    {
        if ($this->tahun < 2000 || $this->tahun > now()->year) {
            $this->tahun = now()->year;
        }
    }

    /**
     * Jumlah tamu hotel per bulan untuk $tahun, dihitung di database (SUM ... GROUP BY bulan).
     * Bulan tanpa data bernilai 0.
     *
     * @return array{label: list<string>, domestik: list<int>, mancanegara: list<int>}
     */
    private function dataGrafik(): array
    {
        $perBulan = KunjunganHotel::query()
            ->where('tahun', $this->tahun)
            ->selectRaw('bulan, SUM(domestik) AS domestik, SUM(mancanegara) AS mancanegara')
            ->groupBy('bulan')
            ->toBase()
            ->get()
            ->keyBy('bulan');

        $data = ['label' => [], 'domestik' => [], 'mancanegara' => []];

        foreach (KunjunganWisata::BULAN as $no => $nama) {
            $data['label'][] = mb_substr($nama, 0, 3);
            $data['domestik'][] = (int) ($perBulan[$no]->domestik ?? 0);
            $data['mancanegara'][] = (int) ($perBulan[$no]->mancanegara ?? 0);
        }

        return $data;
    }

    public function render()
    {
        $grafik = $this->dataGrafik();

        // Pilihan tahun: tahun yang sudah punya data kunjungan hotel + tahun berjalan.
        $daftarTahun = KunjunganHotel::query()->distinct()->pluck('tahun')
            ->push(now()->year)
            ->push($this->tahun)
            ->unique()
            ->sortDesc()
            ->values();

        return view('livewire.admin.dashboard', [
            'jumlahWisata' => Wisata::count(),
            'jumlahUnggulan' => Wisata::unggulan()->count(),
            'jumlahHotel' => Hotel::count(),
            'jumlahBerita' => Berita::count(),
            'jumlahBeritaTerbit' => Berita::terbit()->count(),
            'jumlahKesenian' => Kesenian::count(),
            'jumlahEkraf' => Ekraf::count(),
            'grafik' => $grafik,
            'daftarTahun' => $daftarTahun,
            'daftarBulan' => KunjunganWisata::BULAN,
        ]);
    }
}
