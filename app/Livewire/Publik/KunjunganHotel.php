<?php

namespace App\Livewire\Publik;

use App\Models\Hotel;
use App\Models\KunjunganHotel as KunjunganModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.publik')]
class KunjunganHotel extends HalamanKunjungan
{
    #[Url(as: 'tahun', except: 0)]
    public int $tahun = 0;

    #[Url(as: 'hotel', except: 0)]
    public int $pilihan = 0;

    /** Hotel tidak punya status/soft delete; data kunjungan hotel yang dihapus ikut terhapus (cascade). */
    protected function queryPublik(): Builder
    {
        return KunjunganModel::query();
    }

    protected function kolomTempat(): string
    {
        return 'hotel_id';
    }

    protected function daftarPilihan(): Collection
    {
        return Hotel::orderBy('nama_hotel')->get(['id_hotel', 'nama_hotel'])
            ->map(fn (Hotel $h) => (object) ['id' => $h->id_hotel, 'nama' => $h->nama_hotel]);
    }

    protected function rekapPerTempat(Builder $query): Collection
    {
        return $query
            ->join('hotel', 'hotel.id_hotel', '=', 'kunjungan_hotel.hotel_id')
            ->groupBy('hotel.id_hotel', 'hotel.nama_hotel')
            ->selectRaw('hotel.id_hotel, hotel.nama_hotel, SUM(domestik) AS domestik, SUM(mancanegara) AS mancanegara, SUM(domestik + mancanegara) AS total')
            ->orderByDesc('total')
            ->orderBy('hotel.nama_hotel')
            ->toBase()
            ->get()
            ->map(fn ($r) => (object) [
                'nama' => $r->nama_hotel,
                'url' => route('publik.detail-hotel', $r->id_hotel),
                'domestik' => (int) $r->domestik,
                'mancanegara' => (int) $r->mancanegara,
                'total' => (int) $r->total,
            ]);
    }

    protected function teks(): array
    {
        return [
            'judul' => 'Data kunjungan hotel',
            'deskripsi' => 'Per hotel, per bulan, tamu domestik dan mancanegara.',
            'semua' => 'Semua hotel',
            'kolom' => 'Hotel',
            'orang' => 'Tamu',
            'title' => 'Data Kunjungan Hotel · Disporabudpar Jember',
        ];
    }
}
