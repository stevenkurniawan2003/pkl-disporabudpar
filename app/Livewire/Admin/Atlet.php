<?php

namespace App\Livewire\Admin;

use App\Models\Atlet as AtletModel;
use App\Models\Cabor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Database Atlet (PPO): grafik jumlah atlet, filter cabor, pencarian nama/cabor, Lihat Data, Extract. */
#[Layout('layouts.admin')]
#[Title('Database Atlet · Admin Disporabudpar Jember')]
class Atlet extends Component
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
        $this->dispatch('buka-form-atlet', id: $id)->to(AtletForm::class);
    }

    #[On('atlet-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $atlet = AtletModel::find($id);

        if (! $atlet) {
            $this->beriPesan('Data atlet tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $atlet->nama_lengkap;

        try {
            $atlet->delete();
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Atlet \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Atlet \"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $daftar = AtletModel::with('kecamatan')
            ->filter(['cabor' => isset(Cabor::DAFTAR[$this->cabor]) ? $this->cabor : '', 'cari' => $this->cari])
            ->orderBy('nama_lengkap')
            ->paginate(10);

        return view('livewire.admin.atlet', [
            'daftar' => $daftar,
            'daftarCabor' => Cabor::pilihan(),
            'daftarGrafik' => AtletModel::dataGrafik(),
            'detail' => $this->detailId ? AtletModel::with('kecamatan')->find($this->detailId) : null,
        ]);
    }
}
