<?php

namespace App\Livewire\Publik;

use App\Models\Cabor;
use App\Models\GaleriPrestasi;
use App\Models\PrestasiAtlet;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Sorotan Prestasi (PPO): grafik atlet berprestasi tiap tahun, Galeri Prestasi, dan Data Atlet Berprestasi. */
#[Layout('layouts.publik')]
#[Title('Sorotan Prestasi · Disporabudpar Jember')]
class SorotanPrestasi extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $cabor = '';

    #[Url(except: 0)]
    public int $tahun = 0;

    public function mount(): void
    {
        $this->rapikanTahun();
    }

    public function updated(): void
    {
        $this->rapikanTahun();
        $this->resetPage();
    }

    private function rapikanTahun(): void
    {
        $daftarTahun = $this->daftarTahun();

        if ($this->tahun === 0 || ! $daftarTahun->contains($this->tahun)) {
            $tahunDataTerbaru = (int) PrestasiAtlet::query()->whereIn('tahun', $daftarTahun)->max('tahun');
            $this->tahun = $tahunDataTerbaru ?: ($daftarTahun->first() ?? now()->year);
        }
    }

    /** Pilihan 5 tahun terakhir yang terintegrasi dengan tahun di database. */
    private function daftarTahun(): Collection
    {
        $tahunSekarang = now()->year;

        return PrestasiAtlet::query()
            ->distinct()
            ->pluck('tahun')
            ->merge(range($tahunSekarang - 4, $tahunSekarang))
            ->map(fn ($t) => (int) $t)
            ->unique()
            ->sortDesc()
            ->take(5)
            ->values();
    }

    public function render()
    {
        $daftarTahun = $this->daftarTahun();

        $daftar = PrestasiAtlet::query()
            ->when(isset(Cabor::DAFTAR[$this->cabor]), fn ($q) => $q->where('kode_cabor', $this->cabor))
            ->where('tahun', $this->tahun)
            ->orderByDesc('tahun')
            ->orderByRaw("CASE jenis_medali WHEN 'Emas' THEN 1 WHEN 'Perak' THEN 2 WHEN 'Perunggu' THEN 3 ELSE 0 END")
            ->orderBy('nama_atlet')
            ->paginate(15);

        $galeri = GaleriPrestasi::latest('id_galeri_prestasi')->limit(12)->get();

        return view('livewire.publik.sorotan-prestasi', [
            'daftar' => $daftar,
            'daftarGrafik' => PrestasiAtlet::dataGrafik(),
            'daftarCabor' => Cabor::pilihan(),
            'daftarTahun' => $daftarTahun,
            'galeri' => $galeri,
            // Foto saja yang dibuka di lightbox; video diputar langsung di kartunya.
            'fotoLightbox' => $galeri->where('jenis', 'foto')->values()->map(fn ($g) => ['url' => $g->file_url, 'keterangan' => $g->judul])->all(),
        ]);
    }
}
