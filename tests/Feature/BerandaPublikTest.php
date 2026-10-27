<?php

namespace Tests\Feature;

use App\Livewire\Publik\Beranda;
use App\Models\Berita;
use App\Models\Hotel;
use App\Models\KategoriBerita;
use App\Models\KategoriWisata;
use App\Models\Kecamatan;
use App\Models\KunjunganHotel;
use App\Models\KunjunganWisata;
use App\Models\Wisata;
use Database\Seeders\KategoriSeeder;
use Database\Seeders\KecamatanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class BerandaPublikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([KategoriSeeder::class, KecamatanSeeder::class]);
        Cache::forget(Beranda::KUNCI_CACHE_KUNJUNGAN);
    }

    private function wisata(string $nama, array $atribut = []): Wisata
    {
        return Wisata::create(array_merge([
            'kategori_id' => KategoriWisata::first()->id,
            'kecamatan_id' => Kecamatan::first()->id,
            'nama' => $nama,
            'slug' => str($nama)->slug(),
            'alamat' => 'x',
            'latitude' => -8.4,
            'longitude' => 113.5,
            'status' => 'tayang',
        ], $atribut));
    }

    private function berita(string $judul, int $hariLalu): Berita
    {
        return Berita::create([
            'kategori_berita_id' => KategoriBerita::first()->id,
            'judul' => $judul,
            'slug' => str($judul)->slug(),
            'isi' => 'Isi.',
            'status' => 'terbit',
            'tanggal_terbit' => now()->subDays($hariLalu),
        ]);
    }

    public function test_urutan_bagian_sesuai_desain(): void
    {
        $this->get('/publik/beranda')
            ->assertOk()
            ->assertSeeInOrder([
                'Jember, Miniatur Indonesia', 'Jelajahi destinasi',
                'Destinasi unggulan', 'Lihat peta wisata',
                'Bidang dan UPTD', 'Pariwisata', 'UPTD Olahraga',
                'Berita terbaru', 'Semua berita',
                'Kunjungan wisatawan', 'Kunjungan hotel',
            ])
            ->assertDontSee('Kartu induk kesenian') // bagian Layanan sudah dihapus
            ->assertDontSee('521.343') // angka contoh desain sudah tidak ditulis langsung
            ->assertSee('class="nb on" href="'.route('publik.beranda').'"', false);
    }

    public function test_destinasi_unggulan_dan_cadangan_wisata_terbaru(): void
    {
        $this->wisata('Wisata Lama');
        $this->wisata('Wisata Baru');
        $this->wisata('Masih Draf', ['status' => 'draf', 'unggulan' => true]);

        // Belum ada yang ditandai unggulan -> wisata terbaru.
        Livewire::test(Beranda::class)
            ->assertViewHas('unggulanCadangan', true)
            ->assertViewHas('unggulan', fn ($u) => $u->pluck('nama')->all() === ['Wisata Baru', 'Wisata Lama'])
            ->assertSee('Destinasi wisata terbaru');

        $papuma = $this->wisata('Papuma', ['unggulan' => true]);

        Livewire::test(Beranda::class)
            ->assertViewHas('unggulanCadangan', false)
            ->assertViewHas('unggulan', fn ($u) => $u->pluck('nama')->all() === ['Papuma'])
            ->assertSee(route('publik.detail-wisata', $papuma), false)
            ->assertDontSee('Masih Draf');
    }

    public function test_maksimal_4_wisata_dan_6_berita_terbaru(): void
    {
        foreach (range(1, 7) as $i) {
            $this->wisata("Unggulan {$i}", ['unggulan' => true]);
            $this->berita("Berita {$i}", $i);
        }

        Livewire::test(Beranda::class)
            ->assertViewHas('unggulan', fn ($u) => $u->count() === 4)
            ->assertViewHas('berita', fn ($b) => $b->pluck('judul')->all() === ['Berita 1', 'Berita 2', 'Berita 3', 'Berita 4', 'Berita 5', 'Berita 6'])
            // 2 terbaru jadi kartu utama, sisanya kartu biasa; setiap kartu punya "Selengkapnya".
            ->assertSeeHtmlInOrder(['news-utama', 'Berita 1', 'Berita 2', 'class="news"', 'Berita 3'])
            ->assertSee('Selengkapnya');
    }

    public function test_pita_statistik_kunjungan_dari_database(): void
    {
        $papuma = $this->wisata('Papuma');
        $draf = $this->wisata('Draf', ['status' => 'draf']);
        $hotel = Hotel::create(['nama_hotel' => 'H', 'gambar_utama' => 'x.jpg', 'deskripsi_singkat' => 'x', 'narasi' => 'x', 'alamat' => 'x', 'latitude' => -8.1, 'longitude' => 113.7]);
        $tahun = now()->year - 1; // tahun ini belum ada data -> pakai tahun terbaru yang ada

        KunjunganWisata::create(['wisata_id' => $papuma->id, 'tahun' => $tahun, 'bulan' => 1, 'domestik' => 500000, 'mancanegara' => 21343]);
        KunjunganWisata::create(['wisata_id' => $papuma->id, 'tahun' => $tahun, 'bulan' => 9, 'domestik' => 0, 'mancanegara' => 0]);
        KunjunganWisata::create(['wisata_id' => $draf->id, 'tahun' => $tahun, 'bulan' => 2, 'domestik' => 999, 'mancanegara' => 0]);
        KunjunganHotel::create(['hotel_id' => $hotel->id_hotel, 'tahun' => $tahun, 'bulan' => 3, 'domestik' => 71000, 'mancanegara' => 954]);

        $this->get('/publik/beranda')
            ->assertOk()
            ->assertSee("Kunjungan wisatawan, Januari–September {$tahun}")
            ->assertSee('521.343')
            ->assertSee("Kunjungan hotel, Januari–Maret {$tahun}")
            ->assertSee('71.954')
            ->assertSee(route('publik.kunjungan-wisata', ['tahun' => $tahun]), false);
    }

    public function test_css_tidak_menimpa_class_ikon_bootstrap(): void
    {
        // ".bi" milik Bootstrap Icons; dulu kotak Bidang memakai class yang sama sehingga ikon ikut bergaya kotak.
        $css = file_get_contents(public_path('css/publik.css'));
        // Selector yang DIAWALI ".bi" dilarang; ".cb small .bi" (mengatur ukuran ikon) tetap boleh.
        $this->assertDoesNotMatchRegularExpression('/(^|[},])\s*\.bi[{.\s]/m', $css);
    }

    public function test_query_beranda_tetap_sedikit(): void
    {
        foreach (range(1, 4) as $i) {
            $this->wisata("Unggulan {$i}", ['unggulan' => true]);
            $this->berita("Berita {$i}", $i);
        }
        Beranda::ringkasanKunjungan(); // isi cache seperti kunjungan sebelumnya

        DB::enableQueryLog();
        Livewire::test(Beranda::class);
        $jumlahQuery = count(DB::getQueryLog());

        // wisata + kategori + kecamatan + berita + kategori berita = 5 (eager loading, tanpa N+1).
        $this->assertLessThanOrEqual(6, $jumlahQuery);
    }
}
