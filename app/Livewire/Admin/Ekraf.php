<?php

namespace App\Livewire\Admin;

use App\Models\Ekraf as EkrafModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Ekonomi Kreatif · Admin Disporabudpar Jember')]
class Ekraf extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(as: 'subsektor', except: '')]
    public string $subsektor = '';

    /** Pelaku ekraf yang sedang dibuka di modal detail. */
    public ?int $detailId = null;

    /** Notifikasi di atas tabel. Form ada di modal (satu halaman), jadi tidak memakai session flash. */
    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedSubsektor(): void
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
        $this->dispatch('buka-form-ekraf', id: $id)->to(EkrafForm::class);
    }

    /** Dikirim EkrafForm setelah tambah/ubah berhasil. */
    #[On('ekraf-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $e = EkrafModel::find($id);

        if (! $e) {
            $this->beriPesan('Data ekraf tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $e->nama_usaha;

        try {
            $e->delete();
        } catch (\Throwable $ex) {
            report($ex);
            $this->beriPesan("Data \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Data \"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $daftar = EkrafModel::query()
            ->when($this->cari !== '', function ($q) {
                $kata = '%'.trim($this->cari).'%';
                $q->where(fn ($w) => $w->where('nama_usaha', 'like', $kata)
                    ->orWhere('nama_pemilik', 'like', $kata)
                    ->orWhere('alamat', 'like', $kata));
            })
            ->when($this->subsektor !== '', fn ($q) => $q->where('subsektor', $this->subsektor))
            ->latest('id_ekraf')
            ->paginate(10);

        return view('livewire.admin.ekraf', [
            'daftar' => $daftar,
            'daftarSubsektor' => EkrafModel::SUBSEKTOR,
            'detail' => $this->detailId ? EkrafModel::find($this->detailId) : null,
        ]);
    }
}
