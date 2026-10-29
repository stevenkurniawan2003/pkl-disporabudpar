<?php

namespace Tests\Feature;

use App\Livewire\Admin\CetakKesenian;
use App\Livewire\Admin\Kesenian as KesenianPage;
use App\Livewire\Admin\KesenianForm;
use App\Livewire\Admin\PerpanjanganKik as PerpanjanganPage;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kesenian;
use App\Models\PerpanjanganKik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class KesenianTest extends TestCase
{
    use RefreshDatabase;

    private Kecamatan $ajung;

    private Desa $klompangan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->ajung = Kecamatan::create(['nama' => 'Ajung']);
        $this->klompangan = Desa::create(['kecamatan_id' => $this->ajung->id, 'nama' => 'Klompangan']);
    }

    private function isiForm(string $jenis = '2.5', string $nama = 'Agung Sejati')
    {
        return Livewire::test(KesenianForm::class)
            ->call('buka')
            ->set('kode_jenis', $jenis)
            ->set('nama_kesenian', $nama)
            ->set('nama_pemilik', 'Dimas Santoso')
            ->set('alamat', 'Dusun Krajan 01')
            ->set('kecamatan_id', (string) $this->ajung->id)
            ->set('desa_id', (string) $this->klompangan->id)
            ->set('jumlah_anggota', '30');
    }

    public function test_menu_kebudayaan_wajib_login_dan_tampil_di_sidebar(): void
    {
        $this->get('/admin/kesenian')->assertRedirect(route('admin.login'));
        $this->get('/admin/cetak-kesenian/pdf')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/kesenian')
            ->assertOk()
            ->assertSee('+ Input Data Master')
            ->assertSee('Kebudayaan')
            ->assertSee('Objek Pemajuan Kebudayaan')
            ->assertSee('Perpanjangan KIK');
    }

    public function test_nomor_induk_otomatis_berurutan_per_jenis(): void
    {
        $this->isiForm('2.5', 'Reog A')->call('simpan')->assertHasNoErrors()
            ->assertDispatched('kesenian-disimpan', pesan: 'Kesenian "Reog A" ditambahkan dengan nomor induk 2.5-001.');
        $this->isiForm('2.5', 'Reog B')->call('simpan');
        $this->isiForm('1.2', 'Patrol A')->call('simpan');

        $this->assertSame(['2.5-001', '2.5-002', '1.2-001'], Kesenian::orderBy('id_kesenian')->pluck('nomor_induk')->all());
        $this->assertSame('Jember', Kesenian::first()->kabupaten);
    }

    public function test_form_menampilkan_pratinjau_nomor_induk(): void
    {
        Kesenian::factory()->jenis('2.5')->create(['nomor_urut' => 47, 'nomor_induk' => '2.5-047']);

        $this->isiForm('2.5')->assertViewHas('nomorInduk', '2.5-048');
    }

    public function test_validasi_wajib_dan_desa_harus_di_kecamatan_terpilih(): void
    {
        $lain = Kecamatan::create(['nama' => 'Kalisat']);
        $desaLain = Desa::create(['kecamatan_id' => $lain->id, 'nama' => 'Gambiran']);

        Livewire::test(KesenianForm::class)->call('buka')->call('simpan')
            ->assertHasErrors(['kode_jenis' => 'required', 'nama_kesenian' => 'required', 'kecamatan_id' => 'required', 'desa_id' => 'required', 'jumlah_anggota' => 'required']);

        $this->isiForm()->set('desa_id', (string) $desaLain->id)->call('simpan')->assertHasErrors(['desa_id' => 'exists']);
        $this->isiForm()->set('kode_jenis', '9.9')->call('simpan')->assertHasErrors(['kode_jenis' => 'in']);
        $this->assertSame(0, Kesenian::count());
    }

    public function test_ganti_kecamatan_mengosongkan_desa(): void
    {
        $this->isiForm()->set('kecamatan_id', (string) Kecamatan::create(['nama' => 'Kalisat'])->id)->assertSet('desa_id', '');
    }

    public function test_ubah_tanpa_ganti_jenis_mempertahankan_nomor_dan_ganti_jenis_membuat_nomor_baru(): void
    {
        $k = Kesenian::factory()->jenis('2.5')->create(['nomor_urut' => 3, 'nomor_induk' => '2.5-003']);
        Kesenian::factory()->jenis('2.6')->create(['nomor_urut' => 9, 'nomor_induk' => '2.6-009']);

        Livewire::test(KesenianForm::class)->call('buka', $k->id_kesenian)->set('nama_kesenian', 'Nama Baru')->call('simpan')->assertHasNoErrors();
        $this->assertSame('2.5-003', $k->fresh()->nomor_induk);

        Livewire::test(KesenianForm::class)->call('buka', $k->id_kesenian)->set('kode_jenis', '2.6')->call('simpan')->assertHasNoErrors();
        $this->assertSame('2.6-010', $k->fresh()->nomor_induk);
    }

    public function test_cari_berdasarkan_kolom_terpilih(): void
    {
        Kesenian::factory()->create(['nama_kesenian' => 'Arken', 'nama_pemilik' => 'Sunarto']);
        Kesenian::factory()->create(['nama_kesenian' => 'Larkam', 'nama_pemilik' => 'Juhari']);

        Livewire::test(KesenianPage::class)
            ->set('cari', 'Sunarto')->assertDontSee('Arken')
            ->set('kolom', 'nama_pemilik')->assertSee('Arken')->assertDontSee('Larkam');
    }

    public function test_hapus_kesenian_ikut_menghapus_perpanjangan_dan_file(): void
    {
        Storage::disk('public')->put('kesenian/dok.jpg', 'x');
        Storage::disk('public')->put('kesenian/ketua/a.jpg', 'x');
        $k = Kesenian::factory()->create(['dokumentasi' => 'kesenian/dok.jpg']);
        PerpanjanganKik::create(['id_kesenian' => $k->id_kesenian, 'tanggal_pengajuan' => '2025-12-17', 'berlaku_sampai' => '2027-12-17', 'foto_ketua' => 'kesenian/ketua/a.jpg']);

        Livewire::test(KesenianPage::class)->call('hapus', $k->id_kesenian)->assertSet('jenisPesan', 'ok');

        $this->assertSame(0, PerpanjanganKik::count());
        Storage::disk('public')->assertMissing(['kesenian/dok.jpg', 'kesenian/ketua/a.jpg']);
    }

    public function test_perpanjangan_cek_nomor_induk(): void
    {
        $k = Kesenian::factory()->create();

        Livewire::test(PerpanjanganPage::class)
            ->set('nomorInduk', '9.9-999')->call('cek')->assertHasErrors('nomorInduk')->assertSet('kesenianId', null)
            ->set('nomorInduk', $k->nomor_induk)->call('cek')->assertHasNoErrors()->assertSet('kesenianId', $k->id_kesenian)
            ->assertSee($k->nama_kesenian)
            ->set('nomorInduk', 'x')->assertSet('kesenianId', null);
    }

    public function test_ajukan_perpanjangan_menyimpan_masa_berlaku_dan_foto(): void
    {
        $k = Kesenian::factory()->create();

        Livewire::test(PerpanjanganPage::class)
            ->set('nomorInduk', $k->nomor_induk)->call('cek')
            ->set('tanggal_pengajuan', '2025-12-17')
            ->set('tahun_berakhir', '2027')
            ->set('fotoKetua', UploadedFile::fake()->image('ketua.jpg'))
            ->call('ajukan')
            ->assertHasNoErrors()
            ->assertSet('kesenianId', null)
            ->assertSet('jenisPesan', 'ok');

        $p = PerpanjanganKik::firstOrFail();
        $this->assertSame('2025-12-17', $p->tanggal_pengajuan->toDateString());
        $this->assertSame('2027-12-17', $p->berlaku_sampai->toDateString());
        Storage::disk('public')->assertExists($p->foto_ketua);
    }

    public function test_ajukan_perpanjangan_wajib_cek_foto_dan_tahun_valid(): void
    {
        $k = Kesenian::factory()->create();

        Livewire::test(PerpanjanganPage::class)->call('ajukan')->assertHasErrors('nomorInduk');

        Livewire::test(PerpanjanganPage::class)
            ->set('nomorInduk', $k->nomor_induk)->call('cek')
            ->set('tanggal_pengajuan', '2025-12-17')
            ->set('tahun_berakhir', '2025')
            ->call('ajukan')
            ->assertHasErrors(['fotoKetua' => 'required', 'tahun_berakhir' => 'between']);

        $this->assertSame(0, PerpanjanganKik::count());
    }

    public function test_unduh_kartu_kik_berupa_pdf(): void
    {
        Storage::disk('public')->put('kesenian/ketua/a.jpg', UploadedFile::fake()->image('a.jpg')->getContent());
        $k = Kesenian::factory()->create();
        $p = PerpanjanganKik::create(['id_kesenian' => $k->id_kesenian, 'tanggal_pengajuan' => '2025-12-17', 'berlaku_sampai' => '2027-12-17', 'foto_ketua' => 'kesenian/ketua/a.jpg']);

        $this->actingAs(User::factory()->create(['aktif' => true]))
            ->get(route('admin.kartu-kik', $p))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_cetak_menyaring_data_dan_menghasilkan_pdf(): void
    {
        Kesenian::factory()->jenis('1.2')->create(['nama_kesenian' => 'Patrol Aktif']);
        Kesenian::factory()->jenis('1.2')->nonaktif()->create(['nama_kesenian' => 'Patrol Mati']);
        Kesenian::factory()->jenis('2.5')->create(['nama_kesenian' => 'Reog']);

        Livewire::test(CetakKesenian::class)
            ->set('jenis', '1.2')->set('status', 'aktif')
            ->assertViewHas('jumlah', 1)
            ->assertSee('Patrol Aktif')->assertDontSee('Patrol Mati')->assertDontSee('>Reog<', false)
            ->call('resetFilter')->assertViewHas('jumlah', 3);

        $this->actingAs(User::factory()->create(['aktif' => true]))
            ->get(route('admin.cetak-kesenian.pdf', ['jenis' => '1.2', 'unduh' => 1]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload();

        $this->get(route('admin.cetak-kesenian.pdf', ['jenis' => 'ngawur']))->assertSessionHasErrors('jenis');
    }
}
