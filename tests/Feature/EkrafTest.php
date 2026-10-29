<?php

namespace Tests\Feature;

use App\Livewire\Admin\CetakEkraf;
use App\Livewire\Admin\Ekraf as EkrafPage;
use App\Livewire\Admin\EkrafForm;
use App\Models\Desa;
use App\Models\Ekraf;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EkrafTest extends TestCase
{
    use RefreshDatabase;

    private Kecamatan $mayang;

    private Desa $tegalwaru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mayang = Kecamatan::create(['nama' => 'Mayang']);
        $this->tegalwaru = Desa::create(['kecamatan_id' => $this->mayang->id, 'nama' => 'Tegalwaru']);
    }

    private function isiForm(string $subsektor = '1.8', string $nama = 'Tas Anyaman Kasmirah')
    {
        return Livewire::test(EkrafForm::class)
            ->call('buka')
            ->set('kode_subsektor', $subsektor)
            ->set('nama_usaha', $nama)
            ->set('alamat', 'Jln. Dusun Sumberpinang RT 003/RW 004')
            ->set('kecamatan_id', (string) $this->mayang->id)
            ->set('desa_id', (string) $this->tegalwaru->id)
            ->set('omzet', 8000000);
    }

    public function test_sistem_ekraf_wajib_login_dan_tampil_di_sidebar(): void
    {
        $this->get('/admin/ekonomi-kreatif')->assertRedirect(route('admin.login'));
        $this->get('/admin/cetak-ekraf/pdf')->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create(['aktif' => true]))->get('/admin/ekonomi-kreatif')
            ->assertOk()
            ->assertSee('+ Input Data Master')
            ->assertSee('Data Master EKRAF')
            ->assertSee('Cetak Data EKRAF');
    }

    public function test_id_usaha_otomatis_berurutan_per_subsektor(): void
    {
        $this->isiForm('1.8', 'Tas A')->call('simpan')->assertHasNoErrors()
            ->assertDispatched('ekraf-disimpan', pesan: 'Data "Tas A" ditambahkan dengan ID 1.8-001.');
        $this->isiForm('1.8', 'Tas B')->call('simpan');
        $this->isiForm('1.17', 'Mural')->call('simpan');

        $this->assertSame(['1.8-001', '1.8-002', '1.17-001'], Ekraf::orderBy('id_ekraf')->pluck('nomor_ekraf')->all());
        $this->assertSame(8000000, Ekraf::first()->omzet);
        $this->assertSame('aktif', Ekraf::first()->status);
    }

    public function test_pratinjau_id_dan_omzet_berformat_rupiah_diterima(): void
    {
        Ekraf::factory()->subsektor('1.8')->create(['nomor_urut' => 46, 'nomor_ekraf' => '1.8-046']);

        $this->isiForm('1.8')->assertViewHas('nomor', '1.8-047')
            ->set('omzet', 'Rp 2.500.000')->call('simpan')->assertHasNoErrors();

        $this->assertSame(2500000, Ekraf::where('nomor_ekraf', '1.8-047')->value('omzet'));
    }

    public function test_validasi_wajib_subsektor_dan_desa(): void
    {
        $lain = Kecamatan::create(['nama' => 'Patrang']);
        $desaLain = Desa::create(['kecamatan_id' => $lain->id, 'nama' => 'Slawu']);

        Livewire::test(EkrafForm::class)->call('buka')->call('simpan')
            ->assertHasErrors(['kode_subsektor' => 'required', 'nama_usaha' => 'required', 'alamat' => 'required', 'kecamatan_id' => 'required', 'desa_id' => 'required']);

        $this->isiForm()->set('desa_id', (string) $desaLain->id)->call('simpan')->assertHasErrors(['desa_id' => 'exists']);
        $this->isiForm()->set('kode_subsektor', 'Kuliner')->call('simpan')->assertHasErrors(['kode_subsektor' => 'in']);
        $this->isiForm()->set('no_telepon', 'abc')->call('simpan')->assertHasErrors(['no_telepon' => 'regex']);
        $this->assertSame(0, Ekraf::count());
    }

    public function test_ubah_subsektor_membuat_id_baru(): void
    {
        $e = Ekraf::factory()->subsektor('1.8')->create(['nomor_urut' => 5, 'nomor_ekraf' => '1.8-005']);

        Livewire::test(EkrafForm::class)->call('buka', $e->id_ekraf)->set('nama_usaha', 'Nama Baru')->call('simpan')->assertHasNoErrors();
        $this->assertSame('1.8-005', $e->fresh()->nomor_ekraf);

        Livewire::test(EkrafForm::class)->call('buka', $e->id_ekraf)->set('kode_subsektor', '1.9')->call('simpan')->assertHasNoErrors();
        $this->assertSame('1.9-001', $e->fresh()->nomor_ekraf);
    }

    public function test_daftar_ringkasan_cari_dan_hapus(): void
    {
        Ekraf::factory()->count(2)->create();
        Ekraf::factory()->nonaktif()->create(['nama_usaha' => 'Studio Foto Lama']);

        $halaman = Livewire::test(EkrafPage::class)
            ->assertViewHas('jumlahAktif', 2)
            ->assertViewHas('jumlahNonaktif', 1)
            ->set('cari', 'Studio')->assertSee('Studio Foto Lama');

        $this->assertSame(1, $halaman->viewData('daftar')->total());

        $id = Ekraf::where('nama_usaha', 'Studio Foto Lama')->value('id_ekraf');
        $halaman->call('hapus', $id)->assertSet('pesan', 'Data "Studio Foto Lama" dihapus.');
        $this->assertSame(2, Ekraf::count());

        $halaman->call('hapus', $id)->assertSet('jenisPesan', 'err');
    }

    public function test_cetak_menyaring_dan_menghasilkan_pdf(): void
    {
        Ekraf::factory()->subsektor('1.9')->create(['nama_usaha' => 'Kuliner Aktif']);
        Ekraf::factory()->subsektor('1.9')->nonaktif()->create(['nama_usaha' => 'Kuliner Tutup']);
        Ekraf::factory()->subsektor('1.8')->create(['nama_usaha' => 'Kerajinan']);

        Livewire::test(CetakEkraf::class)
            ->set('subsektor', '1.9')->set('status', 'aktif')
            ->assertViewHas('jumlah', 1)
            ->assertSee('Kuliner Aktif')->assertDontSee('Kuliner Tutup')
            ->call('resetFilter')->assertViewHas('jumlah', 3);

        $this->actingAs(User::factory()->create(['aktif' => true]))
            ->get(route('admin.cetak-ekraf.pdf', ['subsektor' => '1.9', 'unduh' => 1]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload();

        $this->get(route('admin.cetak-ekraf.pdf', ['status' => 'hilang']))->assertSessionHasErrors('status');
    }
}
