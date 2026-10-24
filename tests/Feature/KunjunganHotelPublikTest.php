<?php

namespace Tests\Feature;

use App\Livewire\Publik\KunjunganHotel as Halaman;
use App\Models\Hotel;
use App\Models\KunjunganHotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class KunjunganHotelPublikTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $bintang;

    private Hotel $argopuro;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bintang = $this->hotel('Hotel Bintang');
        $this->argopuro = $this->hotel('Hotel Argopuro');
    }

    private function hotel(string $nama): Hotel
    {
        return Hotel::create([
            'nama_hotel' => $nama, 'gambar_utama' => 'hotel/x.jpg', 'deskripsi_singkat' => 'x', 'narasi' => 'x',
            'alamat' => 'x', 'latitude' => -8.17, 'longitude' => 113.7,
        ]);
    }

    private function kunjungan(Hotel $h, int $tahun, int $bulan, int $dom, int $man): void
    {
        KunjunganHotel::create(['hotel_id' => $h->id_hotel, 'tahun' => $tahun, 'bulan' => $bulan, 'domestik' => $dom, 'mancanegara' => $man]);
    }

    public function test_halaman_bisa_dibuka_tanpa_login_dengan_grafik_dan_menu_aktif(): void
    {
        $this->kunjungan($this->bintang, 2025, 8, 2140, 36);

        $this->get('/publik/kunjungan-hotel')
            ->assertOk()
            ->assertSee('<title>Data Kunjungan Hotel · Disporabudpar Jember</title>', false)
            ->assertSee('Data kunjungan hotel')
            ->assertSee('Semua hotel')
            ->assertSee('Tamu domestik')
            ->assertSee('Rekap per hotel, 2025')
            ->assertSee('plugins: [ChartDataLabels]', false)
            ->assertSee('2.176')
            ->assertSee('class="nb on" aria-haspopup="true">Destinasi', false);
    }

    public function test_default_tahun_terbaru_ringkasan_grafik_dan_rekap(): void
    {
        $this->kunjungan($this->bintang, 2025, 8, 2140, 36);
        $this->kunjungan($this->bintang, 2025, 9, 1985, 41);
        $this->kunjungan($this->argopuro, 2025, 8, 5000, 12);
        $this->kunjungan($this->argopuro, 2024, 1, 9999, 9999);

        DB::enableQueryLog();

        Livewire::test(Halaman::class)
            ->assertSet('tahun', 2025)
            ->assertViewHas('totalDomestik', 9125)
            ->assertViewHas('totalMancanegara', 89)
            ->assertViewHas('grafik', fn ($g) => $g['domestik'][7] === 7140 && $g['domestik'][8] === 1985 && $g['domestik'][0] === 0)
            ->assertViewHas('rekap', fn ($r) => $r->pluck('nama')->all() === ['Hotel Argopuro', 'Hotel Bintang']
                && $r[1]->total === 4202
                && $r[1]->url === route('publik.detail-hotel', $this->bintang))
            ->assertSee('9.214');

        $sql = strtolower(collect(DB::getQueryLog())->pluck('query')->implode("\n"));
        $this->assertStringContainsString('group by `bulan`', $sql);
        $this->assertStringContainsString('group by `hotel`.`id_hotel`', $sql);
    }

    public function test_filter_hotel_dan_tahun(): void
    {
        $this->kunjungan($this->bintang, 2025, 2, 300, 3);
        $this->kunjungan($this->argopuro, 2025, 2, 700, 7);
        $this->kunjungan($this->bintang, 2024, 2, 50, 5);

        Livewire::test(Halaman::class)
            ->set('pilihan', $this->bintang->id_hotel)
            ->assertDispatched('grafik-kunjungan', fn ($n, $p) => $p['data']['domestik'][1] === 300)
            ->assertViewHas('rekap', fn ($r) => $r->count() === 1)
            ->set('tahun', 2024)
            ->assertViewHas('totalDomestik', 50)
            ->set('pilihan', 999999)
            ->assertSet('pilihan', 0);

        Livewire::withQueryParams(['hotel' => $this->argopuro->id_hotel, 'tahun' => 2025])
            ->test(Halaman::class)
            ->assertSet('pilihan', $this->argopuro->id_hotel)
            ->assertViewHas('totalDomestik', 700);
    }

    public function test_tanpa_data_tetap_tampil(): void
    {
        $this->get('/publik/kunjungan-hotel')->assertOk()->assertSee('Belum ada data kunjungan');
    }
}
