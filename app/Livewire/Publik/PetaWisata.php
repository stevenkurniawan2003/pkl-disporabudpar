<?php

namespace App\Livewire\Publik;

use App\Models\Cabor;
use App\Models\CagarBudaya;
use App\Models\Ekraf;
use App\Models\Hotel;
use App\Models\KategoriWisata;
use App\Models\Kesenian;
use App\Models\LokasiPeta;
use App\Models\PaketWisata;
use App\Models\Wisata;
use App\Support\TeksKaya;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Peta Wisata (dokumen konsep Pariwisata poin 1):
 * 1. Peta Wisata berlapis kategori (centang untuk menampilkan/menyembunyikan, plus pencarian nama) — data dikirim
 *    sekali, penyaringan dilakukan di JavaScript tanpa request ke server.
 * 2. Peta Obyek Wisata: daftar + peta semua obyek wisata, dengan filter kategori wisata.
 * 3. Saran dokumen: obyek wisata terbaru dan paket wisata terbaru.
 */
#[Layout('layouts.publik')]
#[Title('Peta Wisata · Disporabudpar Jember')]
class PetaWisata extends Component
{
    /** Lapisan peta: kunci => [label, ikon Bootstrap Icons]. Warnanya ada di publik.css (.pin-peta.<kunci>). */
    public const LAPISAN = [
        'desa-wisata' => ['Desa Wisata', 'bi-house-heart-fill'],
        'ekraf' => ['Ekonomi Kreatif', 'bi-lightbulb-fill'],
        'hotel' => ['Hotel & Penginapan', 'bi-building'],
        'objek-wisata' => ['Obyek Wisata', 'bi-tree-fill'],
        'restoran' => ['Restoran & Rumah Makan', 'bi-cup-hot-fill'],
        'seni-budaya' => ['Seni Budaya', 'bi-music-note-beamed'],
        'situs-budaya' => ['Situs Budaya', 'bi-bank2'],
        'oleh-oleh' => ['Pusat Oleh-oleh', 'bi-bag-heart-fill'],
        'cabor' => ['Sekretariat Cabor', 'bi-trophy-fill'],
        'lainnya' => ['Lainnya', 'bi-geo-alt-fill'],
    ];

    /** Filter kategori Peta Obyek Wisata (bagian kedua). */
    #[Url(as: 'kategori', except: 'Semua')]
    public string $kategori = 'Semua';

    /** Wisata yang langsung dibuka popup-nya di Peta Obyek Wisata (tautan dari beranda: ?dipilih=ID). */
    #[Url(as: 'dipilih')]
    public ?int $dipilih = null;

    public function pilihKategori(string $nama): void
    {
        $this->kategori = $nama;
        $this->dispatch('peta-obyek-diperbarui', titik: $this->titikObyek($this->daftarWisata()));
    }

    public static function koordinatValid(mixed $lat, mixed $lng): bool
    {
        return is_numeric($lat) && is_numeric($lng)
            && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180
            && ! ((float) $lat === 0.0 && (float) $lng === 0.0);
    }

    /** Wisata tayang dengan koordinat valid untuk Peta Obyek Wisata, difilter kategori. */
    private function daftarWisata(): Collection
    {
        return Wisata::with(['kategori', 'kecamatan'])
            ->tayang()
            ->when($this->kategori !== 'Semua', fn ($q) => $q->whereHas('kategori', fn ($k) => $k->where('nama', $this->kategori)))
            ->orderBy('nama')
            ->get()
            ->filter(fn (Wisata $w) => self::koordinatValid($w->latitude, $w->longitude))
            ->values();
    }

    /** Lapisan wisata menurut kategorinya: desa wisata, kuliner (restoran), selain itu obyek wisata. */
    private static function lapisanWisata(Wisata $w): string
    {
        return match ($w->kategori?->slug) {
            'desa-wisata' => 'desa-wisata',
            'kuliner' => 'restoran',
            default => 'objek-wisata',
        };
    }

    /**
     * Satu titik peta. Teks dimasukkan ke popup lewat textContent di JavaScript, jadi aman dari HTML yang diketik admin.
     *
     * @return array{kunci: string, jenis: string, nama: string, lat: float, lng: float, label: string, gambar: ?string, ikon: string, teks: ?string, url: ?string}
     */
    private static function titik(string $kunci, string $jenis, string $nama, mixed $lat, mixed $lng, string $label, ?string $gambar, ?string $teks, ?string $url): array
    {
        return [
            'kunci' => $kunci,
            'jenis' => $jenis,
            'nama' => $nama,
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'label' => $label,
            'gambar' => $gambar,
            'ikon' => self::LAPISAN[$jenis][1],
            'teks' => $teks,
            'url' => $url,
        ];
    }

