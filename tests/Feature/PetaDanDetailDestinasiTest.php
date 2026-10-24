<?php

namespace Tests\Feature;

use App\Livewire\Publik\PetaWisata;
use App\Models\GaleriHotel;
use App\Models\GaleriWisata;
use App\Models\Hotel;
use App\Models\KategoriWisata;
use App\Models\Kecamatan;
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

    public function test_peta_berisi_titik_wisata_dan_hotel_valid_saja(): void
    {
        $papuma = $this->wisata('Papuma');
        $this->wisata('Masih Draf', ['status' => 'draf']);
        $this->wisata('Titik Nol', ['latitude' => 0, 'longitude' => 0]);
        $this->wisata('Dihapus')->delete();
        $bintang = $this->hotel('Hotel Bintang');

        Livewire::test(PetaWisata::class)
            ->assertViewHas('titik', function (array $titik) use ($papuma, $bintang) {
                $kunci = array_column($titik, 'kunci');
                sort($kunci);

                return $kunci === ['h-'.$bintang->id_hotel, 'w-'.$papuma->id]
                    && collect($titik)->firstWhere('jenis', 'hotel')['url'] === route('publik.detail-hotel', $bintang)
                    && collect($titik)->firstWhere('jenis', 'hotel')['teks'] === 'Singkat Hotel Bintang.'
                    && collect($titik)->firstWhere('jenis', 'wisata')['url'] === route('publik.detail-wisata', $papuma)
                    && collect($titik)->firstWhere('jenis', 'wisata')['teks'] === 'Narasi lengkap Papuma.';
            })
            ->assertSee('Papuma')
            ->assertSee('Hotel Bintang');
    }

    public function test_filter_wisata_saja_hotel_saja_dan_kategori(): void
    {
        $this->wisata('Papuma');
        $this->wisata('Rembangan', ['kategori_id' => KategoriWisata::where('nama', 'Pegunungan')->value('id')]);
        $this->hotel('Hotel Bintang');

        Livewire::test(PetaWisata::class)
            ->call('pilihJenis', 'hotel')
            ->assertDispatched('peta-diperbarui', fn ($n, $p) => count($p['titik']) === 1 && $p['titik'][0]['jenis'] === 'hotel')
            ->assertDontSee('Rembangan')
            ->call('pilihJenis', 'wisata')
            ->assertDispatched('peta-diperbarui', fn ($n, $p) => count($p['titik']) === 2)
            ->assertDontSee('Hotel Bintang')
            ->call('pilihJenis', 'semua')
            ->call('pilihKategori', 'Pegunungan')
            ->assertDispatched('peta-diperbarui', fn ($n, $p) => count($p['titik']) === 1 && $p['titik'][0]['nama'] === 'Rembangan')
            ->call('pilihJenis', 'tidak-ada')
            ->assertSet('jenis', 'semua');
    }

    public function test_halaman_peta_bisa_dibuka_dan_memuat_leaflet(): void
    {
        $this->wisata('Papuma');

        $this->get('/publik/peta-wisata')
            ->assertOk()
            ->assertSee('leaflet.min.js', false)
            ->assertSee('data-kunci="w-', false);
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
