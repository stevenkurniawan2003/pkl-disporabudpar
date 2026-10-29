<?php

namespace App\Livewire\Admin;

use App\Models\CagarBudaya as CagarBudayaModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Cagar Budaya · Admin Disporabudpar Jember')]
class CagarBudaya extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(as: 'kategori', except: '')]
    public string $kategori = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public ?int $detailId = null;

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updated(string $properti): void
    {
        if (in_array($properti, ['cari', 'kategori', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function lihatDetail(int $id): void
    {
        $this->detailId = $id;
    }

    public function tutupDetail(): void
    {
        $this->detailId = null;
    }

    public function ubahDariDetail(): void
    {
        $id = $this->detailId;
        $this->detailId = null;
        $this->dispatch('buka-form-cagar-budaya', id: $id)->to(CagarBudayaForm::class);
    }

    #[On('cagar-budaya-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $cb = CagarBudayaModel::with('galeri')->find($id);

        if (! $cb) {
            $this->beriPesan('Data ODCB/CB tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $cb->nama;
        $file = $cb->galeri->pluck('gambar')->push($cb->gambar_utama)->filter()->all();

        try {
            $cb->delete(); // galeri_cagar_budaya ikut terhapus lewat foreign key cascade
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("\"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        Storage::disk('public')->delete($file);

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("\"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $daftar = CagarBudayaModel::query()
            ->when($this->kategori !== '', fn ($q) => $q->where('kategori', $this->kategori))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when(trim($this->cari) !== '', function ($q) {
                $kata = '%'.addcslashes(trim($this->cari), '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $kata)->orWhere('alamat', 'like', $kata));
            })
            ->latest('id_cagar_budaya')
            ->paginate(10);

        return view('livewire.admin.cagar-budaya', [
            'daftar' => $daftar,
            'daftarKategori' => CagarBudayaModel::KATEGORI,
            'daftarStatus' => CagarBudayaModel::STATUS,
            'detail' => $this->detailId ? CagarBudayaModel::with(['galeri', 'pencatat'])->find($this->detailId) : null,
        ]);
    }
}
