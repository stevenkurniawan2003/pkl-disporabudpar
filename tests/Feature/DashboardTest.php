<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Models\Ekraf;
use App\Models\Hotel;
use App\Models\Kesenian;
use App\Models\KunjunganHotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function hotel(string $nama): Hotel
    {
        return Hotel::create([
            'nama_hotel' => $nama, 'gambar_utama' => 'hotel/x.jpg', 'deskripsi_singkat' => 'x', 'narasi' => 'x',
            'alamat' => 'x', 'latitude' => -8.1, 'longitude' => 113.7,
        ]);
    }

    public function test_halaman_dashboard_tampil_tanpa_destinasi_unggulan(): void
    {
        $admin = User::factory()->create(['aktif' => true]);

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Kunjungan hotel per bulan, '.now()->year)
            ->assertSee('chartjs-plugin-datalabels', false)
            ->assertDontSee('Destinasi unggulan')
            ->assertDontSee('Wisata unggulan');
    }

    public function test_kartu_statistik_menghitung_jumlah_data(): void
    {
        $this->hotel('A');
        $this->hotel('B');
        Kesenian::create(['nama_kesenian' => 'Tari', 'kategori' => 'Tari', 'deskripsi' => 'x']);
        Ekraf::create(['nama_usaha' => 'U', 'nama_pemilik' => 'P', 'subsektor' => 'Kuliner', 'alamat' => 'x', 'deskripsi' => 'x']);

        Livewire::test(Dashboard::class)
            ->assertViewHas('jumlahHotel', 2)
            ->assertViewHas('jumlahKesenian', 1)
            ->assertViewHas('jumlahEkraf', 1)
            ->assertViewHas('jumlahWisata', 0)
            ->assertViewHas('jumlahBerita', 0);
    }

    public function test_grafik_menjumlahkan_per_bulan_dan_bulan_kosong_bernilai_nol(): void
    {
        $a = $this->hotel('A');
        $b = $this->hotel('B');
        $tahun = now()->year - 1;

        // Januari dari dua hotel (dijumlahkan) dan Agustus; bulan lain tanpa data.
        foreach ([[$a, 1, 100, 5], [$b, 1, 50, 1], [$a, 8, 2140, 36]] as [$h, $bulan, $d, $m]) {
            KunjunganHotel::create(['hotel_id' => $h->id_hotel, 'tahun' => $tahun, 'bulan' => $bulan, 'domestik' => $d, 'mancanegara' => $m]);
        }
        // Data tahun lain tidak ikut terhitung.
        KunjunganHotel::create(['hotel_id' => $a->id_hotel, 'tahun' => $tahun - 1, 'bulan' => 1, 'domestik' => 999, 'mancanegara' => 999]);

        DB::enableQueryLog();

        Livewire::test(Dashboard::class)
            ->assertSet('tahun', now()->year)
            ->set('tahun', $tahun)
            ->assertDispatched('grafik-kunjungan-hotel', function (string $nama, array $params) {
                $d = $params['data'];

                return count($d['label']) === 12
                    && $d['label'][0] === 'Jan' && $d['label'][7] === 'Agu'
                    && $d['domestik'] === [150, 0, 0, 0, 0, 0, 0, 2140, 0, 0, 0, 0]
                    && $d['mancanegara'] === [6, 0, 0, 0, 0, 0, 0, 36, 0, 0, 0, 0];
            })
            ->assertSee('2.140')
            ->assertSee('2.332'); // total tamu tahun itu: 150 + 6 + 2140 + 36

        // Agregasi dilakukan di database, bukan dengan mengambil semua baris.
        $sql = collect(DB::getQueryLog())->pluck('query')->implode("\n");
        $this->assertStringContainsString('sum(domestik)', strtolower($sql));
        $this->assertStringContainsString('group by `bulan`', strtolower($sql));
    }

    public function test_tahun_tidak_masuk_akal_dari_url_kembali_ke_tahun_berjalan(): void
    {
        Livewire::withQueryParams(['tahun' => 1500])
            ->test(Dashboard::class)
            ->assertSet('tahun', now()->year);

        Livewire::test(Dashboard::class)
            ->set('tahun', now()->year + 5)
            ->assertSet('tahun', now()->year);
    }
}
