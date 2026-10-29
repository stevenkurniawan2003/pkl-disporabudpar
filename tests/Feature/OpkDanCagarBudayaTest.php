<?php

namespace Tests\Feature;

use App\Livewire\Admin\CagarBudaya as CagarBudayaPage;
use App\Livewire\Admin\CagarBudayaForm;
use App\Livewire\Admin\Opk as OpkPage;
use App\Livewire\Admin\OpkForm;
use App\Models\CagarBudaya;
use App\Models\GaleriOpk;
use App\Models\Opk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OpkDanCagarBudayaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_halaman_wajib_login(): void
    {
        $this->get('/admin/opk')->assertRedirect(route('admin.login'));
        $this->get('/admin/cagar-budaya')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/opk')->assertOk()->assertSee('-- Filter Jenis OPK --');
        $this->actingAs($admin)->get('/admin/cagar-budaya')->assertOk()->assertSee('+ Tambah Data');
    }

    public function test_tambah_opk_dengan_galeri_dan_pencatat(): void
    {
        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin);

        Livewire::test(OpkForm::class)
            ->call('buka')
            ->set('gambarBaru', UploadedFile::fake()->image('utama.jpg'))
            ->set('unggahGaleri', [UploadedFile::fake()->image('a.jpg')])
            ->set('nama', ' Wayang Orang ')
            ->set('jenis', 'Seni')
            ->set('kondisi', 'Terancam Punah')
            ->set('lokasi_persebaran', 'Kecamatan Wuluhan')
            ->set('tahun', '1900')
            ->set('deskripsi', '<p>Wayang wong.</p><script>alert(1)</script>')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('opk-disimpan', pesan: 'OPK "Wayang Orang" ditambahkan.');

        $opk = Opk::firstOrFail();
        $this->assertSame('Wayang Orang', $opk->nama);
        $this->assertSame($admin->id, $opk->dicatat_oleh);
        $this->assertStringNotContainsString('<script', $opk->deskripsi);
        $this->assertSame(1, GaleriOpk::count());
        Storage::disk('public')->assertExists($opk->gambar_utama);
    }

    public function test_validasi_opk(): void
    {
        Livewire::test(OpkForm::class)
            ->call('buka')
            ->set('jenis', 'Kuliner')
            ->set('kondisi', 'Hilang')
            ->set('tahun', '3000')
            ->set('gambarBaru', UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'))
            ->call('simpan')
            ->assertHasErrors(['nama' => 'required', 'jenis' => 'in', 'kondisi' => 'in', 'tahun' => 'between', 'gambarBaru' => 'image', 'deskripsi' => 'required']);
    }

    public function test_filter_jenis_dan_hapus_opk_beserta_file(): void
    {
        Storage::disk('public')->put('opk/a.jpg', 'x');
        $seni = Opk::factory()->create(['nama' => 'Wayang', 'jenis' => 'Seni', 'gambar_utama' => 'opk/a.jpg']);
        Opk::factory()->create(['nama' => 'Egrang', 'jenis' => 'Permainan Tradisional']);

        Livewire::test(OpkPage::class)
            ->set('jenis', 'Seni')->assertSee('Wayang')->assertDontSee('Egrang')
            ->call('hapus', $seni->id_opk)->assertSet('pesan', 'OPK "Wayang" dihapus.');

        $this->assertModelMissing($seni);
        Storage::disk('public')->assertMissing('opk/a.jpg');
    }

    public function test_tambah_dan_validasi_cagar_budaya(): void
    {
        Livewire::test(CagarBudayaForm::class)
            ->call('buka')
            ->set('status', 'XX')
            ->set('latitude', 'abc')
            ->call('simpan')
            ->assertHasErrors(['nama' => 'required', 'status' => 'in', 'latitude' => 'numeric', 'kategori' => 'required', 'gambarBaru' => 'required']);

        Livewire::test(CagarBudayaForm::class)
            ->call('buka')
            ->set('gambarBaru', UploadedFile::fake()->image('situs.jpg'))
            ->set('nama', 'Situs Duplang')
            ->set('alamat', 'Desa Kamal, Arjasa')
            ->set('latitude', '-8,1170')
            ->set('longitude', '113,7420')
            ->set('kategori', 'Situs')
            ->set('status', 'CB')
            ->set('kondisi', 'Terpelihara')
            ->set('deskripsi_sejarah', '<p>Situs megalitikum.</p>')
            ->call('simpan')
            ->assertHasNoErrors();

        $cb = CagarBudaya::firstOrFail();
        $this->assertSame(-8.117, $cb->latitude);
        $this->assertSame('CB', $cb->status);
    }

    public function test_filter_status_cagar_budaya(): void
    {
        CagarBudaya::factory()->create(['nama' => 'Situs Duplang', 'status' => 'CB']);
        CagarBudaya::factory()->create(['nama' => 'Batu Kenong', 'status' => 'ODCB']);

        Livewire::test(CagarBudayaPage::class)->set('status', 'CB')->assertSee('Situs Duplang')->assertDontSee('Batu Kenong');
    }
}
