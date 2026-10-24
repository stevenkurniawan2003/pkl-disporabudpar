<?php

namespace App\Livewire\Publik;

use App\Models\Kesenian;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.publik')]
#[Title('Kebudayaan · Disporabudpar Jember')]
class Kebudayaan extends Component
{
    use WithPagination;

    #[Url(as: 'kategori', except: 'Semua')]
    public string $kategori = 'Semua';

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    public function pilihKategori(string $nama): void
    {
        $this->kategori = $nama;
        $this->resetPage();
    }

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $kata = trim($this->cari);

        $daftar = Kesenian::query()
            ->when($this->kategori !== 'Semua', fn ($q) => $q->where('kategori', $this->kategori))
            ->when($kata !== '', function ($q) use ($kata) {
                // addcslashes: tanda % dan _ yang diketik pengunjung dicari apa adanya, bukan sebagai wildcard.
                $pola = '%'.addcslashes($kata, '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama_kesenian', 'like', $pola)->orWhere('asal_daerah', 'like', $pola));
            })
            ->orderBy('nama_kesenian')
            ->paginate(12);

        // Chip kategori: hanya kategori yang punya data, urut sesuai daftar di model.
        $adaData = Kesenian::query()->distinct()->pluck('kategori')->all();

        return view('livewire.publik.kebudayaan', [
            'daftar' => $daftar,
            'daftarKategori' => array_values(array_filter(Kesenian::KATEGORI, fn ($k) => in_array($k, $adaData, true))),
        ]);
    }
}
