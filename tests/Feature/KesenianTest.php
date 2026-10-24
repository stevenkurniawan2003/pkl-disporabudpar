<?php

namespace Tests\Feature;

use App\Livewire\Admin\Kesenian as KesenianPage;
use App\Livewire\Admin\KesenianForm;
use App\Models\Kesenian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KesenianTest extends TestCase
{
    use RefreshDatabase;

    private function buat(string $nama, string $kategori, ?string $asal = null): Kesenian
    {
        return Kesenian::create([
            'nama_kesenian' => $nama,
            'kategori' => $kategori,
            'asal_daerah' => $asal,
            'deskripsi' => "Deskripsi {$nama}.",
        ]);
    }

    public function test_halaman_kesenian_wajib_login_dan_muncul_di_sidebar(): void
    {
        $this->get('/admin/kesenian')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/kesenian')
            ->assertOk()
            ->assertSee('+ Tambah kesenian')
            ->assertSee('Data Master');
    }

    public function test_tambah_kesenian(): void
    {
        Livewire::test(KesenianForm::class)
            ->call('buka')
            ->set('nama_kesenian', '  Tari Lahbako ')
            ->set('kategori', 'Tari')
            ->set('asal_daerah', '')
            ->set('deskripsi', 'Tari tentang petani tembakau.')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('terbuka', false)
            ->assertDispatched('kesenian-disimpan', pesan: 'Kesenian "Tari Lahbako" ditambahkan.');

        $k = Kesenian::firstOrFail();
        $this->assertSame('Tari Lahbako', $k->nama_kesenian);
        $this->assertNull($k->asal_daerah);
    }

    public function test_validasi_field_wajib_dan_kategori(): void
    {
        Livewire::test(KesenianForm::class)
            ->call('buka')
            ->call('simpan')
            ->assertHasErrors(['nama_kesenian' => 'required', 'kategori' => 'required', 'deskripsi' => 'required'])
            ->assertSee('Nama kesenian wajib diisi.')
            ->set('kategori', 'Bukan kategori')
            ->call('simpan')
            ->assertHasErrors(['kategori' => 'in']);

        $this->assertSame(0, Kesenian::count());
    }

    public function test_ubah_kesenian(): void
    {
        $k = $this->buat('Reog', 'Tari', 'Kec. Puger');

        Livewire::test(KesenianForm::class)
            ->call('buka', $k->id_kesenian)
            ->assertSet('nama_kesenian', 'Reog')
            ->assertSet('asal_daerah', 'Kec. Puger')
            ->set('kategori', 'Teater')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('kesenian-disimpan', pesan: 'Kesenian "Reog" diperbarui.');

        $this->assertSame('Teater', $k->fresh()->kategori);
    }

    public function test_daftar_cari_filter_detail_dan_hapus(): void
    {
        $tari = $this->buat('Tari Lahbako', 'Tari', 'Kec. Kalisat');
        $this->buat('Musik Patrol', 'Musik', 'Kec. Sumbersari');

        Livewire::test(KesenianPage::class)
            ->assertSee('Tari Lahbako')
            ->assertSee('Musik Patrol')
            ->set('kategori', 'Musik')
            ->assertDontSee('Tari Lahbako')
            ->assertSee('Musik Patrol')
            ->set('kategori', '')
            ->set('cari', 'Kalisat')
            ->assertSee('Tari Lahbako')
            ->assertDontSee('Musik Patrol')
            ->call('lihatDetail', $tari->id_kesenian)
            ->assertSee('Deskripsi Tari Lahbako.')
            ->call('ubahDariDetail')
            ->assertDispatched('buka-form-kesenian', id: $tari->id_kesenian)
            ->call('hapus', $tari->id_kesenian)
            ->assertSet('jenisPesan', 'ok')
            ->assertSee('Kesenian &quot;Tari Lahbako&quot; dihapus.', false);

        $this->assertModelMissing($tari);
    }

    public function test_hapus_kesenian_yang_tidak_ada_menampilkan_pesan_gagal(): void
    {
        Livewire::test(KesenianPage::class)
            ->call('hapus', 999)
            ->assertSet('jenisPesan', 'err')
            ->assertSee('Kesenian tidak ditemukan');
    }
}
