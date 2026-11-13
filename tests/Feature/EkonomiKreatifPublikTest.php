<?php

namespace Tests\Feature;

use App\Livewire\Publik\EkonomiKreatif;
use App\Livewire\Publik\PencarianEkraf;
use App\Models\Desa;
use App\Models\Ekraf;
use App\Models\Kecamatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EkonomiKreatifPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_ekraf_jember_menghitung_aktif_nonaktif_dan_grafik(): void
    {
        Ekraf::factory()->subsektor('1.9')->count(2)->create();
        Ekraf::factory()->subsektor('1.8')->nonaktif()->create();

        $this->get('/publik/ekonomi-kreatif')
            ->assertOk()
            ->assertSee('<title>Data EKRAF Jember · Disporabudpar Jember</title>', false)
            ->assertSee('Lihat Rincian')
            ->assertSee('id="grafik-ekraf-subsektor"', false)
            ->assertSee('id="grafik-ekraf-kecamatan"', false);

        $komponen = Livewire::test(EkonomiKreatif::class)
            ->assertViewHas('jumlahAktif', 2)
            ->assertViewHas('jumlahNonaktif', 1);

        $subsektor = collect($komponen->viewData('daftarGrafik'))->firstWhere('id', 'grafik-ekraf-subsektor');
        $this->assertCount(17, $subsektor['label']);
        $this->assertSame('1.9 Kuliner', $subsektor['label'][8]);
        $this->assertSame(2, $subsektor['seri'][0]['data'][8]);
    }

    public function test_menu_ekonomi_kreatif_tidak_menautkan_panel_admin(): void
    {
        $this->get('/publik/ekonomi-kreatif/pencarian')
            ->assertOk()
            ->assertSee('Ekonomi Kreatif ▾')
            ->assertSee('class="nb on" href="'.route('publik.pencarian-ekraf').'"', false)
            ->assertDontSee('Sistem EKRAF');
    }

    public function test_pencarian_filter_kecamatan_desa_subsektor_tanpa_data_pribadi(): void
    {
        $patrang = Kecamatan::create(['nama' => 'Patrang']);
        $slawu = Desa::create(['kecamatan_id' => $patrang->id, 'nama' => 'Slawu']);
        Ekraf::factory()->create(['nama_usaha' => 'Tas Anyaman', 'no_telepon' => '081299990000', 'omzet' => 7654321]);
        Ekraf::factory()->subsektor('1.17')->create(['nama_usaha' => 'Mural Patrang', 'kecamatan_id' => $patrang->id, 'desa_id' => $slawu->id]);

        $this->get('/publik/ekonomi-kreatif/pencarian')
            ->assertOk()
            ->assertSee('Tas Anyaman')
            ->assertDontSee('081299990000')
            ->assertDontSee('7.654.321');

        Livewire::test(PencarianEkraf::class)
            ->set('kecamatan', (string) $patrang->id)->assertSee('Mural Patrang')->assertDontSee('Tas Anyaman')
            ->set('desa', (string) $slawu->id)->assertSee('Mural Patrang')
            ->set('kecamatan', '')->assertSet('desa', '')
            ->set('subsektor', '1.8')->assertSee('Tas Anyaman')->assertDontSee('Mural Patrang')
            ->call('resetFilter')->assertSet('subsektor', '')->assertSee('Mural Patrang');
    }
}
