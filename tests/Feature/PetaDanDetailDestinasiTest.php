<?php

namespace Tests\Feature;

use App\Livewire\Publik\PetaWisata;
use App\Models\Cabor;
use App\Models\CagarBudaya;
use App\Models\Ekraf;
use App\Models\GaleriHotel;
use App\Models\GaleriWisata;
use App\Models\Hotel;
use App\Models\KategoriWisata;
use App\Models\Kecamatan;
use App\Models\LokasiPeta;
use App\Models\User;
use App\Models\Wisata;
use Database\Seeders\KategoriSeeder;
use Database\Seeders\KecamatanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PetaDanDetailDestinasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([KategoriSeeder::class, KecamatanSeeder::class]);
    }

    private function wisata(string $nama, array $atribut = []): Wisata
    {
        return Wisata::create(array_merge([
            'kategori_id' => KategoriWisata::where('nama', 'Pantai')->value('id'),
            'kecamatan_id' => Kecamatan::first()->id,
            'nama' => $nama,
            'slug' => str($nama)->slug(),
            'deskripsi_singkat' => "Singkat {$nama}.",
            'narasi' => "Narasi lengkap {$nama}.",
            'alamat' => 'Lojejer',
            'latitude' => -8.43,
            'longitude' => 113.55,
            'status' => 'tayang',
        ], $atribut));
    }

    private function hotel(string $nama, array $atribut = []): Hotel
    {
        return Hotel::create(array_merge([
            'nama_hotel' => $nama,
            'gambar_utama' => 'hotel/x.jpg',
            'deskripsi_singkat' => "Singkat {$nama}.",
            'narasi' => "Narasi {$nama}.",
            'alamat' => 'Jl. Gajah Mada 1',
            'latitude' => -8.17,
            'longitude' => 113.70,
        ], $atribut));
    }

    public function test_peta_berlapis_berisi_titik_valid_per_kategori(): void
    {
        $papuma = $this->wisata('Papuma');
        $desa = $this->wisata('Desa Tutul', ['kategori_id' => KategoriWisata::where('slug', 'desa-wisata')->value('id')]);
        $kuliner = $this->wisata('Rumah Makan Pecel', ['kategori_id' => KategoriWisata::where('slug', 'kuliner')->value('id')]);
        $this->wisata('Masih Draf', ['status' => 'draf']);
        $this->wisata('Titik Nol', ['latitude' => 0, 'longitude' => 0]);
        $this->wisata('Dihapus')->delete();
        $bintang = $this->hotel('Hotel Bintang');
        LokasiPeta::create(['nama' => 'Pusat Suwar-Suwir', 'kategori' => 'oleh-oleh', 'alamat' => 'Jl. Trunojoyo', 'latitude' => -8.17, 'longitude' => 113.70]);
        CagarBudaya::factory()->create(['nama' => 'Situs Duplang']);
        Ekraf::factory()->create(['nama_usaha' => 'Tanpa Titik']);
        Ekraf::factory()->create(['nama_usaha' => 'Batik Jember', 'latitude' => -8.2, 'longitude' => 113.6]);
        Ekraf::factory()->nonaktif()->create(['nama_usaha' => 'Tutup', 'latitude' => -8.2, 'longitude' => 113.6]);
        Cabor::factory()->create(['kode_cabor' => 'atletik', 'latitude' => -8.16, 'longitude' => 113.7]);

        $komponen = Livewire::test(PetaWisata::class);
        $titik = collect($komponen->viewData('titikBerlapis'));

        $this->assertSame(
            ['cabor', 'desa-wisata', 'ekraf', 'hotel', 'objek-wisata', 'oleh-oleh', 'restoran', 'situs-budaya'],
            $titik->pluck('jenis')->sort()->values()->all()
        );
        $this->assertSame(route('publik.detail-hotel', $bintang), $titik->firstWhere('jenis', 'hotel')['url']);
        $this->assertSame('w-'.$papuma->id, $titik->firstWhere('jenis', 'objek-wisata')['kunci']);
        $this->assertSame('w-'.$desa->id, $titik->firstWhere('jenis', 'desa-wisata')['kunci']);
        $this->assertSame('w-'.$kuliner->id, $titik->firstWhere('jenis', 'restoran')['kunci']);
        $this->assertNull($titik->firstWhere('jenis', 'oleh-oleh')['url']);
        $this->assertSame('Batik Jember', $titik->firstWhere('jenis', 'ekraf')['nama']);

        $jumlah = $komponen->viewData('lapisan')->pluck('jumlah', 'kunci');
        $this->assertSame(1, $jumlah['ekraf']);
        $this->assertSame(0, $jumlah['seni-budaya']);
        $komponen->assertSee('Situs Budaya (1)');
    }

    public function test_peta_obyek_wisata_hanya_wisata_dan_filter_kategori(): void
    {
        $papuma = $this->wisata('Papuma');
        $this->wisata('Rembangan', ['kategori_id' => KategoriWisata::where('nama', 'Pegunungan')->value('id')]);
        $this->hotel('Hotel Bintang');

        Livewire::test(PetaWisata::class)
            ->assertViewHas('titik', fn (array $t) => count($t) === 2
                && collect($t)->firstWhere('nama', 'Papuma')['url'] === route('publik.detail-wisata', $papuma)
                && collect($t)->firstWhere('nama', 'Papuma')['teks'] === 'Narasi lengkap Papuma.')
            ->call('pilihKategori', 'Pegunungan')
            ->assertDispatched('peta-obyek-diperbarui', fn ($n, $p) => count($p['titik']) === 1 && $p['titik'][0]['nama'] === 'Rembangan');
    }

    public function test_halaman_peta_bisa_dibuka_dan_memuat_leaflet(): void
    {
        $this->wisata('Papuma');

        $this->get('/publik/peta-wisata')
            ->assertOk()
            ->assertSee('leaflet.min.js', false)
            ->assertSee('data-kunci="w-', false)
            ->assertSee('Peta Obyek Wisata')
            ->assertSee('Cari nama obyek wisata')
            ->assertSee('Desa Wisata (0)');
    }

    public function test_detail_wisata_dengan_galeri_dan_link_maps(): void
    {
        $w = $this->wisata('Papuma', ['link_google_maps' => 'https://maps.app.goo.gl/papuma', 'gambar_utama' => 'wisata/a.jpg']);
        GaleriWisata::create(['wisata_id' => $w->id, 'gambar' => 'wisata/galeri/1.jpg', 'keterangan' => 'Tebing karang', 'urutan' => 1]);
        GaleriWisata::create(['wisata_id' => $w->id, 'gambar' => 'wisata/galeri/2.jpg', 'urutan' => 2]);

        $this->get('/publik/wisata/papuma')
            ->assertOk()
            ->assertSee('<title>Papuma · Disporabudpar Jember</title>', false)
            ->assertSee('Narasi lengkap Papuma.')
            ->assertSee('Galeri foto')
            ->assertSee('storage/wisata/galeri/1.jpg', false)
            ->assertSee('Tebing karang')
            ->assertSee('id="lightbox"', false)
            ->assertSee('id="peta-kecil"', false)
            ->assertSee('href="https://maps.app.goo.gl/papuma"', false)
            ->assertDontSee('Kunjungi Website')
            ->assertSee('class="nb on" aria-haspopup="true">Destinasi', false);
    }

    public function test_detail_wisata_tanpa_link_dan_galeri_menyembunyikan_tombol(): void
    {
        $this->wisata('Rembangan');

        $this->get('/publik/wisata/rembangan')
            ->assertOk()
            ->assertDontSee('Buka di Google Maps')
            ->assertDontSee('id="lightbox"', false);
    }

    public function test_detail_wisata_404_untuk_draf_dihapus_dan_slug_salah(): void
    {
        $this->wisata('Draf', ['status' => 'draf']);
        $this->wisata('Dihapus')->delete();

        $this->get('/publik/wisata/tidak-ada')->assertNotFound();
        $this->get('/publik/wisata/dihapus')->assertNotFound();
        $this->get('/publik/wisata/draf')->assertNotFound();

        // Admin yang login boleh melihat pratinjau draf.
        $this->actingAs(User::factory()->create(['aktif' => true]))
            ->get('/publik/wisata/draf')->assertOk()->assertSee('Pratinjau admin');
    }

    public function test_detail_hotel_lengkap_dan_404(): void
    {
        $h = $this->hotel('Hotel Bintang', [
            'no_telepon' => '0331 123456',
            'link_google_maps' => 'https://maps.app.goo.gl/bintang',
            'link_website' => 'https://hotelbintang.com',
        ]);
        GaleriHotel::create(['id_hotel' => $h->id_hotel, 'gambar' => 'hotel/galeri/1.jpg']);

        $this->get('/publik/hotel/'.$h->id_hotel)
            ->assertOk()
            ->assertSee('Hotel Bintang')
            ->assertSee('Narasi Hotel Bintang.')
            ->assertSee('Jl. Gajah Mada 1')
            ->assertSee('tel:0331123456', false)
            ->assertSee('storage/hotel/galeri/1.jpg', false)
            ->assertSee('href="https://hotelbintang.com"', false)
            ->assertSee('Kunjungi Website')
            ->assertSee('Buka di Google Maps');

        $this->get('/publik/hotel/999')->assertNotFound();
        $this->get('/publik/hotel/bukan-angka')->assertNotFound();
    }
}
