<?php

namespace App\Livewire\Admin;

use App\Models\Opk as OpkModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Objek Pemajuan Kebudayaan · Admin Disporabudpar Jember')]
class Opk extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(as: 'jenis', except: '')]
    public string $jenis = '';

    public ?int $detailId = null;

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedJenis(): void
    {
        $this->resetPage();
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
        $this->dispatch('buka-form-opk', id: $id)->to(OpkForm::class);
    }

    #[On('opk-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $opk = OpkModel::with('galeri')->find($id);

        if (! $opk) {
            $this->beriPesan('Data OPK tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $opk->nama;
        $file = $opk->galeri->pluck('gambar')->push($opk->gambar_utama)->filter()->all();

        try {
            $opk->delete(); // galeri_opk ikut terhapus lewat foreign key cascade
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("OPK \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        Storage::disk('public')->delete($file);

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("OPK \"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $daftar = OpkModel::query()
            ->with('pencatat')
            ->when($this->jenis !== '', fn ($q) => $q->where('jenis', $this->jenis))
            ->when(trim($this->cari) !== '', function ($q) {
                $kata = '%'.addcslashes(trim($this->cari), '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $kata)->orWhere('lokasi_persebaran', 'like', $kata)->orWhere('pelaku_budaya', 'like', $kata));
            })
            ->latest('id_opk')
            ->paginate(10);

        return view('livewire.admin.opk', [
            'daftar' => $daftar,
            'daftarJenis' => OpkModel::JENIS,
            'detail' => $this->detailId ? OpkModel::with(['galeri', 'pencatat'])->find($this->detailId) : null,
        ]);
    }
}
