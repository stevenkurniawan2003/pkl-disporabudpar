<?php

namespace Tests\Feature;

use App\Livewire\Admin\Ebooklet as EbookletPage;
use App\Livewire\Admin\EkrafForm;
use App\Livewire\Admin\LokasiPeta as LokasiPage;
use App\Livewire\Admin\LokasiPetaForm;
use App\Livewire\Admin\PaketWisataForm;
use App\Livewire\Publik\KunjunganWisata;
use App\Models\Desa;
use App\Models\Ebooklet;
use App\Models\Ekraf;
use App\Models\KategoriWisata;
use App\Models\Kecamatan;
use App\Models\KunjunganWisata as KunjunganModel;
use App\Models\LokasiPeta;
use App\Models\PaketWisata;
use App\Models\User;
use App\Models\Wisata;
use Database\Seeders\KategoriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PariwisataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(KategoriSeeder::class);
    }

    private function wisata(string $nama, string $kategori = 'pantai', array $atribut = []): Wisata
    {
        return Wisata::create([
            'kategori_id' => KategoriWisata::where('slug', $kategori)->value('id'),
            'kecamatan_id' => Kecamatan::firstOrCreate(['nama' => 'Wuluhan'])->id,
            'nama' => $nama,
            'slug' => str($nama)->slug(),
            'deskripsi_singkat' => "Singkat {$nama}.",
            'narasi' => "Narasi {$nama}.",
            'alamat' => 'Lojejer',
            'latitude' => -8.43,
            'longitude' => 113.55,
            'status' => 'tayang',
            ...$atribut,
        ]);
    }

    public function test_menu_pariwisata_admin_wajib_login(): void
    {
        foreach (['/admin/lokasi-peta', '/admin/paket-wisata', '/admin/ebooklet'] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }

        $this->actingAs(User::factory()->create(['aktif' => true]))
            ->get('/admin/lokasi-peta')->assertOk()->assertSee('Pariwisata')->assertSee('+ Tambah lokasi');
    }

    public function test_tambah_lokasi_peta_dan_validasi(): void
    {
        Livewire::test(LokasiPetaForm::class)->call('buka')->set('kategori', 'kafe')->call('simpan')
            ->assertHasErrors(['nama' => 'required', 'kategori' => 'in', 'latitude' => 'required']);

        Livewire::test(LokasiPetaForm::class)->call('buka')
            ->set('nama', 'Pusat Oleh-oleh Prol Tape')->set('kategori', 'oleh-oleh')->set('alamat', 'Jl. Trunojoyo')
            ->set('latitude', '-8,17')->set('longitude', '113,70')
            ->call('simpan')->assertHasNoErrors()->assertDispatched('lokasi-disimpan');

        $l = LokasiPeta::firstOrFail();
        $this->assertSame(-8.17, $l->latitude);
        $this->assertSame('Pusat Oleh-oleh', $l->kategori_label);

        Livewire::test(LokasiPage::class)->call('hapus', $l->id_lokasi);
        $this->assertSame(0, LokasiPeta::count());
    }

    public function test_titik_ekraf_opsional_tapi_harus_berpasangan(): void
    {
        $kc = Kecamatan::create(['nama' => 'Mayang']);
        $desa = Desa::create(['kecamatan_id' => $kc->id, 'nama' => 'Tegalwaru']);
        $isi = fn () => Livewire::test(EkrafForm::class)->call('buka')
            ->set('kode_subsektor', '1.8')->set('nama_usaha', 'Batik')->set('alamat', 'Jl. A')
            ->set('kecamatan_id', (string) $kc->id)->set('desa_id', (string) $desa->id);

        $isi()->set('latitude', '-8.2')->call('simpan')->assertHasErrors(['longitude' => 'required_with']);
        $isi()->call('simpan')->assertHasNoErrors();
        $isi()->set('latitude', '-8,2')->set('longitude', '113,6')->call('simpan')->assertHasNoErrors();

        $this->assertSame([null, -8.2], Ekraf::orderBy('id_ekraf')->pluck('latitude')->all());
    }

    public function test_paket_wisata_wajib_flyer_dan_hanya_yang_tayang_di_publik(): void
    {
        Livewire::test(PaketWisataForm::class)->call('buka')->set('nama_paket', 'X')->call('simpan')
            ->assertHasErrors(['flyerBaru' => 'required', 'nama_cp' => 'required', 'no_cp' => 'required', 'deskripsi' => 'required']);

        Livewire::test(PaketWisataForm::class)->call('buka')
            ->set('nama_paket', 'Paket Papuma 2 Hari')->set('deskripsi', 'Pantai Papuma dan Watu Ulo.')
            ->set('nama_cp', 'Bu Rina')->set('no_cp', '0812-3456-7890')
            ->set('flyerBaru', UploadedFile::fake()->image('flyer.jpg'))
            ->call('simpan')->assertHasNoErrors();

        PaketWisata::create(['nama_paket' => 'Paket Rahasia', 'flyer' => 'paket-wisata/x.jpg', 'deskripsi' => 'x', 'nama_cp' => 'A', 'no_cp' => '0811', 'tayang' => false]);

        $this->assertSame('https://wa.me/6281234567890', PaketWisata::where('nama_paket', 'Paket Papuma 2 Hari')->first()->tautan_wa);

        $this->get('/publik/paket-wisata')->assertOk()
            ->assertSee('Paket Papuma 2 Hari')->assertSee('https://wa.me/6281234567890')->assertDontSee('Paket Rahasia');
        $this->get('/publik/peta-wisata')->assertOk()->assertSee('Paket Papuma 2 Hari');
    }

    public function test_ebooklet_admin_unggah_pdf(): void
    {
        Livewire::test(EbookletPage::class)
            ->set('judul', 'Panduan')->set('pdf', UploadedFile::fake()->image('bukan.jpg'))->call('simpan')->assertHasErrors(['pdf' => 'mimes'])
            ->set('judul', 'Jember Travel Guide 2026')->set('pdf', UploadedFile::fake()->create('guide.pdf', 500, 'application/pdf'))
            ->call('simpan')->assertHasNoErrors()->assertSet('judul', '');

        $e = Ebooklet::firstOrFail();
        Storage::disk('public')->assertExists($e->file_pdf);
    }

    public function test_ebooklet_publik_menautkan_google_drive_dari_konfigurasi(): void
    {
        config(['site.ebooklet_url' => '']);
        $this->get('/publik/ebooklet')->assertOk()->assertSee('E-booklet sedang dalam proses pembuatan.');

        config(['site.ebooklet_url' => 'https://drive.google.com/file/d/contoh/view']);

        $this->get('/publik/ebooklet')->assertOk()
            ->assertSee('href="https://drive.google.com/file/d/contoh/view"', false)
            ->assertSee('Baca Selengkapnya');
    }

    public function test_desa_wisata_menampilkan_sk_bupati(): void
    {
        $this->wisata('Desa Wisata Tutul', 'desa-wisata', ['nomor_sk_desa_wisata' => '188.45/321/1.12/2024']);
        $this->wisata('Papuma');

        $this->get('/publik/desa-wisata')->assertOk()
            ->assertSee('Desa Wisata Tutul')->assertSee('SK Bupati 188.45/321/1.12/2024')->assertDontSee('Papuma');
    }

    public function test_foto_destinasi_di_bawah_grafik_kunjungan_dan_bisa_dipilih(): void
    {
        $papuma = $this->wisata('Papuma', 'pantai', ['gambar_utama' => 'wisata/papuma.jpg']);
        $rembangan = $this->wisata('Rembangan', 'pegunungan');
        KunjunganModel::create(['wisata_id' => $papuma->id, 'tahun' => 2025, 'bulan' => 1, 'domestik' => 100, 'mancanegara' => 5]);
        KunjunganModel::create(['wisata_id' => $rembangan->id, 'tahun' => 2025, 'bulan' => 1, 'domestik' => 40, 'mancanegara' => 0]);

        Livewire::test(KunjunganWisata::class)
            ->assertSee('Pilih destinasi wisata')
            ->assertSee($papuma->gambar_url, false)
            ->assertViewHas('totalPerTempat', fn ($t) => $t[$papuma->id] === 105 && $t[$rembangan->id] === 40)
            ->call('$set', 'pilihan', $rembangan->id)
            ->assertSet('pilihan', $rembangan->id)
            ->assertDispatched('grafik-kunjungan')
            ->assertViewHas('totalDomestik', 40)
            // Kartu foto tetap menampilkan total semua tempat walau satu tempat dipilih.
            ->assertViewHas('totalPerTempat', fn ($t) => $t[$papuma->id] === 105);
    }
}
