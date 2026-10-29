<?php

namespace App\Livewire\Admin;

use App\Models\LokasiPeta as LokasiModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Lokasi Peta Wisata lainnya: Restoran & Rumah Makan, Pusat Oleh-oleh, Lainnya. */
#[Layout('layouts.admin')]
#[Title('Lokasi Peta · Admin Disporabudpar Jember')]
class LokasiPeta extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: '')]
    public string $kategori = '';

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updated(): void
    {
        $this->resetPage();
    }

    #[On('lokasi-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = 'ok';
    }

    public function hapus(int $id): void
    {
        $lokasi = LokasiModel::find($id);

        if (! $lokasi) {
            $this->pesan = 'Lokasi tidak ditemukan, mungkin sudah dihapus.';
            $this->jenisPesan = 'err';

            return;
        }

        $lokasi->delete();

        if ($lokasi->gambar) {
            Storage::disk('public')->delete($lokasi->gambar);
        }

        $this->pesan = "Lokasi \"{$lokasi->nama}\" dihapus.";
        $this->jenisPesan = 'ok';
    }

    public function render()
    {
        $daftar = LokasiModel::query()
            ->when(isset(LokasiModel::KATEGORI[$this->kategori]), fn ($q) => $q->where('kategori', $this->kategori))
            ->when(trim($this->cari) !== '', fn ($q) => $q->where('nama', 'like', '%'.addcslashes(trim($this->cari), '%_\\').'%'))
            ->orderBy('nama')
            ->paginate(10);

        return view('livewire.admin.lokasi-peta', ['daftar' => $daftar, 'daftarKategori' => LokasiModel::KATEGORI]);
    }
}
