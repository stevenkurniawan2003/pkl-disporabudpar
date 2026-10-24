<?php

namespace App\Livewire\Publik;

use App\Models\KunjunganWisata as KunjunganWisataModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Dasar halaman publik Data Kunjungan Wisata & Data Kunjungan Hotel:
 * filter tahun + pilihan (wisata/hotel), kartu ringkasan, grafik per bulan, dan rekap per tempat.
 * Kelas turunan cukup menentukan sumber data lewat method abstrak di bawah.
 * Semua angka dihitung di database (SUM ... GROUP BY).
 */
#[Layout('layouts.publik')]
abstract class HalamanKunjungan extends Component
{
    /** Tahun terpilih. 0 = belum dipilih -> tahun terbaru yang punya data. */
    public int $tahun = 0;

    /** Wisata/hotel terpilih (id). 0 = semua. Kelas turunan mendeklarasikan ulang dengan #[Url] masing-masing. */
    public int $pilihan = 0;

    /** Data kunjungan yang boleh tampil di publik. */
    abstract protected function queryPublik(): Builder;

    /** Kolom kunjungan yang menunjuk ke tempat (wisata_id / hotel_id). */
    abstract protected function kolomTempat(): string;

    /** Pilihan di dropdown: koleksi objek dengan ->id dan ->nama. */
    abstract protected function daftarPilihan(): Collection;

    /** Rekap per tempat: baris berisi nama, url, domestik, mancanegara, total (terbanyak dulu). */
    abstract protected function rekapPerTempat(Builder $query): Collection;

    /** Teks halaman: judul, deskripsi, semua (label "semua ..."), kolom (judul kolom tempat), title. */
    abstract protected function teks(): array;

    public function mount(): void
    {
        $this->rapikanFilter();
    }

    public function updated(): void
    {
        $this->rapikanFilter();
        $this->dispatch('grafik-kunjungan', data: $this->dataGrafik());
    }

    /** Tahun/pilihan dari URL yang tidak ada datanya -> kembali ke default. */
    private function rapikanFilter(): void
    {
        $daftarTahun = $this->daftarTahun();

        if (! $daftarTahun->contains($this->tahun)) {
            $this->tahun = $daftarTahun->first() ?? now()->year;
        }

        if ($this->pilihan !== 0 && ! $this->daftarPilihan()->contains('id', $this->pilihan)) {
            $this->pilihan = 0;
        }
    }

    /** Tahun yang punya data, terbaru dulu. */
    private function daftarTahun(): Collection
    {
        return $this->queryPublik()->distinct()->orderByDesc('tahun')->pluck('tahun')->map(fn ($t) => (int) $t);
    }

    private function queryTersaring(): Builder
    {
        return $this->queryPublik()
            ->where('tahun', $this->tahun)
            ->when($this->pilihan !== 0, fn ($q) => $q->where($this->kolomTempat(), $this->pilihan));
    }

    /**
     * Jumlah per bulan (SUM ... GROUP BY bulan). Bulan tanpa data bernilai 0.
     *
     * @return array{label: list<string>, nama_bulan: list<string>, domestik: list<int>, mancanegara: list<int>}
     */
    private function dataGrafik(): array
    {
        $perBulan = $this->queryTersaring()
            ->selectRaw('bulan, SUM(domestik) AS domestik, SUM(mancanegara) AS mancanegara')
            ->groupBy('bulan')
            ->toBase()
            ->get()
            ->keyBy('bulan');

        $data = ['label' => [], 'nama_bulan' => [], 'domestik' => [], 'mancanegara' => []];

        foreach (KunjunganWisataModel::BULAN as $no => $nama) {
            $data['label'][] = mb_substr($nama, 0, 3);
            $data['nama_bulan'][] = $nama;
            $data['domestik'][] = (int) ($perBulan[$no]->domestik ?? 0);
            $data['mancanegara'][] = (int) ($perBulan[$no]->mancanegara ?? 0);
        }

        return $data;
    }

    public function render()
    {
        $grafik = $this->dataGrafik();
        $teks = $this->teks();

        return view('livewire.publik.kunjungan', [
            'teks' => $teks,
            'grafik' => $grafik,
            'totalDomestik' => array_sum($grafik['domestik']),
            'totalMancanegara' => array_sum($grafik['mancanegara']),
            'rekap' => $this->rekapPerTempat($this->queryTersaring()),
            'daftarTahun' => $this->daftarTahun(),
            'daftarPilihan' => $this->daftarPilihan(),
        ])->title($teks['title']);
    }
}
