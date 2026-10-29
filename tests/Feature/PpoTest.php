<?php

namespace Tests\Feature;

use App\Livewire\Admin\Atlet as AtletPage;
use App\Livewire\Admin\AtletForm;
use App\Livewire\Admin\Cabor as CaborPage;
use App\Livewire\Admin\CaborForm;
use App\Livewire\Admin\GaleriPrestasi;
use App\Livewire\Admin\PelatihForm;
use App\Livewire\Admin\PrestasiAtlet as PrestasiPage;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\GaleriPrestasi as GaleriModel;
use App\Models\Kecamatan;
use App\Models\Pelatih;
use App\Models\PrestasiAtlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PpoTest extends TestCase
{
    use RefreshDatabase;

    private Kecamatan $patrang;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->patrang = Kecamatan::create(['nama' => 'Patrang']);
    }

    private function isiBiodata($komponen, string $nama = 'Budi Santoso', string $cabor = 'pencak-silat')
    {
        return $komponen
            ->set('nama_lengkap', $nama)
            ->set('tempat_lahir', 'Jember')
            ->set('tanggal_lahir', '2008-05-20')
            ->set('jenis_kelamin', 'L')
            ->set('alamat', 'Jl. Mastrip 1')
            ->set('kecamatan_id', (string) $this->patrang->id)
            ->set('kode_cabor', $cabor);
    }

    public function test_menu_ppo_wajib_login_dan_tampil_di_sidebar(): void
    {
        $this->get('/admin/ppo/atlet')->assertRedirect(route('admin.login'));
        $this->get('/admin/ppo/ekspor/atlet')->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create(['aktif' => true]))->get('/admin/ppo/atlet')
            ->assertOk()
            ->assertSee('+ Tambah Atlet')
            ->assertSee('Database Pelatih')
            ->assertSee('Galeri Prestasi');
    }

    public function test_tambah_atlet_dan_umur_otomatis(): void
    {
        Carbon::setTestNow('2026-10-28');

        $this->isiBiodata(Livewire::test(AtletForm::class)->call('buka'))
            ->assertViewHas('umur', 18)
            ->set('status_binaan', 'Junior')
            ->set('klub', '')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('atlet-disimpan', pesan: 'Atlet "Budi Santoso" ditambahkan.');

        $a = Atlet::firstOrFail();
        $this->assertSame(18, $a->umur);
        $this->assertNull($a->klub);
        $this->assertSame('Pencak Silat', $a->nama_cabor);
    }

    public function test_validasi_atlet(): void
    {
        Livewire::test(AtletForm::class)->call('buka')
            ->set('tanggal_lahir', now()->addDay()->toDateString())
            ->set('kode_cabor', 'quidditch')
            ->set('status_binaan', 'Legenda')
            ->call('simpan')
            ->assertHasErrors(['nama_lengkap' => 'required', 'tanggal_lahir' => 'before', 'kode_cabor' => 'in', 'status_binaan' => 'in', 'jenis_kelamin' => 'required']);
    }

    public function test_pelatih_punya_lisensi_wajib_nomor_dan_berkas_serta_tidak_punya_membuang_berkas(): void
    {
        $form = $this->isiBiodata(Livewire::test(PelatihForm::class)->call('buka'), 'Pak Joko')
            ->set('punya_lisensi', 'ya')
            ->call('simpan')
            ->assertHasErrors(['nomor_lisensi' => 'required', 'fileBaru' => 'required']);

        $form->set('nomor_lisensi', 'LIS-123')
            ->set('fileBaru', UploadedFile::fake()->create('lisensi.pdf', 100, 'application/pdf'))
            ->call('simpan')
            ->assertHasNoErrors();

        $p = Pelatih::firstOrFail();
        $this->assertTrue($p->punya_lisensi);
        Storage::disk('public')->assertExists($p->file_lisensi);

        Livewire::test(PelatihForm::class)->call('buka', $p->id_pelatih)->set('punya_lisensi', 'tidak')->call('simpan')->assertHasNoErrors();

        Storage::disk('public')->assertMissing($p->file_lisensi);
        $this->assertNull($p->fresh()->file_lisensi);
        $this->assertNull($p->fresh()->nomor_lisensi);
    }

    public function test_cabor_unik_induk_otomatis_dan_jumlah_atlet_pelatih_sinkron(): void
    {
        Atlet::factory()->cabor('pencak-silat')->count(3)->create();
        Atlet::factory()->cabor('pencak-silat')->nonaktif()->create();
        Pelatih::factory()->cabor('pencak-silat')->create();

        Livewire::test(CaborForm::class)->call('buka')
            ->set('kode_cabor', 'pencak-silat')
            ->assertViewHas('induk', 'IPSI (Ikatan Pencak Silat Indonesia)')
            ->assertViewHas('jumlahAtlet', 3)
            ->assertViewHas('jumlahPelatih', 1)
            ->set('ketua_pengkab', 'H. Ahmad')
            ->set('alamat_sekretariat', 'GOR PKPSO')
            ->set('kecamatan_id', (string) $this->patrang->id)
            ->set('latitude', '-8,16')
            ->call('simpan')
            ->assertHasErrors(['longitude' => 'required_with'])
            ->set('longitude', '113,70')
            ->call('simpan')
            ->assertHasNoErrors();

        Livewire::test(CaborForm::class)->call('buka')->set('kode_cabor', 'pencak-silat')->call('simpan')->assertHasErrors(['kode_cabor' => 'unique']);

        $c = Cabor::denganJumlah()->firstOrFail();
        $this->assertSame(3, (int) $c->jumlah_atlet_aktif);
        $this->assertSame(1, (int) $c->jumlah_pelatih_aktif);
        $this->assertSame(-8.16, $c->latitude);

        $grafik = Livewire::test(CaborPage::class)->viewData('daftarGrafik')[0];
        $this->assertSame(['Pencak Silat'], $grafik['label']);
        $this->assertSame([4], $grafik['seri'][2]['data']);
    }

    public function test_daftar_atlet_filter_cabor_dan_cari_lewat_nama_cabor(): void
    {
        Atlet::factory()->cabor('renang')->create(['nama_lengkap' => 'Sari Renang']);
        Atlet::factory()->cabor('pencak-silat')->create(['nama_lengkap' => 'Andi Silat']);

        Livewire::test(AtletPage::class)
            ->set('cabor', 'renang')->assertSee('Sari Renang')->assertDontSee('Andi Silat')
            ->set('cabor', '')->set('cari', 'silat')->assertSee('Andi Silat')->assertDontSee('Sari Renang');
    }

    public function test_extract_pdf_dan_excel_sesuai_filter(): void
    {
        Atlet::factory()->cabor('renang')->create(['nama_lengkap' => 'Sari Renang']);
        $this->actingAs(User::factory()->create(['aktif' => true]));

        $this->get(route('admin.ppo.ekspor', ['jenis' => 'atlet', 'format' => 'pdf', 'cabor' => 'renang']))
            ->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload();

        $excel = $this->get(route('admin.ppo.ekspor', ['jenis' => 'atlet', 'format' => 'xlsx']));
        $excel->assertOk()->assertDownload();
        $this->assertStringStartsWith('PK', $excel->streamedContent()); // berkas .xlsx = arsip zip

        foreach (['pelatih', 'cabor', 'prestasi'] as $jenis) {
            $this->get(route('admin.ppo.ekspor', ['jenis' => $jenis, 'format' => 'xlsx']))->assertOk();
        }

        $this->get('/admin/ppo/ekspor/rahasia')->assertNotFound();
        $this->get(route('admin.ppo.ekspor', ['jenis' => 'atlet', 'format' => 'docx']))->assertSessionHasErrors('format');
    }

    public function test_prestasi_tambah_ubah_hapus_dan_grafik_per_tahun(): void
    {
        $halaman = Livewire::test(PrestasiPage::class)
            ->set('nama_atlet', 'Sari')->set('jenis_medali', 'Emas')->set('kode_cabor', 'renang')
            ->set('nomor_pertandingan', '50 m gaya bebas')->set('tahun', '2025')
            ->call('simpan')->assertHasNoErrors()->assertSet('nama_atlet', '');

        PrestasiAtlet::factory()->create(['tahun' => 2025, 'jenis_medali' => 'Perak']);
        PrestasiAtlet::factory()->create(['tahun' => 2024, 'jenis_medali' => 'Emas']);

        $grafik = PrestasiAtlet::dataGrafik()[0];
        $this->assertSame(['2024', '2025'], $grafik['label']);
        $this->assertSame([1, 2], $grafik['seri'][3]['data']); // total
        $this->assertSame([1, 1], $grafik['seri'][0]['data']); // emas

        $id = PrestasiAtlet::where('nama_atlet', 'Sari')->value('id_prestasi');
        $halaman->call('ubah', $id)->set('jenis_medali', 'Perunggu')->call('simpan')->assertHasNoErrors();
        $this->assertSame('Perunggu', PrestasiAtlet::find($id)->jenis_medali);

        $halaman->call('hapus', $id);
        $this->assertNull(PrestasiAtlet::find($id));

        $halaman->set('jenis_medali', 'Platinum')->call('simpan')->assertHasErrors(['jenis_medali' => 'in', 'nama_atlet' => 'required']);
    }

    public function test_galeri_prestasi_unggah_foto_video_dan_hapus_berkas(): void
    {
        Livewire::test(GaleriPrestasi::class)
            ->set('judul', 'Emas Porprov')->set('berkas', UploadedFile::fake()->image('emas.jpg'))->call('simpan')->assertHasNoErrors()
            ->set('judul', 'Video final')->set('berkas', UploadedFile::fake()->create('final.mp4', 3000, 'video/mp4'))->call('simpan')->assertHasNoErrors()
            ->set('judul', 'Salah')->set('berkas', UploadedFile::fake()->create('dok.pdf', 10, 'application/pdf'))->call('simpan')->assertHasErrors('berkas');

        $this->assertSame(['foto', 'video'], GaleriModel::orderBy('id_galeri_prestasi')->pluck('jenis')->all());

        $video = GaleriModel::where('jenis', 'video')->firstOrFail();
        Storage::disk('public')->assertExists($video->file);

        Livewire::test(GaleriPrestasi::class)->call('hapus', $video->id_galeri_prestasi);
        Storage::disk('public')->assertMissing($video->file);
        $this->assertSame(1, GaleriModel::count());
    }
}