    /** Semua titik Peta Wisata berlapis (bagian pertama). */
    private function titikBerlapis(): Collection
    {
        $valid = fn ($m) => self::koordinatValid($m->latitude, $m->longitude);

        $wisata = Wisata::with('kategori')->tayang()->get()->filter($valid)->map(fn (Wisata $w) => self::titik(
            'w-'.$w->id, self::lapisanWisata($w), $w->nama, $w->latitude, $w->longitude, $w->kategori?->nama ?? 'Wisata',
            $w->gambar_url, TeksKaya::polos($w->narasi ?: (string) $w->deskripsi_singkat, 140), route('publik.detail-wisata', $w),
        ));

        $hotel = Hotel::all()->filter($valid)->map(fn (Hotel $h) => self::titik(
            'h-'.$h->id_hotel, 'hotel', $h->nama_hotel, $h->latitude, $h->longitude, 'Hotel & Penginapan',
            $h->gambar_url, $h->deskripsi_singkat, route('publik.detail-hotel', $h),
        ));

        // Ekraf & kesenian: hanya yang aktif dan diberi titik lokasi oleh admin. Alamat/telepon pribadi tidak ditampilkan.
        $ekraf = Ekraf::with('kecamatan')->aktif()->whereNotNull('latitude')->get()->filter($valid)->map(fn (Ekraf $e) => self::titik(
            'e-'.$e->id_ekraf, 'ekraf', $e->nama_usaha, $e->latitude, $e->longitude, $e->subsektor_lengkap,
            null, 'Kec. '.($e->kecamatan->nama ?? '-'), route('publik.pencarian-ekraf', ['subsektor' => $e->kode_subsektor]),
        ));

        $kesenian = Kesenian::with('kecamatan')->aktif()->whereNotNull('latitude')->get()->filter($valid)->map(fn (Kesenian $k) => self::titik(
            'k-'.$k->id_kesenian, 'seni-budaya', $k->nama_kesenian, $k->latitude, $k->longitude, $k->jenis_lengkap,
            $k->dokumentasi_url, 'Kec. '.($k->kecamatan->nama ?? '-'), route('publik.data-kesenian', ['jenis' => $k->kode_jenis]),
        ));

        $situs = CagarBudaya::all()->filter($valid)->map(fn (CagarBudaya $c) => self::titik(
            'c-'.$c->id_cagar_budaya, 'situs-budaya', $c->nama, $c->latitude, $c->longitude, $c->kategori.' · '.$c->status,
            $c->gambar_url, $c->cuplikan, route('publik.detail-cagar-budaya', $c),
        ));

        $lokasi = LokasiPeta::all()->filter($valid)->map(fn (LokasiPeta $l) => self::titik(
            'l-'.$l->id_lokasi, $l->kategori, $l->nama, $l->latitude, $l->longitude, $l->kategori_label,
            $l->gambar_url, collect([$l->deskripsi_singkat, $l->alamat, $l->no_telepon ? 'Telp. '.$l->no_telepon : null])->filter()->implode(' · '), null,
        ));

        $cabor = Cabor::aktif()->whereNotNull('latitude')->get()->filter($valid)->map(fn (Cabor $c) => self::titik(
            'cb-'.$c->id_cabor, 'cabor', 'Sekretariat '.$c->nama_cabor.' ('.$c->induk_organisasi.')', $c->latitude, $c->longitude, 'Sekretariat Cabor',
            null, $c->alamat_sekretariat, route('publik.ppo-cabor', ['cabor' => $c->kode_cabor]),
        ));

        return collect([$wisata, $hotel, $ekraf, $kesenian, $situs, $lokasi, $cabor])->flatten(1)->values();
    }

    /** Titik Peta Obyek Wisata (bagian kedua). */
    private function titikObyek(Collection $wisata): array
    {
        return $wisata->map(fn (Wisata $w) => [
            ...self::titik('w-'.$w->id, 'objek-wisata', $w->nama, $w->latitude, $w->longitude, $w->kategori?->nama ?? 'Wisata',
                $w->gambar_url, TeksKaya::polos($w->narasi ?: (string) $w->deskripsi_singkat, 140), route('publik.detail-wisata', $w)),
            'ikon' => $w->kategori?->ikon ?? '📍',
        ])->all();
    }

    public function render()
    {
        $wisata = $this->daftarWisata();
        $berlapis = $this->titikBerlapis();

        return view('livewire.publik.peta-wisata', [
            'lapisan' => collect(self::LAPISAN)->map(fn ($l, $kunci) => [
                'kunci' => $kunci, 'label' => $l[0], 'ikon' => $l[1], 'jumlah' => $berlapis->where('jenis', $kunci)->count(),
            ])->values(),
            'titikBerlapis' => $berlapis->all(),
            'daftarKategori' => KategoriWisata::whereHas('wisata', fn ($q) => $q->tayang())->orderBy('nama')->get(),
            'wisata' => $wisata,
            'titik' => $this->titikObyek($wisata),
            'wisataTerbaru' => Wisata::with(['kategori', 'kecamatan'])->tayang()->latest('id')->limit(4)->get(),
            'paketTerbaru' => PaketWisata::tayang()->latest('id_paket')->limit(3)->get(),
        ]);
    }
}
