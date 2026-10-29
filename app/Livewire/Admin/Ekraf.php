<?php

namespace App\Livewire\Admin;

use App\Models\Ekraf as EkrafModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Data Master EKRAF: pelaku ekonomi kreatif Jember, ringkasan aktif/non aktif, dan grafiknya. */
#[Layout('layouts.admin')]
#[Title('Data Master EKRAF · Admin Disporabudpar Jember')]
class Ekraf extends Component
{
    use WithPagination;

    /** Kolom yang dicari (pilihan di sebelah kotak pencarian). */
    public const KOLOM_CARI = [
        'nama_usaha' => 'Nama Usaha/Pelaku',
        'nomor_ekraf' => 'ID Usaha/Pelaku',
        'alamat' => 'Alamat',
    ];

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(as: 'kolom', except: 'nama_usaha')]
    public string $kolom = 'nama_usaha';

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
            $this->kolom = 'nama_usaha';
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
        $this->dispatch('buka-form-ekraf', id: $id)->to(EkrafForm::class);
    }

    #[On('ekraf-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $ekraf = EkrafModel::find($id);

        if (! $ekraf) {
            $this->beriPesan('Data pelaku ekraf tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $nama = $ekraf->nama_usaha;

        try {
            $ekraf->delete();
        } catch (\Throwable $e) {
            report($e);
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
        $kolom = isset(self::KOLOM_CARI[$this->kolom]) ? $this->kolom : 'nama_usaha';

        $daftar = EkrafModel::query()
            ->with(['kecamatan', 'desa'])
            ->when(trim($this->cari) !== '', fn ($q) => $q->where($kolom, 'like', '%'.addcslashes(trim($this->cari), '%_\\').'%'))
            ->latest('id_ekraf')
            ->paginate(10);

        $perStatus = EkrafModel::query()->selectRaw('status, COUNT(*) AS jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return view('livewire.admin.ekraf', [
            'daftar' => $daftar,
            'kolomCari' => self::KOLOM_CARI,
            'jumlahAktif' => (int) ($perStatus['aktif'] ?? 0),
            'jumlahNonaktif' => (int) ($perStatus['nonaktif'] ?? 0),
            'daftarGrafik' => EkrafModel::dataGrafik(),
            'detail' => $this->detailId ? EkrafModel::with(['kecamatan', 'desa'])->find($this->detailId) : null,
        ]);
    }
}
