<?php

namespace App\Livewire\Admin;

use App\Models\Kesenian as KesenianModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Kesenian · Admin Disporabudpar Jember')]
class Kesenian extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(as: 'kategori', except: '')]
    public string $kategori = '';

    /** Kesenian yang sedang dibuka di modal detail. */
    public ?int $detailId = null;

    /** Notifikasi di atas tabel. Form ada di modal (satu halaman), jadi tidak memakai session flash. */
    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedKategori(): void
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
        $this->dispatch('buka-form-kesenian', id: $id)->to(KesenianForm::class);
    }

    /** Dikirim KesenianForm setelah tambah/ubah berhasil. */
    #[On('kesenian-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $k = KesenianModel::find($id);

        if (! $k) {
            $this->beriPesan('Kesenian tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $k->nama_kesenian;

        try {
            $k->delete();
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Kesenian \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Kesenian \"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $daftar = KesenianModel::query()
            ->when($this->cari !== '', function ($q) {
                $kata = '%'.trim($this->cari).'%';
                $q->where(fn ($w) => $w->where('nama_kesenian', 'like', $kata)->orWhere('asal_daerah', 'like', $kata));
            })
            ->when($this->kategori !== '', fn ($q) => $q->where('kategori', $this->kategori))
            ->latest('id_kesenian')
            ->paginate(10);

        return view('livewire.admin.kesenian', [
            'daftar' => $daftar,
            'daftarKategori' => KesenianModel::KATEGORI,
            'detail' => $this->detailId ? KesenianModel::find($this->detailId) : null,
        ]);
    }
}
