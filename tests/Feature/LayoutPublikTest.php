<?php

namespace Tests\Feature;

use App\Http\Middleware\CatatKunjunganSitus;
use App\Models\KunjunganSitus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LayoutPublikTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0 Safari/537.36';

    public function test_navbar_berisi_semua_menu_dan_penanda_aktif(): void
    {
        $this->withHeader('User-Agent', self::BROWSER)
            ->get('/publik/kebudayaan')
            ->assertOk()
            ->assertSeeInOrder(['Beranda', 'Destinasi', 'Peta Wisata', 'Data Kunjungan Wisata', 'Data Kunjungan Hotel',
                'Berita', 'Kebudayaan', 'Ekonomi Kreatif', 'Profil', 'Tentang Kami', 'Visi Misi', 'Struktur Organisasi', 'Tugas dan Fungsi'])
            ->assertSee('class="nb on" href="'.route('publik.kebudayaan').'"', false)
            ->assertDontSee('Hubungi Kami</a>', false);
    }

    public function test_menu_dropdown_aktif_saat_anaknya_dibuka(): void
    {
        $this->get('/publik/profil/visi-misi')
            ->assertOk()
            ->assertSee('<title>Visi Misi · Disporabudpar Jember</title>', false)
            ->assertSee('class="nb on" aria-haspopup="true">Profil', false);
    }

    public function test_semua_halaman_menu_bisa_dibuka_tanpa_login(): void
    {
        foreach (['beranda', 'peta-wisata', 'kunjungan-wisata', 'kunjungan-hotel', 'berita', 'kebudayaan', 'ekonomi-kreatif',
            'profil/tentang-kami', 'profil/visi-misi', 'profil/struktur-organisasi', 'profil/tugas-dan-fungsi'] as $url) {
            $this->get('/publik/'.$url)->assertOk();
        }
    }

    public function test_footer_empat_kolom_dari_config(): void
    {
        config([
            'site.media_sosial' => [
                ['nama' => 'Instagram', 'ikon' => 'instagram', 'url' => 'https://instagram.com/disporabudpar'],
                ['nama' => 'TikTok', 'ikon' => 'tiktok', 'url' => ''],
            ],
            'site.kontak.email' => 'disporabudpar@jemberkab.go.id',
        ]);

        $this->get('/publik/beranda')
            ->assertOk()
            ->assertSee('Tautan Cepat')
            ->assertSee('PPID Kabupaten Jember')
            ->assertSee('href="'.config('site.tautan_cepat.0.url').'" target="_blank"', false)
            ->assertSee('Pengunjung')
            ->assertSee('Hubungi Kami')
            ->assertSee('<iframe', false)
            ->assertSee('mailto:disporabudpar@jemberkab.go.id', false)
            ->assertSee('bi-instagram', false)
            ->assertDontSee('bi-tiktok', false); // URL kosong tidak ditampilkan
    }

    public function test_satu_sesi_dihitung_sekali_per_hari(): void
    {
        $this->withHeader('User-Agent', self::BROWSER);

        $this->get('/publik/beranda')->assertOk();
        $this->get('/publik/beranda')->assertOk();       // refresh
        $this->get('/publik/berita')->assertOk();        // pindah halaman

        $this->assertSame(1, KunjunganSitus::count());
        $this->assertSame(now()->toDateString(), session(CatatKunjunganSitus::KUNCI_SESI));

        // Besok, sesi yang sama dihitung lagi.
        $this->travel(1)->days();
        $this->get('/publik/beranda')->assertOk();
        $this->assertSame(2, KunjunganSitus::count());
    }

    public function test_sesi_berbeda_dihitung_terpisah(): void
    {
        $this->withHeader('User-Agent', self::BROWSER)->get('/publik/beranda');

        $this->flushSession();
        $this->app['session']->driver()->regenerate(true);

        $this->withHeader('User-Agent', self::BROWSER)->get('/publik/beranda');

        $this->assertSame(2, KunjunganSitus::count());
    }

    public function test_bot_admin_login_dan_halaman_admin_tidak_dihitung(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get('/publik/beranda')->assertOk();
        $this->withHeader('User-Agent', '')->get('/publik/beranda')->assertOk();
        $this->assertSame(0, KunjunganSitus::count());

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->withHeader('User-Agent', self::BROWSER)->get('/publik/beranda')->assertOk();
        $this->assertSame(0, KunjunganSitus::count());

        auth()->logout();
        $this->flushSession();
        $this->withHeader('User-Agent', self::BROWSER)->get('/admin/login')->assertOk();
        $this->assertSame(0, KunjunganSitus::count());
    }

    public function test_statistik_hari_ini_bulan_ini_total_dan_cache(): void
    {
        $buat = fn (string $sesi, string $tanggal) => KunjunganSitus::create(['session_id' => $sesi, 'tanggal' => $tanggal]);
        $buat('a', now()->toDateString());
        $buat('b', now()->toDateString());
        $buat('c', now()->startOfMonth()->toDateString());
        $buat('d', now()->subMonthNoOverflow()->startOfMonth()->toDateString());

        Cache::forget(KunjunganSitus::KUNCI_CACHE);
        $statistik = KunjunganSitus::statistik();

        $hariIniAwalBulan = now()->isSameDay(now()->startOfMonth());
        $this->assertSame($hariIniAwalBulan ? 3 : 2, $statistik['hari_ini']);
        $this->assertSame(3, $statistik['bulan_ini']);
        $this->assertSame(4, $statistik['total']);

        // Selama cache berlaku, data baru belum terhitung (tidak query di setiap request).
        $buat('e', now()->toDateString());
        $this->assertSame(4, KunjunganSitus::statistik()['total']);

        Cache::forget(KunjunganSitus::KUNCI_CACHE);
        $this->assertSame(5, KunjunganSitus::statistik()['total']);
    }
}
