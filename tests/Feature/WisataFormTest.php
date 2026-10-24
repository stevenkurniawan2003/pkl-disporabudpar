<?php

namespace Tests\Feature;

use App\Livewire\Admin\WisataForm;
use App\Models\GaleriWisata;
use App\Models\KategoriWisata;
use App\Models\Kecamatan;
use App\Models\User;
use App\Models\Wisata;
use Database\Seeders\KategoriSeeder;
use Database\Seeders\KecamatanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WisataFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed([KategoriSeeder::class, KecamatanSeeder::class]);
    }

    private function isiForm()
    {
        return Livewire::test(WisataForm::class)
            ->call('buka')
            ->set('nama', 'Pantai Uji')
            ->set('deskripsi_singkat', 'Pantai pasir putih.')
            ->set('kategori_id', KategoriWisata::first()->id)
            ->set('kecamatan_id', Kecamatan::first()->id)
            ->set('alamat', 'Desa Uji')
            ->set('latitude', '-8,4286')
            ->set('longitude', '113.5469');
    }

    public function test_tambah_wisata_dengan_gambar_utama_dan_galeri(): void
    {
        $this->isiForm()
            ->set('gambarBaru', UploadedFile::fake()->image('utama.jpg'))
            ->set('unggahGaleri', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')])
            ->assertCount('galeriBaru', 2)
            ->assertCount('unggahGaleri', 0)
            ->set('keteranganBaru.0', 'Sunset di pantai')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('terbuka', false)
            ->assertDispatched('wisata-disimpan', pesan: 'Wisata "Pantai Uji" ditambahkan.');

        $wisata = Wisata::where('nama', 'Pantai Uji')->firstOrFail();
        Storage::disk('public')->assertExists($wisata->gambar_utama);

        $galeri = $wisata->galeri;
        $this->assertCount(2, $galeri);
        $this->assertSame('Sunset di pantai', $galeri[0]->keterangan);
        $this->assertNull($galeri[1]->keterangan);
        $this->assertSame([1, 2], $galeri->pluck('urutan')->all());
        $galeri->each(fn ($g) => Storage::disk('public')->assertExists($g->gambar));
    }

    public function test_ubah_wisata_hapus_dan_tambah_foto_galeri(): void
    {
        $this->isiForm()
            ->set('unggahGaleri', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
            ->call('simpan');

        $wisata = Wisata::firstOrFail();
        [$lama1, $lama2] = $wisata->galeri->all();

        Livewire::test(WisataForm::class)
            ->call('buka', $wisata->id)
            ->assertCount('galeri', 2)
            ->call('tandaiHapusGaleri', 0)
            ->set('galeri.1.keterangan', 'Diperbarui')
            ->set('unggahGaleri', [UploadedFile::fake()->image('c.jpg')])
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertModelMissing($lama1);
        Storage::disk('public')->assertMissing($lama1->gambar);
        $this->assertSame('Diperbarui', $lama2->fresh()->keterangan);

        $galeri = $wisata->fresh()->galeri;
        $this->assertCount(2, $galeri);
        $this->assertSame(3, $galeri->last()->urutan);
    }

    public function test_galeri_menolak_file_bukan_gambar_dan_melebihi_batas(): void
    {
        $form = $this->isiForm()
            ->set('unggahGaleri', [UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')])
            ->assertHasErrors('unggahGaleri.0')
            ->assertCount('galeriBaru', 0);

        $banyak = array_map(fn ($i) => UploadedFile::fake()->image("f{$i}.jpg"), range(1, WisataForm::MAKS_GALERI + 1));

        $form->set('unggahGaleri', $banyak)
            ->assertHasErrors('unggahGaleri')
            ->assertCount('galeriBaru', 0);

        $this->assertSame(0, GaleriWisata::count());
    }

    public function test_deskripsi_singkat_wajib_dan_link_maps_harus_url(): void
    {
        $this->isiForm()
            ->set('deskripsi_singkat', '')
            ->set('link_google_maps', 'bukan url')
            ->call('simpan')
            ->assertHasErrors(['deskripsi_singkat' => 'required', 'link_google_maps' => 'url']);

        $this->isiForm()
            ->set('link_google_maps', 'maps.app.goo.gl/xyz')
            ->call('simpan')
            ->assertHasNoErrors();

        $wisata = Wisata::firstOrFail();
        $this->assertSame('Pantai pasir putih.', $wisata->deskripsi_singkat);
        $this->assertSame('https://maps.app.goo.gl/xyz', $wisata->link_google_maps);
    }

    public function test_detail_dan_hapus_dari_halaman_daftar(): void
    {
        $this->isiForm()
            ->set('unggahGaleri', [UploadedFile::fake()->image('a.jpg')])
            ->set('keteranganBaru.0', 'Tebing karang')
            ->call('simpan');

        $wisata = Wisata::firstOrFail();

        Livewire::test(\App\Livewire\Admin\Wisata::class)
            ->call('lihatDetail', $wisata->id)
            ->assertSee('Pantai pasir putih.')
            ->assertSee('Tebing karang')
            ->call('ubahDariDetail')
            ->assertSet('detailId', null)
            ->assertDispatched('buka-form-wisata', id: $wisata->id)
            ->call('hapus', $wisata->id)
            ->assertSet('jenisPesan', 'ok')
            ->assertSee('Wisata &quot;Pantai Uji&quot; dihapus.', false);

        $this->assertSoftDeleted($wisata);
    }

    public function test_halaman_wisata_wajib_login_dan_tampil(): void
    {
        $this->get('/admin/wisata')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/wisata')->assertOk()->assertSee('+ Tambah wisata');
    }

    public function test_gambar_utama_bukan_gambar_langsung_ditolak_tanpa_error(): void
    {
        $this->isiForm()
            ->set('gambarBaru', UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'))
            ->assertHasErrors(['gambarBaru' => 'image'])
            ->assertOk();
    }

    public function test_daftar_wisata_menampilkan_jumlah_foto_galeri(): void
    {
        $this->isiForm()
            ->set('unggahGaleri', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
            ->call('simpan');

        Livewire::test(\App\Livewire\Admin\Wisata::class)
            ->assertSee('Pantai Uji')
            ->assertSee('2 foto');
    }

    public function test_batalkan_foto_galeri_baru_sebelum_disimpan(): void
    {
        $this->isiForm()
            ->set('unggahGaleri', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
            ->set('keteranganBaru.1', 'Tetap')
            ->call('batalGaleriBaru', 0)
            ->assertCount('galeriBaru', 1)
            ->assertSet('keteranganBaru', ['Tetap'])
            ->call('simpan');

        $this->assertSame(['Tetap'], GaleriWisata::pluck('keterangan')->all());
    }
}
