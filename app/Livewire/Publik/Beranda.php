<?php

namespace App\Livewire\Publik;

use App\Models\Berita;
use App\Models\KunjunganHotel;
use App\Models\KunjunganWisata;
use App\Models\Wisata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.publik')]
#[Title('Disporabudpar Jember')]
class Beranda extends Component
{
    public const KUNCI_CACHE_KUNJUNGAN = 'beranda_ringkasan_kunjungan';

    /**
     * Total kunjungan untuk pita statistik: tahun berjalan kalau sudah ada datanya,
     * kalau belum, tahun terbaru yang punya data. Di-cache karena beranda paling sering dibuka.
     *
     * @return array{wisata: ?array, hotel: ?array}
     */
    public static function ringkasanKunjungan(): array
    {
        return Cache::remember(self::KUNCI_CACHE_KUNJUNGAN, 300, fn () => [
            'wisata' => self::periodeTerakhir(KunjunganWisata::query()->tampilPublik()),
            'hotel' => self::periodeTerakhir(KunjunganHotel::query()),
        ]);
    }

    /** @return array{tahun: int, bulan_akhir: int, total: int}|null null kalau belum ada data sama sekali */
    private static function periodeTerakhir(Builder $query): ?array
    {
        $tahun = (clone $query)->where('tahun', '<=', now()->year)->max('tahun');

        if (! $tahun) {
            return null;
        }

        $baris = (clone $query)
            ->where('tahun', $tahun)
            ->selectRaw('MAX(bulan) AS bulan_akhir, COALESCE(SUM(domestik + mancanegara), 0) AS total')
            ->toBase()
            ->first();

        return ['tahun' => (int) $tahun, 'bulan_akhir' => (int) $baris->bulan_akhir, 'total' => (int) $baris->total];
    }

    public function render()
    {
        $wisata = fn () => Wisata::with(['kategori', 'kecamatan'])->tayang()->latest('id')->limit(4);

        // Wisata tayang yang ditandai unggulan oleh admin. Kalau belum ada yang ditandai, pakai wisata terbaru.
        $unggulan = $wisata()->unggulan()->get();
        $cadangan = $unggulan->isEmpty();

        return view('livewire.publik.beranda', [
            'unggulan' => $cadangan ? $wisata()->get() : $unggulan,
            'unggulanCadangan' => $cadangan,

            // Berita terbit (tanggal terbit sudah tiba), 6 terbaru: 2 pertama jadi kartu utama, sisanya kartu biasa.
            'berita' => Berita::with('kategori')
                ->terbit()
                ->latest('tanggal_terbit')
                ->latest('id')
                ->limit(6)
                ->get(),

            'kunjungan' => self::ringkasanKunjungan(),
            'namaBulan' => KunjunganWisata::BULAN,
        ]);
    }
}
