<?php

namespace Tests\Feature;

use App\Livewire\Admin\Ekraf as EkrafPage;
use App\Livewire\Admin\EkrafForm;
use App\Models\Ekraf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EkrafTest extends TestCase
{
    use RefreshDatabase;

    private function buat(string $usaha, string $subsektor, string $alamat = 'Sumbersari'): Ekraf
    {
        return Ekraf::create([
            'nama_usaha' => $usaha,
            'nama_pemilik' => "Pemilik {$usaha}",
            'subsektor' => $subsektor,
            'alamat' => $alamat,
            'deskripsi' => "Deskripsi {$usaha}.",
        ]);
    }

    private function isiForm()
    {
        return Livewire::test(EkrafForm::class)
            ->call('buka')
            ->set('nama_usaha', 'Cokelat Raddina')
            ->set('nama_pemilik', 'Bu Raddina')
            ->set('subsektor', 'Kuliner')
            ->set('alamat', 'Jl. Kalimantan 10')
            ->set('deskripsi', 'Cokelat khas Jember.');
    }

    public function test_halaman_ekraf_wajib_login_dan_tampil(): void
    {
        $this->get('/admin/ekonomi-kreatif')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/ekonomi-kreatif')
            ->assertOk()
            ->assertSee('+ Tambah pelaku')
            ->assertSee('Ekraf');
    }

    public function test_tambah_ekraf_dengan_link_dirapikan(): void
    {
        $this->isiForm()
            ->set('no_telepon', '0812 3456 789')
            ->set('media_sosial', 'instagram.com/raddina')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('terbuka', false)
            ->assertDispatched('ekraf-disimpan', pesan: 'Data "Cokelat Raddina" ditambahkan.');

        $e = Ekraf::firstOrFail();
        $this->assertSame('https://instagram.com/raddina', $e->media_sosial);
        $this->assertSame('0812 3456 789', $e->no_telepon);
    }

    public function test_validasi_wajib_subsektor_link_dan_telepon(): void
    {
        Livewire::test(EkrafForm::class)
            ->call('buka')
            ->set('media_sosial', 'bukan url')
            ->set('no_telepon', 'abc')
            ->call('simpan')
            ->assertHasErrors([
                'nama_usaha' => 'required',
                'nama_pemilik' => 'required',
                'subsektor' => 'required',
                'alamat' => 'required',
                'deskripsi' => 'required',
                'media_sosial' => 'url',
                'no_telepon' => 'regex',
            ])
            ->set('subsektor', 'Bukan subsektor')
            ->call('simpan')
            ->assertHasErrors(['subsektor' => 'in']);

        $this->assertSame(0, Ekraf::count());
    }

    public function test_ubah_ekraf(): void
    {
        $e = $this->buat('Kripik Presto', 'Kuliner');

        Livewire::test(EkrafForm::class)
            ->call('buka', $e->id_ekraf)
            ->assertSet('nama_usaha', 'Kripik Presto')
            ->set('subsektor', 'Kriya')
            ->set('media_sosial', '')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('ekraf-disimpan', pesan: 'Data "Kripik Presto" diperbarui.');

        $this->assertSame('Kriya', $e->fresh()->subsektor);
        $this->assertNull($e->fresh()->media_sosial);
    }

    public function test_daftar_cari_filter_detail_dan_hapus(): void
    {
        $cokelat = $this->buat('Cokelat Raddina', 'Kuliner', 'Kaliwates');
        $this->buat('Pengrajin Kentongan', 'Kriya', 'Ambulu');

        Livewire::test(EkrafPage::class)
            ->assertSee('Cokelat Raddina')
            ->assertSee('Pengrajin Kentongan')
            ->set('subsektor', 'Kriya')
            ->assertDontSee('Cokelat Raddina')
            ->set('subsektor', '')
            ->set('cari', 'Pemilik Cokelat')
            ->assertSee('Cokelat Raddina')
            ->assertDontSee('Pengrajin Kentongan')
            ->call('lihatDetail', $cokelat->id_ekraf)
            ->assertSee('Deskripsi Cokelat Raddina.')
            ->call('ubahDariDetail')
            ->assertDispatched('buka-form-ekraf', id: $cokelat->id_ekraf)
            ->call('hapus', $cokelat->id_ekraf)
            ->assertSet('jenisPesan', 'ok')
            ->assertSee('Data &quot;Cokelat Raddina&quot; dihapus.', false);

        $this->assertModelMissing($cokelat);
    }

    public function test_hapus_data_yang_tidak_ada_menampilkan_pesan_gagal(): void
    {
        Livewire::test(EkrafPage::class)
            ->call('hapus', 999)
            ->assertSet('jenisPesan', 'err')
            ->assertSee('Data ekraf tidak ditemukan');
    }
}
