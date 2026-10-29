<?php

namespace App\Livewire\Publik;

use App\Models\CagarBudaya;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.publik')]
#[Title('Cagar Budaya · Disporabudpar Jember')]
class DaftarCagarBudaya extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: 'Semua')]
    public string $kategori = 'Semua';

    public function pilihKategori(string $kategori): void
    {
        $this->kategori = in_array($kategori, CagarBudaya::KATEGORI, true) ? $kategori : 'Semua';
        $this->resetPage();
    }

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $kata = trim($this->cari);

        $daftar = CagarBudaya::query()
            ->when($this->kategori !== 'Semua', fn ($q) => $q->where('kategori', $this->kategori))
            ->when($kata !== '', function ($q) use ($kata) {
                $pola = '%'.addcslashes($kata, '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $pola)->orWhere('alamat', 'like', $pola));
            })
            ->orderBy('nama')
            ->paginate(12);

        $adaData = CagarBudaya::query()->distinct()->pluck('kategori')->all();

        return view('livewire.publik.daftar-kebudayaan', [
            'judul' => 'Cagar Budaya',
            'deskripsi' => 'Objek Diduga Cagar Budaya (ODCB) dan Cagar Budaya (CB) di Kabupaten Jember.',
            'placeholder' => 'Cari nama atau alamat…',
            'filter' => $this->kategori,
            'aksiFilter' => 'pilihKategori',
            'daftarFilter' => array_values(array_filter(CagarBudaya::KATEGORI, fn ($k) => in_array($k, $adaData, true))),
            'kartu' => $daftar->getCollection()->map(fn (CagarBudaya $c) => [
                'kunci' => 'cb-'.$c->id_cagar_budaya,
                'url' => route('publik.detail-cagar-budaya', $c),
                'gambar' => $c->gambar_url,
                'label' => $c->kategori.' · '.$c->status,
                'nama' => $c->nama,
                'sub' => $c->alamat,
                'cuplikan' => $c->cuplikan,
                'ikon' => '🏛',
            ]),
            'daftar' => $daftar,
        ]);
    }
}
