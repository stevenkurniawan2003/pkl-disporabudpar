<?php

namespace App\Livewire\Publik;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kesenian;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar Kartu Induk Kesenian untuk publik. Nama pemilik, alamat rumah, dan nomor telepon tidak ditampilkan
 * (data pribadi); publik cukup melihat kelompok, jenis, wilayah, jumlah anggota, dan status.
 */
#[Layout('layouts.publik')]
#[Title('Data Kesenian · Disporabudpar Jember')]
class DataKesenian extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: '')]
    public string $kecamatan = '';

    #[Url(except: '')]
    public string $desa = '';

    #[Url(except: '')]
    public string $jenis = '';

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

        $daftar = Kesenian::query()
            ->with(['kecamatan', 'desa'])
            ->filterCetak([
                'kecamatan' => $this->kecamatan,
                'desa' => $this->desa,
                'jenis' => isset(Kesenian::JENIS[$this->jenis]) ? $this->jenis : '',
            ])
            ->when($kata !== '', function ($q) use ($kata) {
                $pola = '%'.addcslashes($kata, '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama_kesenian', 'like', $pola)->orWhere('nomor_induk', 'like', $pola));
            })
            ->orderBy('kode_jenis')
            ->orderBy('nomor_urut')
            ->paginate(15);

        return view('livewire.publik.data-kesenian', [
            'daftar' => $daftar,
            'daftarKecamatan' => Kecamatan::orderBy('nama')->get(['id', 'nama']),
            'daftarDesa' => $this->kecamatan !== '' ? Desa::where('kecamatan_id', (int) $this->kecamatan)->orderBy('nama')->get(['id', 'nama']) : collect(),
            'daftarJenis' => Kesenian::JENIS,
        ]);
    }
}
