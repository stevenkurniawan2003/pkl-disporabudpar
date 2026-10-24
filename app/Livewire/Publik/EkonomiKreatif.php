<?php

namespace App\Livewire\Publik;

use App\Models\Ekraf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.publik')]
#[Title('Ekonomi Kreatif · Disporabudpar Jember')]
class EkonomiKreatif extends Component
{
    use WithPagination;

    #[Url(as: 'subsektor', except: 'Semua')]
    public string $subsektor = 'Semua';

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    public function pilihSubsektor(string $nama): void
    {
        $this->subsektor = $nama;
        $this->resetPage();
    }

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $kata = trim($this->cari);

        $daftar = Ekraf::query()
            ->when($this->subsektor !== 'Semua', fn ($q) => $q->where('subsektor', $this->subsektor))
            ->when($kata !== '', function ($q) use ($kata) {
                // addcslashes: tanda % dan _ yang diketik pengunjung dicari apa adanya, bukan sebagai wildcard.
                $pola = '%'.addcslashes($kata, '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama_usaha', 'like', $pola)->orWhere('alamat', 'like', $pola));
            })
            ->orderBy('nama_usaha')
            ->paginate(12);

        // Chip subsektor: hanya subsektor yang punya data, urut sesuai daftar di model.
        $adaData = Ekraf::query()->distinct()->pluck('subsektor')->all();

        return view('livewire.publik.ekonomi-kreatif', [
            'daftar' => $daftar,
            'daftarSubsektor' => array_values(array_filter(Ekraf::SUBSEKTOR, fn ($s) => in_array($s, $adaData, true))),
        ]);
    }
}
