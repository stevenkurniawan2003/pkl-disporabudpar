<?php

namespace App\Livewire\Admin;

use App\Models\Wisata as WisataModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Wisata · Admin Disporabudpar Jember')]
class Wisata extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    /** Wisata yang sedang dibuka di modal detail. */
    public ?int $detailId = null;

    /** Notifikasi di atas tabel. Form ada di modal (satu halaman), jadi tidak memakai session flash. */
    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updatedCari(): void
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

    /** Dari modal detail langsung ke modal ubah. */
    public function ubahDariDetail(): void
    {
        $id = $this->detailId;
        $this->detailId = null;
        $this->dispatch('buka-form-wisata', id: $id)->to(WisataForm::class);
    }

    /** Dikirim WisataForm setelah tambah/ubah berhasil. */
    #[On('wisata-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function toggleUnggulan(int $id): void
    {
        $w = WisataModel::find($id);

        if (! $w) {
            $this->beriPesan('Wisata tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $w->update(['unggulan' => ! $w->unggulan]);
    }

    public function hapus(int $id): void
    {
        $w = WisataModel::find($id);

        if (! $w) {
            $this->beriPesan('Wisata tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $w->nama;

        try {
            $w->delete(); // soft delete: hilang dari admin dan publik, data dan gambar masih disimpan
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Wisata \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Wisata \"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $wisata = WisataModel::with(['kategori', 'kecamatan'])
            ->withCount('galeri')
            ->when($this->cari !== '', function ($q) {
                $kata = '%'.trim($this->cari).'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $kata)->orWhere('alamat', 'like', $kata));
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.admin.wisata', [
            'daftar' => $wisata,
            'detail' => $this->detailId ? WisataModel::with(['kategori', 'kecamatan', 'galeri'])->find($this->detailId) : null,
        ]);
    }
}
