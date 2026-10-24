<?php

namespace App\Livewire\Admin;

use App\Models\Hotel as HotelModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Hotel · Admin Disporabudpar Jember')]
class Hotel extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    /** Hotel yang sedang dibuka di modal detail. */
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
        $this->dispatch('buka-form-hotel', id: $id)->to(HotelForm::class);
    }

    /** Dikirim HotelForm setelah tambah/ubah berhasil. */
    #[On('hotel-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $hotel = HotelModel::with('galeri')->find($id);

        if (! $hotel) {
            $this->beriPesan('Hotel tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $hotel->nama_hotel;
        $file = $hotel->galeri->pluck('gambar')->push($hotel->gambar_utama)->filter()->all();

        try {
            $hotel->delete(); // galeri_hotel ikut terhapus lewat foreign key cascade
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Hotel \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        // File baru dihapus setelah datanya pasti terhapus.
        Storage::disk('public')->delete($file);

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Hotel \"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $daftar = HotelModel::query()
            ->when($this->cari !== '', function ($q) {
                $kata = '%'.trim($this->cari).'%';
                $q->where(fn ($w) => $w->where('nama_hotel', 'like', $kata)->orWhere('alamat', 'like', $kata));
            })
            ->latest('id_hotel')
            ->paginate(10);

        return view('livewire.admin.hotel', [
            'daftar' => $daftar,
            'detail' => $this->detailId ? HotelModel::with('galeri')->find($this->detailId) : null,
        ]);
    }
}
