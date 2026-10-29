<?php

namespace App\Livewire\Publik;

use App\Models\Desa;
use App\Models\Ekraf;
use App\Models\Kecamatan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pencarian Data Ekonomi Kreatif Jember (dokumen konsep Ekraf poin 4-5): filter kecamatan -> desa -> subsektor.
 * Omzet dan nomor telepon tidak ditampilkan ke publik (data pribadi/usaha).
 */
#[Layout('layouts.publik')]
#[Title('Pencarian Data Ekonomi Kreatif · Disporabudpar Jember')]
class PencarianEkraf extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: '')]
    public string $kecamatan = '';

    #[Url(except: '')]
    public string $desa = '';

    #[Url(except: '')]
    public string $subsektor = '';

    public function updated(string $properti): void
    {
        if ($properti === 'kecamatan') {
            $this->desa = '';
        }

        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset();
        $this->resetPage();
    }

    public function render()
    {
        $kata = trim($this->cari);

        $daftar = Ekraf::query()
            ->with(['kecamatan', 'desa'])
            ->filter([
                'kecamatan' => $this->kecamatan,
                'desa' => $this->desa,
                'subsektor' => isset(Ekraf::SUBSEKTOR[$this->subsektor]) ? $this->subsektor : '',
            ])
            ->when($kata !== '', fn ($q) => $q->where('nama_usaha', 'like', '%'.addcslashes($kata, '%_\\').'%'))
            ->orderBy('kode_subsektor')
            ->orderBy('nomor_urut')
            ->paginate(15);

        return view('livewire.publik.pencarian-ekraf', [
            'daftar' => $daftar,
            'daftarKecamatan' => Kecamatan::orderBy('nama')->get(['id', 'nama']),
            'daftarDesa' => $this->kecamatan !== '' ? Desa::where('kecamatan_id', (int) $this->kecamatan)->orderBy('nama')->get(['id', 'nama']) : collect(),
            'daftarSubsektor' => Ekraf::SUBSEKTOR,
        ]);
    }
}
