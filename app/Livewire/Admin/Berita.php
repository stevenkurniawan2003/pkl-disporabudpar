<?php

namespace App\Livewire\Admin;

use App\Models\Berita as BeritaModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Berita · Admin Disporabudpar Jember')]
class Berita extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    /** Berita yang sedang dibuka di modal detail. */
    public ?int $detailId = null;

    /** Notifikasi di atas tabel. Form ada di modal (satu halaman), jadi tidak memakai session flash. */
    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
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
        $this->dispatch('buka-form-berita', id: $id)->to(BeritaForm::class);
    }

    /** Dikirim BeritaForm setelah tulis/ubah berhasil. */
    #[On('berita-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $b = BeritaModel::find($id);

        if (! $b) {
            $this->beriPesan('Berita tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $judul = $b->judul;

        try {
            $b->delete(); // soft delete: hilang dari admin dan publik, data dan gambar masih disimpan
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Berita \"{$judul}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Berita \"{$judul}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $daftar = BeritaModel::with('kategori')
            ->when($this->cari !== '', fn ($q) => $q->where('judul', 'like', '%'.trim($this->cari).'%'))
            ->when(in_array($this->status, ['terbit', 'draf'], true), fn ($q) => $q->where('status', $this->status))
            ->latest('id')
            ->paginate(10);

        return view('livewire.admin.berita', [
            'daftar' => $daftar,
            'detail' => $this->detailId ? BeritaModel::with(['kategori', 'penulis'])->find($this->detailId) : null,
        ]);
    }
}
