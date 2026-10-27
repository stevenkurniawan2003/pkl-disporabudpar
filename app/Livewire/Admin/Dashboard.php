<?php

namespace App\Livewire\Admin;

use App\Models\Berita;
use App\Models\Ekraf;
use App\Models\Hotel;
use App\Models\Kesenian;
use App\Models\KunjunganHotel;
use App\Models\KunjunganWisata;
use App\Models\Wisata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard · Admin Disporabudpar Jember')]
class Dashboard extends Component
{
    /** Tahun grafik kunjungan wisata. Default tahun berjalan. */
    #[Url(as: 'tahun_wisata', except: 0)]
    public int $tahunWisata = 0;

    /** Tahun grafik kunjungan hotel. Default tahun berjalan. */
    #[Url(as: 'tahun', except: 0)]
    public int $tahun = 0;

    public function mount(): void
    {
        $this->tahunWisata = $this->rapikanTahun($this->tahunWisata);
        $this->tahun = $this->rapikanTahun($this->tahun);
    }

    /** Ganti tahun: kirim data baru ke grafik (canvas-nya wire:ignore, jadi tidak ikut dirender ulang). */
    public function updatedTahunWisata(): void
    {
        $this->tahunWisata = $this->rapikanTahun($this->tahunWisata);
        $this->dispatch('grafik-kunjungan-wisata', data: $this->dataGrafik(KunjunganWisata::query(), $this->tahunWisata));
    }

    public function updatedTahun(): void
    {
        $this->tahun = $this->rapikanTahun($this->tahun);
        $this->dispatch('grafik-kunjungan-hotel', data: $this->dataGrafik(KunjunganHotel::query(), $this->tahun));
    }

    /** Tahun kosong atau tidak masuk akal (misalnya dari URL) -> tahun berjalan. */
    private function rapikanTahun(int $tahun): int
    {
        return ($tahun < 2000 || $tahun > now()->year) ? now()->year : $tahun;
    }

    /**
     * Jumlah kunjungan per bulan untuk $tahun, dihitung di database (SUM ... GROUP BY bulan).
     * Bulan tanpa data bernilai 0.
     *
     * @return array{label: list<string>, domestik: list<int>, mancanegara: list<int>}
     */
    private function dataGrafik(Builder $query, int $tahun): array
    {
        $perBulan = $query
            ->where('tahun', $tahun)
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

    /** Pilihan tahun: tahun yang sudah punya data + tahun berjalan + tahun terpilih. */
    private function daftarTahun(Builder $query, int $terpilih): Collection
    {
        return $query->distinct()->pluck('tahun')
            ->push(now()->year)
            ->push($terpilih)
            ->map(fn ($t) => (int) $t)
            ->unique()
            ->sortDesc()
            ->values();
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'jumlahWisata' => Wisata::count(),
            'jumlahUnggulan' => Wisata::unggulan()->count(),
            'jumlahHotel' => Hotel::count(),
            'jumlahBerita' => Berita::count(),
            'jumlahBeritaTerbit' => Berita::terbit()->count(),
            'jumlahKesenian' => Kesenian::count(),
            'jumlahEkraf' => Ekraf::count(),
            // Kunci: nama properti tahun (wire:model), id canvas/event, judul, satuan tooltip, tahun, data, pilihan tahun, menu isian.
            'daftarGrafik' => [
                [
                    'model' => 'tahunWisata',
                    'kunci' => 'grafik-kunjungan-wisata',
                    'judul' => 'Kunjungan wisata per bulan',
                    'satuan' => 'wisatawan',
                    'tahun' => $this->tahunWisata,
                    'data' => $this->dataGrafik(KunjunganWisata::query(), $this->tahunWisata),
                    'daftarTahun' => $this->daftarTahun(KunjunganWisata::query(), $this->tahunWisata),
                    'menu' => 'Data Kunjungan Wisata',
                ],
                [
                    'model' => 'tahun',
                    'kunci' => 'grafik-kunjungan-hotel',
                    'judul' => 'Kunjungan hotel per bulan',
                    'satuan' => 'tamu',
                    'tahun' => $this->tahun,
                    'data' => $this->dataGrafik(KunjunganHotel::query(), $this->tahun),
                    'daftarTahun' => $this->daftarTahun(KunjunganHotel::query(), $this->tahun),
                    'menu' => 'Data Kunjungan Hotel',
                ],
            ],
            'daftarBulan' => KunjunganWisata::BULAN,
        ]);
    }
}
