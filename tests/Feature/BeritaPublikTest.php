<?php

namespace Tests\Feature;

use App\Livewire\Publik\Berita as BeritaPublik;
use App\Models\Berita;
use App\Models\KategoriBerita;
use App\Models\User;
use Database\Seeders\KategoriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BeritaPublikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KategoriSeeder::class);
    }

    private function berita(string $judul, array $atribut = []): Berita
    {
        return Berita::create(array_merge([
            'kategori_berita_id' => KategoriBerita::first()->id,
            'judul' => $judul,
            'slug' => str($judul)->slug(),
            'isi' => "Isi berita {$judul}.",
            'status' => 'terbit',
            'tanggal_terbit' => now()->subDay(),
        ], $atribut));
    }

    public function test_list_hanya_berita_terbit_terbaru_dulu(): void
    {
        $this->berita('Berita Lama', ['tanggal_terbit' => now()->subDays(10)]);
        $this->berita('Berita Baru', ['tanggal_terbit' => now()->subDay()]);
        $this->berita('Masih Draf', ['status' => 'draf']);
        $this->berita('Terjadwal Besok', ['tanggal_terbit' => now()->addDay()]);
        $this->berita('Sudah Dihapus')->delete();

        $this->get('/publik/berita')
            ->assertOk()
            ->assertSeeInOrder(['Berita Baru', 'Berita Lama'])
            ->assertDontSee('Masih Draf')
            ->assertDontSee('Terjadwal Besok')
            ->assertDontSee('Sudah Dihapus');
    }

    public function test_cuplikan_150_karakter_tanpa_tag_html(): void
    {
        $this->berita('Panjang', ['isi' => '<b>Tebal</b> '.str_repeat('kata ', 60)]);

        $cuplikan = Berita::first()->ringkasan_tampil;
        $this->assertStringNotContainsString('<b>', $cuplikan);
        $this->assertStringStartsWith('Tebal kata', $cuplikan);
        $this->assertLessThanOrEqual(153, mb_strlen($cuplikan));

        $this->get('/publik/berita')->assertSee('Tebal kata kata');
    }

    public function test_pencarian_berdasarkan_judul_dan_pagination(): void
    {
        $this->berita('Festival Hyang Argopuro');
        foreach (range(1, 10) as $i) {
            $this->berita("Berita nomor {$i}", ['tanggal_terbit' => now()->subDays($i + 1)]);
        }

        Livewire::test(BeritaPublik::class)
            ->assertViewHas('daftar', fn ($d) => $d->total() === 11 && $d->count() === 9 && $d->lastPage() === 2)
            ->set('cari', 'argopuro')
            ->assertSee('Festival Hyang Argopuro')
            ->assertDontSee('Berita nomor 1')
            ->set('cari', '100%')
            ->assertSee('Tidak ada berita dengan judul');
    }

    public function test_detail_menampilkan_penulis_isi_dan_berita_lainnya(): void
    {
        $penulis = User::factory()->create(['name' => 'Rina Admin']);
        $utama = $this->berita('Berita Utama', ['penulis_id' => $penulis->id, 'isi' => "Paragraf satu <script>x</script>.\n\nParagraf dua."]);
        foreach (range(1, 5) as $i) {
            $this->berita("Lainnya {$i}", ['tanggal_terbit' => now()->subDays($i + 1)]);
        }

        $this->get('/publik/berita/berita-utama')
            ->assertOk()
            ->assertSee('<title>Berita Utama · Disporabudpar Jember</title>', false)
            ->assertSee('oleh Rina Admin')
            ->assertSee('Paragraf dua.')
            ->assertSee('&lt;script&gt;', false) // isi di-escape
            ->assertSee('Berita Lainnya')
            ->assertSeeInOrder(['Lainnya 1', 'Lainnya 2', 'Lainnya 3', 'Lainnya 4'])
            ->assertDontSee('Lainnya 5'); // maksimal 4

        $this->assertSame(1, $utama->fresh()->jumlah_dibaca);
    }

    public function test_detail_404_untuk_slug_tidak_ada_draf_dan_yang_dihapus(): void
    {
        $this->berita('Draf Rahasia', ['status' => 'draf']);
        $this->berita('Dihapus')->delete();

        $this->get('/publik/berita/tidak-ada')->assertNotFound();
        $this->get('/publik/berita/draf-rahasia')->assertNotFound();
        $this->get('/publik/berita/dihapus')->assertNotFound();
    }
}
