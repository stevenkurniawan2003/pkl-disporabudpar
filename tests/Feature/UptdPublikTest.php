<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UptdPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_uptd_olahraga_berisi_sarana_sop_fasilitas_dan_kontak(): void
    {
        $this->get('/publik/uptd/olahraga')
            ->assertOk()
            ->assertSee('<title>UPTD Sarana dan Prasarana Olahraga · Disporabudpar Jember</title>', false)
            ->assertSee('Stadion Jember Sport Garden')
            ->assertSee('GOR Argopuro')
            ->assertSee(asset('images/uptd/stadion-jsg.jpg'), false)
            ->assertSee('Perda Nomor 1 Tahun 2024')
            ->assertSee('Keamanan 24 Jam')
            ->assertSee('0851 0000 9955')
            ->assertSee('class="nb on" href="'.route('publik.uptd.olahraga').'"', false);
    }

    public function test_uptd_wisata_terpadu_berisi_empat_destinasi_dan_info_kunjungan(): void
    {
        $this->get('/publik/uptd/wisata-terpadu')
            ->assertOk()
            ->assertSeeInOrder(['Wisata Rembangan', 'Pantai Watu Ulo', 'Pemandian &amp; Hotel Kebonagung', 'Pemandian Patemon'], false)
            ->assertSee('Dewasa Rp 15.000 · Anak Rp 8.000')
            ->assertSee('Parkir mobil')
            ->assertSee('wisata@jemberkab.go.id');
    }

    public function test_halaman_uptd_lain_404(): void
    {
        $this->get('/publik/uptd/kepemudaan')->assertNotFound();
    }
}
