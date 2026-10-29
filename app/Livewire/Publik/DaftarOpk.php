<?php

namespace App\Livewire\Publik;

use App\Models\Opk;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.publik')]
#[Title('Objek Pemajuan Kebudayaan · Disporabudpar Jember')]
class DaftarOpk extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: 'Semua')]
    public string $jenis = 'Semua';

    public function pilihJenis(string $jenis): void
    {
        $this->jenis = in_array($jenis, Opk::JENIS, true) ? $jenis : 'Semua';
        $this->resetPage();
    }

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $kata = trim($this->cari);

        $daftar = Opk::query()
            ->when($this->jenis !== 'Semua', fn ($q) => $q->where('jenis', $this->jenis))
            ->when($kata !== '', function ($q) use ($kata) {
                $pola = '%'.addcslashes($kata, '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $pola)->orWhere('lokasi_persebaran', 'like', $pola));
            })
            ->orderBy('nama')
            ->paginate(12);

        $adaData = Opk::query()->distinct()->pluck('jenis')->all();

        return view('livewire.publik.daftar-kebudayaan', [
            'judul' => 'Objek Pemajuan Kebudayaan',
            'deskripsi' => 'Data digital Objek Pemajuan Kebudayaan (OPK) yang ada di Kabupaten Jember.',
            'placeholder' => 'Cari nama OPK atau lokasi persebaran…',
            'filter' => $this->jenis,
            'aksiFilter' => 'pilihJenis',
            'daftarFilter' => array_values(array_filter(Opk::JENIS, fn ($j) => in_array($j, $adaData, true))),
            'kartu' => $daftar->getCollection()->map(fn (Opk $o) => [
                'kunci' => 'opk-'.$o->id_opk,
                'url' => route('publik.detail-opk', $o),
                'gambar' => $o->gambar_url,
                'label' => $o->jenis,
                'nama' => $o->nama,
                'sub' => $o->lokasi_persebaran,
                'cuplikan' => $o->cuplikan,
                'ikon' => '🏺',
            ]),
            'daftar' => $daftar,
        ]);
    }
}
