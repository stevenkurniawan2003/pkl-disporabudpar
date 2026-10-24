<?php

namespace App\Livewire\Admin;

use App\Models\Hotel;
use App\Models\KunjunganHotel as KunjunganModel;
use App\Models\KunjunganWisata;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Data Kunjungan Hotel · Admin Disporabudpar Jember')]
class KunjunganHotel extends Component
{
    use WithPagination;

    #[Url(as: 'tahun', except: '')]
    public string $tahun = '';

    #[Url(as: 'bulan', except: '')]
    public string $bulan = '';

    #[Url(as: 'hotel', except: '')]
    public string $hotel = '';

    /** Data kunjungan yang sedang dibuka di modal detail. */
    public ?int $detailId = null;

    /** Notifikasi di atas tabel. Form ada di modal (satu halaman), jadi tidak memakai session flash. */
    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function updated(string $properti): void
    {
        if (in_array($properti, ['tahun', 'bulan', 'hotel'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->reset('tahun', 'bulan', 'hotel');
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
        $this->dispatch('buka-form-kunjungan-hotel', id: $id)->to(KunjunganHotelForm::class);
    }

    /** Dikirim KunjunganHotelForm setelah tambah/ubah berhasil. */
    #[On('kunjungan-hotel-disimpan')]
    public function setelahSimpan(string $pesan): void
    {
        $this->beriPesan($pesan);
    }

    public function hapus(int $id): void
    {
        $k = KunjunganModel::with('hotel')->find($id);

        if (! $k) {
            $this->beriPesan('Data kunjungan tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $label = "{$k->hotel?->nama_hotel} ({$k->nama_bulan} {$k->tahun})";

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
            ->when($this->hotel !== '', fn ($q) => $q->where('hotel_id', (int) $this->hotel));
    }

    public function render()
    {
        $daftar = $this->queryTersaring()
            ->with('hotel')
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

        return view('livewire.admin.kunjungan-hotel', [
            'daftar' => $daftar,
            'totalDomestik' => (int) $jumlah->domestik,
            'totalMancanegara' => (int) $jumlah->mancanegara,
            'daftarTahun' => $daftarTahun,
            'daftarBulan' => KunjunganWisata::BULAN,
            'daftarHotel' => Hotel::orderBy('nama_hotel')->get(['id_hotel', 'nama_hotel']),
            'detail' => $this->detailId ? KunjunganModel::with(['hotel', 'pencatat'])->find($this->detailId) : null,
        ]);
    }
}
