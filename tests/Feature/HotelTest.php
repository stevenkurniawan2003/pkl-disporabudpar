<?php

namespace Tests\Feature;

use App\Livewire\Admin\Hotel as HotelPage;
use App\Livewire\Admin\HotelForm;
use App\Models\GaleriHotel;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HotelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function isiForm(?int $id = null)
    {
        return Livewire::test(HotelForm::class)
            ->call('buka', $id)
            ->set('nama_hotel', 'Hotel Uji')
            ->set('deskripsi_singkat', 'Hotel nyaman di pusat kota.')
            ->set('narasi', 'Deskripsi lengkap hotel uji.')
            ->set('alamat', 'Jl. Uji No. 1')
            ->set('latitude', '-8,1724')
            ->set('longitude', '113.6995');
    }

    private function buatHotel(int $jumlahGaleri = 2): Hotel
    {
        $galeri = array_map(fn ($i) => UploadedFile::fake()->image("g{$i}.jpg"), range(1, $jumlahGaleri));

        $this->isiForm()
            ->set('gambarBaru', UploadedFile::fake()->image('utama.jpg'))
            ->set('unggahGaleri', $galeri)
            ->call('simpan')
            ->assertHasNoErrors();

        return Hotel::with('galeri')->firstOrFail();
    }

    public function test_halaman_hotel_wajib_login(): void
    {
        $this->get('/admin/hotel')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/hotel')->assertOk()->assertSee('Hotel');
    }

    public function test_tambah_hotel_lengkap_dengan_galeri(): void
    {
        $this->isiForm()
            ->set('link_google_maps', 'maps.app.goo.gl/abc')
            ->set('link_website', 'https://hotel-uji.com')
            ->set('no_telepon', '0331 123456')
            ->set('gambarBaru', UploadedFile::fake()->image('utama.jpg'))
            ->set('unggahGaleri', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.webp')])
            ->assertCount('galeriBaru', 2)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('terbuka', false)
            ->assertDispatched('hotel-disimpan', pesan: 'Hotel "Hotel Uji" ditambahkan.');

        $hotel = Hotel::with('galeri')->firstOrFail();
        $this->assertSame('https://maps.app.goo.gl/abc', $hotel->link_google_maps);
        $this->assertSame(-8.1724, $hotel->latitude);
        $this->assertCount(2, $hotel->galeri);
        Storage::disk('public')->assertExists($hotel->gambar_utama);
        $hotel->galeri->each(fn ($g) => Storage::disk('public')->assertExists($g->gambar));
    }

    public function test_validasi_wajib_url_dan_gambar(): void
    {
        Livewire::test(HotelForm::class)
            ->call('buka')
            ->set('link_website', 'bukan url')
            ->set('no_telepon', 'abc')
            ->call('simpan')
            ->assertHasErrors([
                'nama_hotel' => 'required',
                'deskripsi_singkat' => 'required',
                'narasi' => 'required',
                'alamat' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'gambarBaru' => 'required',
                'link_website' => 'url',
                'no_telepon' => 'regex',
            ])
            ->assertSee('Gambar utama wajib diunggah.');

        $this->isiForm()
            ->set('gambarBaru', UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'))
            ->set('unggahGaleri', [UploadedFile::fake()->image('besar.jpg')->size(3000)])
            ->assertHasErrors('unggahGaleri.0')
            ->call('simpan')
            ->assertHasErrors(['gambarBaru']);

        $this->assertSame(0, Hotel::count());
    }

    public function test_ubah_hotel_ganti_gambar_utama_dan_kelola_galeri(): void
    {
        $hotel = $this->buatHotel(2);
        $gambarLama = $hotel->gambar_utama;
        [$foto1, $foto2] = $hotel->galeri->all();

        Livewire::test(HotelForm::class)
            ->call('buka', $hotel->id_hotel)
            ->assertSet('nama_hotel', 'Hotel Uji')
            ->assertCount('galeri', 2)
            // Ubah tanpa gambar baru tetap boleh.
            ->set('nama_hotel', 'Hotel Uji Baru')
            ->set('gambarBaru', UploadedFile::fake()->image('baru.jpg'))
            ->call('tandaiHapusGaleri', 0)
            ->set('unggahGaleri', [UploadedFile::fake()->image('c.jpg')])
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('hotel-disimpan', pesan: 'Hotel "Hotel Uji Baru" diperbarui.');

        $hotel->refresh()->load('galeri');
        $this->assertSame('Hotel Uji Baru', $hotel->nama_hotel);
        Storage::disk('public')->assertMissing($gambarLama);
        Storage::disk('public')->assertExists($hotel->gambar_utama);
        $this->assertModelMissing($foto1);
        Storage::disk('public')->assertMissing($foto1->gambar);
        $this->assertModelExists($foto2);
        $this->assertCount(2, $hotel->galeri);
    }

    public function test_ubah_tanpa_ganti_gambar_utama(): void
    {
        $hotel = $this->buatHotel(0);

        Livewire::test(HotelForm::class)
            ->call('buka', $hotel->id_hotel)
            ->set('alamat', 'Alamat baru')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame('Alamat baru', $hotel->fresh()->alamat);
        Storage::disk('public')->assertExists($hotel->gambar_utama);
    }

    public function test_batal_hapus_galeri_tidak_menghapus_foto(): void
    {
        $hotel = $this->buatHotel(1);

        Livewire::test(HotelForm::class)
            ->call('buka', $hotel->id_hotel)
            ->call('tandaiHapusGaleri', 0)
            ->call('tandaiHapusGaleri', 0)
            ->call('simpan');

        $this->assertSame(1, GaleriHotel::count());
    }

    public function test_daftar_detail_dan_hapus_hotel_beserta_semua_file(): void
    {
        $hotel = $this->buatHotel(2);
        $semuaFile = $hotel->galeri->pluck('gambar')->push($hotel->gambar_utama)->all();

        Livewire::test(HotelPage::class)
            ->assertSee('Hotel Uji')
            ->set('cari', 'tidak-ada')
            ->assertDontSee('Jl. Uji No. 1')
            ->set('cari', 'Uji No')
            ->assertSee('Hotel Uji')
            ->call('lihatDetail', $hotel->id_hotel)
            ->assertSee('Hotel nyaman di pusat kota.')
            ->assertSee('Galeri (2 foto)')
            ->call('hapus', $hotel->id_hotel)
            ->assertSet('detailId', null)
            ->assertSet('jenisPesan', 'ok')
            ->assertSee('Hotel &quot;Hotel Uji&quot; dihapus.', false);

        $this->assertSame(0, Hotel::count());
        $this->assertSame(0, GaleriHotel::count());
        foreach ($semuaFile as $file) {
            Storage::disk('public')->assertMissing($file);
        }
    }

    public function test_hapus_hotel_yang_sudah_tidak_ada_menampilkan_pesan_gagal(): void
    {
        Livewire::test(HotelPage::class)
            ->call('hapus', 999)
            ->assertSet('jenisPesan', 'err')
            ->assertSee('Hotel tidak ditemukan');
    }

    public function test_pesan_sukses_dari_form_tampil_di_daftar(): void
    {
        Livewire::test(HotelPage::class)
            ->dispatch('hotel-disimpan', pesan: 'Hotel "X" ditambahkan.')
            ->assertSee('Hotel &quot;X&quot; ditambahkan.', false);
    }
}
