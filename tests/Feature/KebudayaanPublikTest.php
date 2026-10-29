<?php

namespace Tests\Feature;

use App\Livewire\Publik\DataKesenian;
use App\Livewire\Publik\Kebudayaan;
use App\Models\CagarBudaya;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kesenian;
use App\Models\Opk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KebudayaanPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_kartu_per_kelompok_dan_grafik_garis(): void
    {
        Kesenian::factory()->jenis('1.2')->create();
        Kesenian::factory()->jenis('2.5')->count(2)->create();
        Kesenian::factory()->jenis('2.6')->nonaktif()->create();
        Opk::factory()->create(['jenis' => 'Ritus']);
        CagarBudaya::factory()->create(['kategori' => 'Bangunan', 'status' => 'CB']);

        $this->get('/publik/kebudayaan')
            ->assertOk()
            ->assertSee('<title>Kebudayaan · Disporabudpar Jember</title>', false)
            ->assertSee('Rincian data kesenian')
            ->assertSee('chart.umd.min.js', false)
            ->assertSee('id="grafik-kecamatan-kesenian"', false)
            ->assertSee('type: &#039;line&#039;', false);

        $komponen = Livewire::test(Kebudayaan::class)
            ->assertViewHas('totalKesenian', 4)
            ->assertViewHas('totalAktif', 3)
            ->assertViewHas('totalOpk', 1)
            ->assertViewHas('totalCagar', 1)
            ->assertViewHas('perKelompok', fn ($k) => $k->pluck('jumlah', 'nama')->all() === ['Seni Musik' => 1, 'Seni Pertunjukan' => 3, 'Seni Sastra Tradisi' => 0]);

        $grafik = collect($komponen->viewData('daftarGrafik'))->keyBy('id');
        $this->assertSame(1, $grafik['grafik-opk']['seri'][0]['data'][array_search('Ritus', Opk::JENIS, true)]);
        $cb = collect($grafik['grafik-cagar-budaya']['seri'])->firstWhere('label', 'CB (Cagar Budaya)');
        $this->assertSame([0, 1, 0, 0, 0], $cb['data']);
    }

    public function test_menu_kebudayaan_berupa_dropdown(): void
    {
        $this->get('/publik/kebudayaan/opk')
            ->assertOk()
            ->assertSee('Kebudayaan ▾')
            ->assertSee('class="nb on" href="'.route('publik.opk').'"', false);
    }

    public function test_data_kesenian_publik_menyaring_wilayah_dan_menyembunyikan_data_pribadi(): void
    {
        $kalisat = Kecamatan::create(['nama' => 'Kalisat']);
        $gambiran = Desa::create(['kecamatan_id' => $kalisat->id, 'nama' => 'Gambiran']);
        Kesenian::factory()->create(['nama_kesenian' => 'Sanggar Kota', 'nama_pemilik' => 'Rahasia Pemilik', 'no_telepon' => '081299990000']);
        Kesenian::factory()->create(['nama_kesenian' => 'Sanggar Desa', 'kecamatan_id' => $kalisat->id, 'desa_id' => $gambiran->id]);

        $this->get('/publik/kebudayaan/kesenian')
            ->assertOk()
            ->assertSee('Sanggar Kota')
            ->assertDontSee('Rahasia Pemilik')
            ->assertDontSee('081299990000');

        Livewire::test(DataKesenian::class)
            ->set('kecamatan', (string) $kalisat->id)
            ->assertSee('Sanggar Desa')->assertDontSee('Sanggar Kota')
            ->set('desa', (string) $gambiran->id)
            ->set('kecamatan', '')
            ->assertSet('desa', '');
    }

    public function test_daftar_dan_detail_opk_serta_cagar_budaya(): void
    {
        $opk = Opk::factory()->create(['nama' => 'Wayang Orang', 'jenis' => 'Seni', 'deskripsi' => '<p>Tari <b>klasik</b></p>']);
        $cb = CagarBudaya::factory()->create(['nama' => 'Situs Duplang', 'status' => 'CB']);

        $this->get('/publik/kebudayaan/opk')->assertOk()->assertSee('Wayang Orang');
        $this->get(route('publik.detail-opk', $opk))->assertOk()->assertSee('<b>klasik</b>', false)->assertSee('Kecamatan Wuluhan');
        $this->get('/publik/kebudayaan/cagar-budaya')->assertOk()->assertSee('Situs Duplang');
        $this->get(route('publik.detail-cagar-budaya', $cb))->assertOk()->assertSee('Cagar Budaya')->assertSee('peta-kecil', false);
        $this->get('/publik/kebudayaan/opk/999')->assertNotFound();
    }
}
