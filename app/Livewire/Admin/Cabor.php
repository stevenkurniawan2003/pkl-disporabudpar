<?php

namespace App\Livewire\Admin;

use App\Models\Cabor as CaborModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Database Cabor (PPO): grafik jumlah atlet & pelatih per cabor, pencarian nama cabor, Lihat Data, Extract. */
#[Layout('layouts.admin')]
#[Title('Database Cabor · Admin Disporabudpar Jember')]
class Cabor extends Component
{
    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: '')]
    public string $cabor = '';

    public ?int $detailId = null;

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

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
        $this->dispatch('buka-form-cabor', id: $id)->to(CaborForm::class);
    }

    #[On('cabor-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $cabor = CaborModel::find($id);

        if (! $cabor) {
            $this->beriPesan('Data cabor tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $cabor->nama_cabor;

        try {
            $cabor->delete(); // data atlet/pelatih cabor ini tetap ada (cabor disimpan sebagai kode)
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Cabor \"{$nama}\" gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Cabor \"{$nama}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $kata = mb_strtolower(trim($this->cari));

        // Maksimal 50 baris (satu per cabor), jadi disaring & diurutkan di PHP tanpa pagination.
        $daftar = CaborModel::with('kecamatan')->denganJumlah()
            ->when(isset(CaborModel::DAFTAR[$this->cabor]), fn ($q) => $q->where('kode_cabor', $this->cabor))
            ->get()
            ->filter(fn ($c) => $kata === '' || str_contains(mb_strtolower($c->nama_cabor.' '.$c->induk_organisasi), $kata))
            ->sortBy('nama_cabor')
            ->values();

        return view('livewire.admin.cabor', [
            'daftar' => $daftar,
            'daftarCabor' => CaborModel::pilihan(),
            'daftarGrafik' => CaborModel::dataGrafik(),
            'detail' => $this->detailId ? CaborModel::with('kecamatan')->denganJumlah()->find($this->detailId) : null,
        ]);
    }
}
