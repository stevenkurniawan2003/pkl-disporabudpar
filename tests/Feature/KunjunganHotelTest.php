<?php

namespace Tests\Feature;

use App\Livewire\Admin\KunjunganHotel as KunjunganPage;
use App\Livewire\Admin\KunjunganHotelForm;
use App\Models\Hotel;
use App\Models\KunjunganHotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KunjunganHotelTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $bintang;

    private Hotel $argopuro;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bintang = $this->buatHotel('Hotel Bintang');
        $this->argopuro = $this->buatHotel('Hotel Argopuro');
    }

    private function buatHotel(string $nama): Hotel
    {
        return Hotel::create([
            'nama_hotel' => $nama,
            'gambar_utama' => 'hotel/contoh.jpg',
            'deskripsi_singkat' => 'Hotel uji.',
            'narasi' => 'Narasi.',
            'alamat' => "Jl. {$nama}",
            'latitude' => -8.17,
            'longitude' => 113.7,
        ]);
    }

    private function kunjungan(Hotel $h, int $tahun, int $bulan, int $dom, int $man): KunjunganHotel
    {
        return KunjunganHotel::create([
            'hotel_id' => $h->id_hotel, 'tahun' => $tahun, 'bulan' => $bulan, 'domestik' => $dom, 'mancanegara' => $man,
        ]);
    }

    private function isiForm(?int $id = null)
    {
        return Livewire::test(KunjunganHotelForm::class)
            ->call('buka', $id)
            ->set('hotel_id', $this->bintang->id_hotel)
            ->set('bulan', 8)
            ->set('tahun', '2025')
            ->set('domestik', '2.140')
            ->set('mancanegara', '36');
    }

    public function test_halaman_wajib_login_dan_menu_tampil(): void
    {
        $this->get('/admin/kunjungan-hotel')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/kunjungan-hotel')
            ->assertOk()
            ->assertSee('Data Kunjungan Hotel')
            ->assertSee('+ Tambah data');
    }

    public function test_tambah_data_kunjungan_dan_relasi(): void
    {
        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin);

        $this->isiForm()
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('terbuka', false)
            ->assertDispatched('kunjungan-hotel-disimpan', pesan: 'Data kunjungan Hotel Bintang (Agustus 2025) ditambahkan.');

        $k = KunjunganHotel::firstOrFail();
        $this->assertSame(2140, $k->domestik);
        $this->assertSame(2176, $k->total);
        $this->assertSame($admin->id, $k->dicatat_oleh);
        $this->assertTrue($k->hotel->is($this->bintang));
        $this->assertSame(1, $this->bintang->kunjungan()->count());
    }

    public function test_tolak_duplikat_hotel_bulan_tahun(): void
    {
        $k = $this->kunjungan($this->bintang, 2025, 8, 10, 0);

        $this->isiForm()
            ->call('simpan')
            ->assertHasErrors(['hotel_id' => 'unique'])
            ->assertSee(KunjunganHotelForm::PESAN_DUPLIKAT);

        $this->isiForm()->set('hotel_id', $this->argopuro->id_hotel)->call('simpan')->assertHasNoErrors();

        // Ubah tanpa mengganti periode tidak dianggap duplikat.
        $this->isiForm($k->id)->set('domestik', '99')->call('simpan')->assertHasNoErrors();
        $this->assertSame(99, $k->fresh()->domestik);
    }

    public function test_validasi_wajib_dan_angka(): void
    {
        Livewire::test(KunjunganHotelForm::class)
            ->call('buka')
            ->set('bulan', null)
            ->set('tahun', '')
            ->call('simpan')
            ->assertHasErrors([
                'hotel_id' => 'required', 'bulan' => 'required', 'tahun' => 'required',
                'domestik' => 'required', 'mancanegara' => 'required',
            ]);

        $this->isiForm()->set('domestik', '-1')->set('mancanegara', 'abc')->call('simpan')
            ->assertHasErrors(['domestik' => 'min', 'mancanegara' => 'integer']);

        $this->isiForm()->set('hotel_id', 999)->call('simpan')->assertHasErrors(['hotel_id' => 'exists']);

        $this->assertSame(0, KunjunganHotel::count());
    }

    public function test_daftar_filter_ringkasan_detail_dan_hapus(): void
    {
        $a = $this->kunjungan($this->bintang, 2025, 8, 2140, 36);
        $this->kunjungan($this->bintang, 2025, 9, 1985, 41);
        $this->kunjungan($this->argopuro, 2025, 8, 1320, 12);

        Livewire::test(KunjunganPage::class)
            ->assertViewHas('totalDomestik', 5445)
            ->assertViewHas('totalMancanegara', 89)
            ->set('bulan', '8')
            ->assertViewHas('totalDomestik', 3460)
            ->set('hotel', (string) $this->bintang->id_hotel)
            ->assertViewHas('totalDomestik', 2140)
            ->assertSee('Agustus')
            ->assertSee('2.176')
            ->call('resetFilter')
            ->call('lihatDetail', $a->id)
            ->assertSee('Agustus 2025')
            ->call('ubahDariDetail')
            ->assertDispatched('buka-form-kunjungan-hotel', id: $a->id)
            ->call('hapus', $a->id)
            ->assertSet('jenisPesan', 'ok')
            ->assertSee('Data kunjungan Hotel Bintang (Agustus 2025) dihapus.');

        $this->assertModelMissing($a);
    }

    public function test_hapus_hotel_ikut_menghapus_data_kunjungannya(): void
    {
        $this->kunjungan($this->bintang, 2025, 8, 1, 1);

        $this->bintang->delete();

        $this->assertSame(0, KunjunganHotel::count());
    }
}
