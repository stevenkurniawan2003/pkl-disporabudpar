<?php

namespace App\Livewire\Admin;

use App\Models\Kesenian as KesenianModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Data Master Kesenian (Kartu Induk Kesenian): sanggar/komunitas/lembaga kebudayaan. */
#[Layout('layouts.admin')]
#[Title('Data Master Kesenian · Admin Disporabudpar Jember')]
class Kesenian extends Component
{
    use WithPagination;

    /** Kolom yang dicari (pilihan di sebelah kotak pencarian). */
    public const KOLOM_CARI = [
        'nama_kesenian' => 'Nama Kesenian',
        'nama_pemilik' => 'Nama Pemilik',
        'nomor_induk' => 'Nomor Induk',
    ];

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(as: 'kolom', except: 'nama_kesenian')]
    public string $kolom = 'nama_kesenian';

    public ?int $detailId = null;

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    public function updatedKolom(): void
    {
        if (! isset(self::KOLOM_CARI[$this->kolom])) {
            $this->kolom = 'nama_kesenian';
        }

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
        $this->dispatch('buka-form-kesenian', id: $id)->to(KesenianForm::class);
    }

    #[On('kesenian-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $kesenian = KesenianModel::with('perpanjangan')->find($id);

        if (! $kesenian) {
            $this->beriPesan('Data kesenian tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $kesenian->nama_kesenian;
        $file = $kesenian->perpanjangan->pluck('foto_ketua')->push($kesenian->dokumentasi)->filter()->all();

        try {
            $kesenian->delete(); // riwayat perpanjangan_kik ikut terhapus lewat foreign key cascade
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Kesenian \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        Storage::disk('public')->delete($file);

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
        $kolom = isset(self::KOLOM_CARI[$this->kolom]) ? $this->kolom : 'nama_kesenian';

        $daftar = KesenianModel::query()
            ->with(['kecamatan', 'desa'])
            ->when(trim($this->cari) !== '', fn ($q) => $q->where($kolom, 'like', '%'.addcslashes(trim($this->cari), '%_\\').'%'))
            ->orderBy('kode_jenis')
            ->orderBy('nomor_urut')
            ->paginate(10);

        return view('livewire.admin.kesenian', [
            'daftar' => $daftar,
            'kolomCari' => self::KOLOM_CARI,
            'detail' => $this->detailId ? KesenianModel::with(['kecamatan', 'desa', 'perpanjangan'])->find($this->detailId) : null,
        ]);
    }
}
