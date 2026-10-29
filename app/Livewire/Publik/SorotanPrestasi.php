<?php

namespace App\Livewire\Publik;

use App\Models\Cabor;
use App\Models\GaleriPrestasi;
use App\Models\PrestasiAtlet;
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

    #[Url(except: '')]
    public string $tahun = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $daftar = PrestasiAtlet::query()
            ->when(isset(Cabor::DAFTAR[$this->cabor]), fn ($q) => $q->where('kode_cabor', $this->cabor))
            ->when(ctype_digit($this->tahun), fn ($q) => $q->where('tahun', (int) $this->tahun))
            ->orderByDesc('tahun')
            ->orderByRaw("FIELD(jenis_medali, 'Emas', 'Perak', 'Perunggu')")
            ->orderBy('nama_atlet')
            ->paginate(15);

        $galeri = GaleriPrestasi::latest('id_galeri_prestasi')->limit(12)->get();

        return view('livewire.publik.sorotan-prestasi', [
            'daftar' => $daftar,
            'daftarGrafik' => PrestasiAtlet::dataGrafik(),
            'daftarCabor' => Cabor::pilihan(),
            'daftarTahun' => PrestasiAtlet::query()->distinct()->orderByDesc('tahun')->pluck('tahun'),
            'galeri' => $galeri,
            // Foto saja yang dibuka di lightbox; video diputar langsung di kartunya.
            'fotoLightbox' => $galeri->where('jenis', 'foto')->values()->map(fn ($g) => ['url' => $g->file_url, 'keterangan' => $g->judul])->all(),
        ]);
    }
}
