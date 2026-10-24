<?php

namespace App\Livewire\Admin;

use App\Models\KunjunganWisata as KunjunganModel;
use App\Models\Wisata;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Data Kunjungan Wisata · Admin Disporabudpar Jember')]
class KunjunganWisata extends Component
{
    use WithPagination;

    #[Url(as: 'tahun', except: '')]
    public string $tahun = '';

    #[Url(as: 'bulan', except: '')]
    public string $bulan = '';

    #[Url(as: 'wisata', except: '')]
    public string $wisata = '';

    /** Data kunjungan yang sedang dibuka di modal detail. */
    public ?int $detailId = null;

    /** Notifikasi di atas tabel. Form ada di modal (satu halaman), jadi tidak memakai session flash. */
    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updated(string $properti): void
    {
        if (in_array($properti, ['tahun', 'bulan', 'wisata'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->reset('tahun', 'bulan', 'wisata');
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
        $this->dispatch('buka-form-kunjungan-wisata', id: $id)->to(KunjunganWisataForm::class);
    }

    /** Dikirim KunjunganWisataForm setelah tambah/ubah berhasil. */
    #[On('kunjungan-wisata-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $k = KunjunganModel::with('wisata')->find($id);

        if (! $k) {
            $this->beriPesan('Data kunjungan tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $label = "{$k->wisata?->nama} ({$k->nama_bulan} {$k->tahun})";

        try {
            $k->delete();
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan("Data kunjungan {$label} gagal dihapus. Silakan coba lagi.", 'err');

            return;
        }

        if ($this->detailId === $id) {
            $this->detailId = null;
        }

        $this->beriPesan("Data kunjungan {$label} dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    /** Query yang sudah difilter; dipakai untuk tabel dan ringkasan total supaya angkanya selalu sama. */
    private function queryTersaring(): Builder
    {
        return KunjunganModel::query()
            ->when($this->tahun !== '', fn ($q) => $q->where('tahun', (int) $this->tahun))
            ->when($this->bulan !== '', fn ($q) => $q->where('bulan', (int) $this->bulan))
            ->when($this->wisata !== '', fn ($q) => $q->where('wisata_id', (int) $this->wisata));
    }

    public function render()
    {
        $daftar = $this->queryTersaring()
            ->with('wisata')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('id')
            ->paginate(10);

        $jumlah = $this->queryTersaring()
            ->selectRaw('COALESCE(SUM(domestik), 0) AS domestik, COALESCE(SUM(mancanegara), 0) AS mancanegara')
            ->toBase()
            ->first();

        // Pilihan tahun: tahun yang sudah punya data + tahun berjalan.
        $daftarTahun = KunjunganModel::query()->distinct()->pluck('tahun')
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('livewire.admin.kunjungan-wisata', [
            'daftar' => $daftar,
            'totalDomestik' => (int) $jumlah->domestik,
            'totalMancanegara' => (int) $jumlah->mancanegara,
            'daftarTahun' => $daftarTahun,
            'daftarBulan' => KunjunganModel::BULAN,
            'daftarWisata' => Wisata::orderBy('nama')->get(['id', 'nama']),
            'detail' => $this->detailId ? KunjunganModel::with(['wisata.kategori', 'wisata.kecamatan', 'pencatat'])->find($this->detailId) : null,
        ]);
    }
}
