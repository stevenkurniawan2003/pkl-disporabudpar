<?php

namespace Tests\Feature;

use App\Livewire\Publik\EkonomiKreatif;
use App\Models\Ekraf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EkonomiKreatifPublikTest extends TestCase
{
    use RefreshDatabase;

    private function ekraf(string $usaha, string $subsektor = 'Kuliner', array $atribut = []): Ekraf
    {
        return Ekraf::create(array_merge([
            'nama_usaha' => $usaha,
            'nama_pemilik' => "Pemilik {$usaha}",
            'subsektor' => $subsektor,
            'alamat' => 'Kec. Kaliwates',
            'deskripsi' => "Deskripsi {$usaha}.",
        ], $atribut));
    }

    public function test_kartu_menampilkan_data_dan_menyembunyikan_kontak_kosong(): void
    {
        $this->ekraf('Cokelat Raddina', 'Kuliner', ['no_telepon' => '0812 3456 789', 'media_sosial' => 'https://instagram.com/raddina']);
        $this->ekraf('Pengrajin Kentongan', 'Kriya', ['alamat' => 'Kec. Ambulu']);

        $this->get('/publik/ekonomi-kreatif')
            ->assertOk()
            ->assertSee('<title>Ekonomi Kreatif · Disporabudpar Jember</title>', false)
            ->assertSee('Cokelat Raddina')
            ->assertSee('Pemilik Cokelat Raddina')
            ->assertSee('tel:08123456789', false)
            ->assertSee('href="https://instagram.com/raddina"', false)
            ->assertSee('bi-instagram', false)
            ->assertSee('Kec. Ambulu')
            ->assertSee('🍜')
            ->assertSee('🏺')
            ->assertSee('class="nb on" href="'.route('publik.ekonomi-kreatif').'"', false);

        // Usaha tanpa telepon & medsos tidak punya baris aksi.
        $this->assertSame(1, substr_count(
            $this->get('/publik/ekonomi-kreatif')->getContent(),
            'class="aksi-ekraf"'
        ));
    }

    public function test_filter_subsektor_dan_pencarian(): void
    {
        $this->ekraf('Cokelat Raddina', 'Kuliner');
        $this->ekraf('Pengrajin Kentongan', 'Kriya');
        $this->ekraf('Studio Foto Heritage', 'Fotografi', ['alamat' => 'Jl. Sultan Agung']);

        Livewire::test(EkonomiKreatif::class)
            ->assertViewHas('daftarSubsektor', ['Kuliner', 'Kriya', 'Fotografi'])
            ->call('pilihSubsektor', 'Kriya')
            ->assertSee('Pengrajin Kentongan')
            ->assertDontSee('Cokelat Raddina')
            ->call('pilihSubsektor', 'Semua')
            ->set('cari', 'cokelat')
            ->assertSee('Cokelat Raddina')
            ->assertDontSee('Pengrajin Kentongan')
            ->set('cari', 'sultan') // cari juga lewat alamat
            ->assertSee('Studio Foto Heritage')
            ->set('cari', 'tidak-ada')
            ->assertSee('Tidak ada usaha yang cocok');
    }

    public function test_pagination_12_per_halaman(): void
    {
        foreach (range(1, 13) as $i) {
            $this->ekraf(sprintf('Usaha %02d', $i));
        }

        Livewire::test(EkonomiKreatif::class)
            ->assertViewHas('daftar', fn ($d) => $d->count() === 12 && $d->lastPage() === 2)
            ->call('nextPage')
            ->assertSee('Usaha 13');
    }

    public function test_detail_deskripsi_lengkap_kontak_dan_usaha_serupa(): void
    {
        $e = $this->ekraf('Cokelat Raddina', 'Kuliner', [
            'deskripsi' => "Cokelat khas Jember <b>asli</b>.\n\nTersedia oleh-oleh.",
            'media_sosial' => 'https://facebook.com/raddina',
        ]);
        $this->ekraf('Keripik Presto', 'Kuliner');
        $this->ekraf('Batik Tembakau', 'Fesyen');

        $this->get('/publik/ekonomi-kreatif/'.$e->id_ekraf)
            ->assertOk()
            ->assertSee('<title>Cokelat Raddina · Disporabudpar Jember</title>', false)
            ->assertSee('Pemilik: Pemilik Cokelat Raddina')
            ->assertSee('Tersedia oleh-oleh.')
            ->assertSee('&lt;b&gt;asli&lt;/b&gt;', false)
            ->assertSee('Kunjungi Facebook')
            ->assertSee('Usaha kuliner lainnya')
            ->assertSee('Keripik Presto')
            ->assertDontSee('Batik Tembakau')
            ->assertSee('class="nb on" href="'.route('publik.ekonomi-kreatif').'"', false);
    }

    public function test_platform_sosmed_dikenali_dari_link(): void
    {
        $this->assertNull($this->ekraf('A')->platform_sosmed);
        $this->assertSame('TikTok', $this->ekraf('B', 'Kuliner', ['media_sosial' => 'https://www.tiktok.com/@b'])->platform_sosmed['nama']);
        $this->assertSame(['nama' => 'Media sosial', 'ikon' => 'link-45deg'], $this->ekraf('C', 'Kuliner', ['media_sosial' => 'https://linktr.ee/c'])->platform_sosmed);
    }

    public function test_detail_404_dan_tanpa_data(): void
    {
        $this->get('/publik/ekonomi-kreatif/999')->assertNotFound();
        $this->get('/publik/ekonomi-kreatif')->assertOk()->assertSee('Belum ada data ekonomi kreatif');
    }
}
