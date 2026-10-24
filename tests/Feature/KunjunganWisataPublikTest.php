<?php

namespace Tests\Feature;

use App\Livewire\Publik\KunjunganWisata as Halaman;
use App\Models\KategoriWisata;
use App\Models\Kecamatan;
use App\Models\KunjunganWisata;
use App\Models\Wisata;
use Database\Seeders\KategoriSeeder;
use Database\Seeders\KecamatanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class KunjunganWisataPublikTest extends TestCase
{
    use RefreshDatabase;

    private Wisata $papuma;

    private Wisata $rembangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([KategoriSeeder::class, KecamatanSeeder::class]);
        $this->papuma = $this->wisata('Pantai Papuma');
        $this->rembangan = $this->wisata('Wisata Rembangan');
    }

    private function wisata(string $nama, string $status = 'tayang'): Wisata
    {
        return Wisata::create([
            'kategori_id' => KategoriWisata::first()->id,
            'kecamatan_id' => Kecamatan::first()->id,
            'nama' => $nama,
            'slug' => str($nama)->slug(),
            'alamat' => 'x',
            'latitude' => -8.4,
            'longitude' => 113.5,
            'status' => $status,
        ]);
    }

    private function kunjungan(Wisata $w, int $tahun, int $bulan, int $dom, int $man): void
    {
        KunjunganWisata::create(['wisata_id' => $w->id, 'tahun' => $tahun, 'bulan' => $bulan, 'domestik' => $dom, 'mancanegara' => $man]);
    }

    public function test_halaman_bisa_dibuka_tanpa_login_dan_memuat_chart(): void
    {
        $this->kunjungan($this->papuma, 2025, 1, 12500, 30);

        $this->get('/publik/kunjungan-wisata')
            ->assertOk()
            ->assertSee('Data kunjungan wisata')
            ->assertSee('chart.umd.min.js', false)
            ->assertSee('chartjs-plugin-datalabels', false)
            ->assertSee('plugins: [ChartDataLabels]', false) // isi @script dari partial grafik ikut terkirim
            ->assertSee('12.500');
    }

    public function test_default_tahun_terbaru_yang_punya_data(): void
    {
        $this->kunjungan($this->papuma, 2023, 1, 1, 1);
        $this->kunjungan($this->papuma, 2025, 1, 1, 1);
        $this->kunjungan($this->papuma, 2024, 1, 1, 1);

        Livewire::test(Halaman::class)
            ->assertSet('tahun', 2025)
            ->assertViewHas('daftarTahun', fn ($t) => $t->all() === [2025, 2024, 2023]);

        // Tahun dari URL yang tidak punya data -> kembali ke default.
        Livewire::withQueryParams(['tahun' => 1999])->test(Halaman::class)->assertSet('tahun', 2025);
    }

    public function test_ringkasan_grafik_per_bulan_dan_rekap_per_destinasi(): void
    {
        $this->kunjungan($this->papuma, 2025, 1, 1000, 50);
        $this->kunjungan($this->papuma, 2025, 3, 2000, 20);
        $this->kunjungan($this->rembangan, 2025, 1, 5000, 0);
        $this->kunjungan($this->rembangan, 2024, 1, 9999, 9999); // tahun lain

        DB::enableQueryLog();

        Livewire::test(Halaman::class)
            ->assertSet('tahun', 2025)
            ->assertViewHas('totalDomestik', 8000)
            ->assertViewHas('totalMancanegara', 70)
            ->assertViewHas('grafik', fn ($g) => $g['domestik'] === [6000, 0, 2000, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                && $g['mancanegara'] === [50, 0, 20, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                && $g['label'][0] === 'Jan')
            ->assertViewHas('rekap', fn ($r) => $r->pluck('nama')->all() === ['Wisata Rembangan', 'Pantai Papuma']
                && (int) $r[1]->total === 3070)
            ->assertSee('8.070')
            ->assertSeeInOrder(['Wisata Rembangan', '5.000', 'Pantai Papuma', '3.070']);

        $sql = strtolower(collect(DB::getQueryLog())->pluck('query')->implode("\n"));
        $this->assertStringContainsString('group by `bulan`', $sql);
        $this->assertStringContainsString('group by `wisata`.`id`', $sql);
    }

    public function test_filter_destinasi_mengirim_data_grafik_baru(): void
    {
        $this->kunjungan($this->papuma, 2025, 2, 300, 3);
        $this->kunjungan($this->rembangan, 2025, 2, 700, 7);

        Livewire::test(Halaman::class)
            ->set('pilihan', $this->papuma->id)
            ->assertDispatched('grafik-kunjungan', fn ($n, $p) => $p['data']['domestik'][1] === 300)
            ->assertViewHas('totalDomestik', 300)
            ->assertViewHas('rekap', fn ($r) => $r->count() === 1)
            ->set('pilihan', 999999)
            ->assertSet('pilihan', 0)
            ->assertViewHas('totalDomestik', 1000);
    }

    public function test_wisata_draf_dan_terhapus_tidak_ikut_dihitung(): void
    {
        $draf = $this->wisata('Wisata Rahasia', 'draf');
        $hapus = $this->wisata('Sudah Ditutup');
        $this->kunjungan($this->papuma, 2025, 1, 100, 0);
        $this->kunjungan($draf, 2025, 1, 5000, 0);
        $this->kunjungan($hapus, 2025, 1, 7000, 0);
        $hapus->delete();

        Livewire::test(Halaman::class)
            ->assertViewHas('totalDomestik', 100)
            ->assertDontSee('Wisata Rahasia')
            ->assertDontSee('Sudah Ditutup');
    }

    public function test_tanpa_data_sama_sekali_tetap_tampil(): void
    {
        $this->get('/publik/kunjungan-wisata')
            ->assertOk()
            ->assertSee('Belum ada data kunjungan');
    }
}
