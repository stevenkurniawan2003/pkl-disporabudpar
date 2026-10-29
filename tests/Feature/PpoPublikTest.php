<?php

namespace Tests\Feature;

use App\Livewire\Publik\Beranda;
use App\Livewire\Publik\PpoAtlet;
use App\Livewire\Publik\SorotanPrestasi;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\GaleriPrestasi;
use App\Models\Pelatih;
use App\Models\PrestasiAtlet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PpoPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_menampilkan_total_atlet_pelatih_dan_cabor(): void
    {
        Atlet::factory()->count(3)->create();
        Pelatih::factory()->create();
        Cabor::factory()->count(2)->create();

        $this->get('/publik/beranda')->assertOk()->assertSee('Total Atlet')->assertSee('Total Cabang Olahraga (Cabor)');

        Livewire::test(Beranda::class)->assertViewHas('olahraga', ['atlet' => 3, 'pelatih' => 1, 'cabor' => 2]);
    }

    public function test_menu_ppo_dan_database_atlet_tanpa_data_pribadi(): void
    {
        Atlet::factory()->cabor('renang')->create(['nama_lengkap' => 'Sari Renang', 'no_hp' => '081299990000', 'alamat' => 'Jl. Rahasia 9']);
        Atlet::factory()->cabor('catur')->create(['nama_lengkap' => 'Bima Catur']);

        $this->get('/publik/ppo/atlet')
            ->assertOk()
            ->assertSee('PPO ▾')
            ->assertSee('id="grafik-atlet"', false)
            ->assertSee('Sari Renang')
            ->assertDontSee('081299990000')
            ->assertDontSee('Jl. Rahasia 9');

        Livewire::test(PpoAtlet::class)->set('cabor', 'catur')->assertSee('Bima Catur')->assertDontSee('Sari Renang');
    }

    public function test_database_pelatih_dan_cabor_bisa_dibuka(): void
    {
        Pelatih::factory()->create(['nama_lengkap' => 'Pak Joko', 'punya_lisensi' => true]);
        Cabor::factory()->create(['kode_cabor' => 'atletik', 'ketua_pengkab' => 'H. Ahmad']);

        $this->get('/publik/ppo/pelatih')->assertOk()->assertSee('Pak Joko')->assertSee('Punya');
        $this->get('/publik/ppo/cabor')->assertOk()->assertSee('Atletik')->assertSee('PASI')->assertSee('H. Ahmad');
        $this->get('/publik/ppo/cabor?q=renang')->assertOk()->assertDontSee('H. Ahmad');
    }

    public function test_sorotan_prestasi_galeri_dan_filter_tahun(): void
    {
        PrestasiAtlet::factory()->create(['nama_atlet' => 'Juara Lama', 'tahun' => 2023]);
        PrestasiAtlet::factory()->create(['nama_atlet' => 'Juara Baru', 'tahun' => 2025, 'jenis_medali' => 'Perunggu']);
        GaleriPrestasi::create(['judul' => 'Foto podium', 'jenis' => 'foto', 'file' => 'prestasi/foto/a.jpg']);
        GaleriPrestasi::create(['judul' => 'Video final', 'jenis' => 'video', 'file' => 'prestasi/video/b.mp4']);

        $this->get('/publik/ppo/sorotan-prestasi')
            ->assertOk()
            ->assertSee('Foto podium')
            ->assertSee('<video', false)
            ->assertSee('id="grafik-prestasi"', false)
            ->assertSee('Juara Lama');

        Livewire::test(SorotanPrestasi::class)->set('tahun', '2025')->assertSee('Juara Baru')->assertDontSee('Juara Lama');
    }
}
