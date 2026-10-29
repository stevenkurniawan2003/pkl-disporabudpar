<?php

namespace App\Livewire\Admin;

use App\Models\Cabor;
use App\Models\Pelatih as PelatihModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Database Pelatih (PPO): grafik jumlah pelatih, filter cabor, pencarian nama/cabor, Lihat Data, Extract. */
#[Layout('layouts.admin')]
#[Title('Database Pelatih · Admin Disporabudpar Jember')]
class Pelatih extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: '')]
    public string $cabor = '';

    public ?int $detailId = null;

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updated(string $properti): void
    {
        if (in_array($properti, ['cari', 'cabor'], true)) {
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
        $this->dispatch('buka-form-pelatih', id: $id)->to(PelatihForm::class);
    }

    #[On('pelatih-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $pelatih = PelatihModel::find($id);

        if (! $pelatih) {
            $this->beriPesan('Data pelatih tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $pelatih->nama_lengkap;

        try {
            $pelatih->delete();
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Pelatih \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        // Berkas baru dihapus setelah datanya pasti terhapus.
        if ($pelatih->file_lisensi) {
            Storage::disk('public')->delete($pelatih->file_lisensi);
        }

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Pelatih \"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $daftar = PelatihModel::with('kecamatan')
            ->filter(['cabor' => isset(Cabor::DAFTAR[$this->cabor]) ? $this->cabor : '', 'cari' => $this->cari])
            ->orderBy('nama_lengkap')
            ->paginate(10);

        return view('livewire.admin.pelatih', [
            'daftar' => $daftar,
            'daftarCabor' => Cabor::pilihan(),
            'daftarGrafik' => PelatihModel::dataGrafik(),
            'detail' => $this->detailId ? PelatihModel::with('kecamatan')->find($this->detailId) : null,
        ]);
    }
}
