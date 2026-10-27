<?php

namespace App\Livewire\Publik;

use App\Models\Hotel;
use App\Models\KategoriWisata;
use App\Models\Wisata;
use App\Support\TeksKaya;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.publik')]
#[Title('Peta Wisata · Disporabudpar Jember')]
class PetaWisata extends Component
{
    public const JENIS = ['semua', 'wisata', 'hotel'];

    /** Tampilkan wisata, hotel, atau keduanya. */
    #[Url(as: 'tampil', except: 'semua')]
    public string $jenis = 'semua';

    /** Filter kategori, hanya berlaku untuk wisata. */
    #[Url(as: 'kategori', except: 'Semua')]
    public string $kategori = 'Semua';

    /** Wisata yang langsung dibuka popup-nya (dipakai tautan dari beranda: ?dipilih=ID). */
    #[Url(as: 'dipilih')]
    public ?int $dipilih = null;

    public function pilihJenis(string $jenis): void
    {
        $this->jenis = in_array($jenis, self::JENIS, true) ? $jenis : 'semua';
        $this->kirimKePeta();
    }

    public function pilihKategori(string $nama): void
    {
        $this->kategori = $nama;
        $this->kirimKePeta();
    }

    private function tampilWisata(): bool
    {
        return $this->jenis !== 'hotel';
    }

    private function tampilHotel(): bool
    {
        return $this->jenis !== 'wisata' && $this->kategori === 'Semua';
    }

    /** Wisata tayang dengan koordinat valid, difilter kategori. */
    private function daftarWisata(): Collection
    {
        if (! $this->tampilWisata()) {
            return collect();
        }

        return Wisata::with(['kategori', 'kecamatan'])
            ->tayang()
            ->when($this->kategori !== 'Semua', fn ($q) => $q->whereHas('kategori', fn ($k) => $k->where('nama', $this->kategori)))
            ->orderBy('nama')
            ->get()
            ->filter(fn (Wisata $w) => self::koordinatValid($w->latitude, $w->longitude))
            ->values();
    }

    /** Hotel dengan koordinat valid. Hotel tidak punya kategori wisata, jadi disembunyikan saat filter kategori aktif. */
    private function daftarHotel(): Collection
    {
        if (! $this->tampilHotel()) {
            return collect();
        }

        return Hotel::orderBy('nama_hotel')
            ->get()
            ->filter(fn (Hotel $h) => self::koordinatValid($h->latitude, $h->longitude))
            ->values();
    }

    public static function koordinatValid(mixed $lat, mixed $lng): bool
    {
        return is_numeric($lat) && is_numeric($lng)
            && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180
            && ! ((float) $lat === 0.0 && (float) $lng === 0.0);
    }

    /**
     * Data minimal untuk JavaScript Leaflet (marker + isi popup). Teks dimasukkan ke popup
     * lewat textContent di JavaScript, jadi aman dari HTML yang diketik admin.
     */
    private function titikPeta(Collection $wisata, Collection $hotel): array
    {
        $titikWisata = $wisata->map(fn (Wisata $w) => [
            'kunci' => 'w-'.$w->id,
            'jenis' => 'wisata',
            'nama' => $w->nama,
            'lat' => (float) $w->latitude,
            'lng' => (float) $w->longitude,
            'label' => $w->kategori?->nama ?? 'Wisata',
            'gambar' => $w->gambar_url,
            'ikon' => $w->kategori?->ikon ?? '📍',
            'teks' => TeksKaya::polos($w->narasi ?: (string) $w->deskripsi_singkat, 140),
            'url' => route('publik.detail-wisata', $w),
        ]);

        $titikHotel = $hotel->map(fn (Hotel $h) => [
            'kunci' => 'h-'.$h->id_hotel,
            'jenis' => 'hotel',
            'nama' => $h->nama_hotel,
            'lat' => (float) $h->latitude,
            'lng' => (float) $h->longitude,
            'label' => 'Hotel',
            'gambar' => $h->gambar_url,
            'ikon' => '🏨',
            'teks' => $h->deskripsi_singkat,
            'url' => route('publik.detail-hotel', $h),
        ]);

        return $titikWisata->concat($titikHotel)->values()->all();
    }

    /** Kabari peta (JavaScript) bahwa daftar penanda berubah. */
    private function kirimKePeta(): void
    {
        $this->dispatch('peta-diperbarui', titik: $this->titikPeta($this->daftarWisata(), $this->daftarHotel()));
    }

    public function render()
    {
        $wisata = $this->daftarWisata();
        $hotel = $this->daftarHotel();

        return view('livewire.publik.peta-wisata', [
            'daftarKategori' => KategoriWisata::whereHas('wisata', fn ($q) => $q->tayang())->orderBy('nama')->get(),
            'wisata' => $wisata,
            'hotel' => $hotel,
            'titik' => $this->titikPeta($wisata, $hotel),
        ]);
    }
}
