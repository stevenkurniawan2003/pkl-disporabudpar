<?php

namespace Tests\Feature;

use App\Livewire\Admin\KunjunganWisata as KunjunganPage;
use App\Livewire\Admin\KunjunganWisataForm;
use App\Models\KategoriWisata;
use App\Models\Kecamatan;
use App\Models\KunjunganWisata;
use App\Models\User;
use App\Models\Wisata;
use Database\Seeders\KategoriSeeder;
use Database\Seeders\KecamatanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KunjunganWisataTest extends TestCase
{
    use RefreshDatabase;

    private Wisata $papuma;

    private Wisata $rembangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([KategoriSeeder::class, KecamatanSeeder::class]);
        $this->papuma = $this->buatWisata('Pantai Papuma');
        $this->rembangan = $this->buatWisata('Wisata Rembangan');
    }

    private function buatWisata(string $nama): Wisata
    {
        return Wisata::create([
            'kategori_id' => KategoriWisata::first()->id,
            'kecamatan_id' => Kecamatan::first()->id,
            'nama' => $nama,
            'slug' => str($nama)->slug(),
            'alamat' => 'Alamat',
            'latitude' => -8.4,
            'longitude' => 113.5,
        ]);
    }

    private function kunjungan(Wisata $w, int $tahun, int $bulan, int $dom, int $man): KunjunganWisata
    {
        return KunjunganWisata::create([
            'wisata_id' => $w->id, 'tahun' => $tahun, 'bulan' => $bulan, 'domestik' => $dom, 'mancanegara' => $man,
        ]);
    }

    private function isiForm(?int $id = null)
    {
        return Livewire::test(KunjunganWisataForm::class)
            ->call('buka', $id)
            ->set('wisata_id', $this->papuma->id)
            ->set('bulan', 1)
            ->set('tahun', '2025')
            ->set('domestik', '1.250')
            ->set('mancanegara', '30');
    }

    public function test_halaman_wajib_login_dan_menu_tampil(): void
    {
        $this->get('/admin/kunjungan-wisata')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/kunjungan-wisata')
            ->assertOk()
            ->assertSee('Kelola Data Kunjungan')
            ->assertSee('+ Tambah data');
    }

    public function test_tambah_data_kunjungan(): void
    {
        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin);

        $this->isiForm()
            ->set('keterangan', 'Libur tahun baru')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('terbuka', false)
            ->assertDispatched('kunjungan-wisata-disimpan', pesan: 'Data kunjungan Pantai Papuma (Januari 2025) ditambahkan.');

        $k = KunjunganWisata::firstOrFail();
        $this->assertSame(1250, $k->domestik);
        $this->assertSame(1280, $k->total);
        $this->assertSame($admin->id, $k->dicatat_oleh);
        $this->assertTrue($this->papuma->kunjungan()->exists());
    }

    public function test_tolak_duplikat_wisata_bulan_tahun(): void
    {
        $this->kunjungan($this->papuma, 2025, 1, 10, 0);

        $this->isiForm()
            ->call('simpan')
            ->assertHasErrors(['wisata_id' => 'unique'])
            ->assertSee(KunjunganWisataForm::PESAN_DUPLIKAT);

        // Wisata lain di periode yang sama boleh.
        $this->isiForm()->set('wisata_id', $this->rembangan->id)->call('simpan')->assertHasNoErrors();

        $this->assertSame(2, KunjunganWisata::count());
    }

    public function test_ubah_tanpa_mengganti_periode_tidak_dianggap_duplikat(): void
    {
        $k = $this->kunjungan($this->papuma, 2025, 1, 10, 0);
        $this->kunjungan($this->papuma, 2025, 2, 10, 0);

        $this->isiForm($k->id)->set('domestik', '99')->call('simpan')->assertHasNoErrors();
        $this->assertSame(99, $k->fresh()->domestik);

        // Dipindah ke periode yang sudah dipakai: ditolak.
        $this->isiForm($k->id)->set('bulan', 2)->call('simpan')->assertHasErrors(['wisata_id' => 'unique']);
    }

    public function test_validasi_wajib_angka_dan_periode(): void
    {
        Livewire::test(KunjunganWisataForm::class)
            ->call('buka')
            ->set('bulan', null)
            ->set('tahun', '')
            ->call('simpan')
            ->assertHasErrors([
                'wisata_id' => 'required', 'bulan' => 'required', 'tahun' => 'required',
                'domestik' => 'required', 'mancanegara' => 'required',
            ]);

        $this->isiForm()->set('domestik', '-5')->set('mancanegara', '2,5')->call('simpan')
            ->assertHasErrors(['domestik' => 'min', 'mancanegara' => 'integer']);

        $this->isiForm()->set('tahun', (string) (now()->year + 1))->call('simpan')
            ->assertHasErrors(['tahun' => 'between']);

        if (now()->month < 12) {
            $this->isiForm()->set('tahun', (string) now()->year)->set('bulan', now()->month + 1)->call('simpan')
                ->assertHasErrors('bulan');
        }

        $this->assertSame(0, KunjunganWisata::count());
    }

    public function test_tidak_bisa_memilih_wisata_yang_sudah_dihapus(): void
    {
        $this->rembangan->delete();

        $this->isiForm()->set('wisata_id', $this->rembangan->id)->call('simpan')
            ->assertHasErrors(['wisata_id' => 'exists']);
    }

    public function test_daftar_filter_ringkasan_detail_dan_hapus(): void
    {
        $a = $this->kunjungan($this->papuma, 2025, 1, 1000, 50);
        $this->kunjungan($this->papuma, 2025, 2, 2000, 20);
        $this->kunjungan($this->rembangan, 2025, 1, 300, 0);
        $this->kunjungan($this->papuma, 2024, 1, 7, 3);

        Livewire::test(KunjunganPage::class)
            ->assertViewHas('totalDomestik', 3307)
            ->assertViewHas('totalMancanegara', 73)
            ->set('tahun', '2025')
            ->assertViewHas('totalDomestik', 3300)
            ->set('bulan', '1')
            ->assertViewHas('totalDomestik', 1300)
            ->assertViewHas('totalMancanegara', 50)
            ->set('wisata', (string) $this->papuma->id)
            ->assertViewHas('totalDomestik', 1000)
            ->assertSee('Januari')
            ->assertSee('1.050')
            ->call('resetFilter')
            ->assertViewHas('totalDomestik', 3307)
            ->call('lihatDetail', $a->id)
            ->assertSee('Januari 2025')
            ->call('ubahDariDetail')
            ->assertDispatched('buka-form-kunjungan-wisata', id: $a->id)
            ->call('hapus', $a->id)
            ->assertSet('jenisPesan', 'ok')
            ->assertSee('Data kunjungan Pantai Papuma (Januari 2025) dihapus.')
            ->assertViewHas('totalDomestik', 2307);

        $this->assertModelMissing($a);
    }

    public function test_hapus_data_yang_tidak_ada_menampilkan_pesan_gagal(): void
    {
        Livewire::test(KunjunganPage::class)
            ->call('hapus', 999)
            ->assertSet('jenisPesan', 'err')
            ->assertSee('Data kunjungan tidak ditemukan');
    }
}
