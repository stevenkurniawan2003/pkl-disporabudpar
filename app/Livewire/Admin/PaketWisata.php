<?php

namespace App\Livewire\Admin;

use App\Models\PaketWisata as PaketModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Paket wisata Jember beserta flyer dan CP. */
#[Layout('layouts.admin')]
#[Title('Paket Wisata · Admin Disporabudpar Jember')]
class PaketWisata extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    #[On('paket-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = 'ok';
    }

    public function hapus(int $id): void
    {
        $paket = PaketModel::find($id);

        if (! $paket) {
            $this->pesan = 'Paket tidak ditemukan, mungkin sudah dihapus.';
            $this->jenisPesan = 'err';

            return;
        }

        $paket->delete();
        Storage::disk('public')->delete($paket->flyer);

        $this->pesan = "Paket \"{$paket->nama_paket}\" dihapus.";
        $this->jenisPesan = 'ok';
    }

    public function render()
    {
        return view('livewire.admin.paket-wisata', [
            'daftar' => PaketModel::query()
                ->when(trim($this->cari) !== '', fn ($q) => $q->where('nama_paket', 'like', '%'.addcslashes(trim($this->cari), '%_\\').'%'))
                ->latest('id_paket')
                ->paginate(10),
        ]);
    }
}
