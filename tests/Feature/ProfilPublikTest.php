<?php

namespace Tests\Feature;

use App\Livewire\Publik\Profil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProfilPublikTest extends TestCase
{
    use RefreshDatabase;

    private string $bagan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bagan = public_path('images/struktur-organisasi.png');
    }

    public function test_keempat_halaman_punya_banner_breadcrumb_dan_tab_yang_sama(): void
    {
        foreach (Profil::HALAMAN as $slug => [$judul]) {
            $this->get('/publik/profil/'.$slug)
                ->assertOk()
                ->assertSee("<title>{$judul} · Disporabudpar Jember</title>", false)
                ->assertSee('class="profil-banner"', false)
                ->assertSeeInOrder(['Beranda', 'Profil', $judul])
                ->assertSee('<span aria-current="page">'.$judul.'</span>', false)
                ->assertSeeInOrder(['Tentang Kami', 'Visi Misi', 'Struktur Organisasi', 'Tugas dan Fungsi'])
                ->assertSee('class="chip on" href="'.route('publik.profil.'.$slug).'"', false)
                ->assertSee('class="nb on" aria-haspopup="true">Profil', false);
        }
    }

    public function test_isi_berupa_placeholder_yang_jelas(): void
    {
        $this->get('/publik/profil/tentang-kami')->assertSee('[ISI SEJARAH SINGKAT DINAS DI SINI]');
        $this->get('/publik/profil/visi-misi')->assertSee('[ISI VISI DINAS DI SINI]')->assertSee('[ISI MISI KE-1 DI SINI]');
        $this->get('/publik/profil/tugas-dan-fungsi')->assertSee('[ISI TUGAS POKOK DINAS DI SINI]')->assertSee('[ISI FUNGSI KE-1 DI SINI]');
    }

    public function test_struktur_organisasi_tanpa_gambar_menampilkan_petunjuk(): void
    {
        if (is_file($this->bagan)) {
            $this->markTestSkipped('Gambar bagan asli sudah ada di public/images.');
        }

        $this->get('/publik/profil/struktur-organisasi')
            ->assertOk()
            ->assertSee('Bagan struktur organisasi belum tersedia')
            ->assertSee('public/images/struktur-organisasi.png')
            ->assertDontSee('id="lightbox-bagan"', false);
    }

    public function test_struktur_organisasi_dengan_gambar_bisa_diperbesar(): void
    {
        $sudahAda = is_file($this->bagan);
        if (! $sudahAda) {
            File::ensureDirectoryExists(dirname($this->bagan));
            File::put($this->bagan, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
        }

        try {
            $this->get('/publik/profil/struktur-organisasi')
                ->assertOk()
                ->assertSee('images/struktur-organisasi.png', false)
                ->assertSee('Klik untuk memperbesar')
                ->assertSee('id="lightbox-bagan"', false);
        } finally {
            if (! $sudahAda) {
                File::delete($this->bagan);
                @rmdir(dirname($this->bagan));
            }
        }
    }

    public function test_halaman_profil_yang_tidak_ada_404(): void
    {
        $this->get('/publik/profil/sejarah')->assertNotFound();
    }
}
