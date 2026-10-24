<?php

namespace Tests\Feature;

use App\Livewire\Publik\Kebudayaan;
use App\Models\Kesenian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KebudayaanPublikTest extends TestCase
{
    use RefreshDatabase;

    private function kesenian(string $nama, string $kategori = 'Tari', ?string $asal = 'Kec. Kalisat', ?string $deskripsi = null): Kesenian
    {
        return Kesenian::create([
            'nama_kesenian' => $nama,
            'kategori' => $kategori,
            'asal_daerah' => $asal,
            'deskripsi' => $deskripsi ?? "Deskripsi {$nama}.",
        ]);
    }

    public function test_daftar_kartu_dengan_badge_asal_cuplikan_dan_ikon(): void
    {
        $this->kesenian('Tari Lahbako', 'Tari', 'Kec. Kalisat', '<b>Tari</b> '.str_repeat('petani tembakau ', 20));
        $this->kesenian('Patrol', 'Musik', null);

        $this->get('/publik/kebudayaan')
            ->assertOk()
            ->assertSee('<title>Kebudayaan · Disporabudpar Jember</title>', false)
            ->assertSee('Tari Lahbako')
            ->assertSee('Kec. Kalisat')
            ->assertSee('Tari petani tembakau')
            ->assertDontSee('<b>Tari</b>', false)
            ->assertSee('💃')
            ->assertSee('🥁')
            ->assertSee('class="nb on" href="'.route('publik.kebudayaan').'"', false);
    }

    public function test_filter_kategori_hanya_yang_punya_data_dan_pencarian(): void
    {
        $this->kesenian('Tari Lahbako', 'Tari', 'Kec. Kalisat');
        $this->kesenian('Patrol Jember', 'Musik', 'Kec. Sumbersari');
        $this->kesenian('Petik Laut', 'Upacara adat', 'Kec. Puger');

        Livewire::test(Kebudayaan::class)
            ->assertViewHas('daftarKategori', ['Tari', 'Musik', 'Upacara adat'])
            ->call('pilihKategori', 'Upacara adat')
            ->assertSee('Petik Laut')
            ->assertDontSee('Tari Lahbako')
            ->call('pilihKategori', 'Semua')
            ->set('cari', 'puger') // cari juga lewat asal daerah
            ->assertSee('Petik Laut')
            ->assertDontSee('Patrol Jember')
            ->set('cari', 'patrol')
            ->assertSee('Patrol Jember')
            ->set('cari', '50%')
            ->assertSee('Tidak ada kesenian yang cocok');
    }

    public function test_pagination_12_per_halaman(): void
    {
        foreach (range(1, 13) as $i) {
            $this->kesenian(sprintf('Kesenian %02d', $i));
        }

        Livewire::test(Kebudayaan::class)
            ->assertViewHas('daftar', fn ($d) => $d->count() === 12 && $d->lastPage() === 2)
            ->call('nextPage')
            ->assertSee('Kesenian 13');
    }

    public function test_detail_deskripsi_lengkap_dan_kesenian_serupa(): void
    {
        $tari = $this->kesenian('Tari Lahbako', 'Tari', 'Kec. Kalisat', "Paragraf satu <script>x</script>.\n\nParagraf dua.");
        $this->kesenian('Tari Kecak Jember', 'Tari');
        $this->kesenian('Patrol', 'Musik');

        $this->get('/publik/kebudayaan/'.$tari->id_kesenian)
            ->assertOk()
            ->assertSee('<title>Tari Lahbako · Disporabudpar Jember</title>', false)
            ->assertSee('Paragraf dua.')
            ->assertSee('&lt;script&gt;', false)
            ->assertSee('Kesenian tari lainnya')
            ->assertSee('Tari Kecak Jember')
            ->assertDontSee('Patrol')
            ->assertSee('class="nb on" href="'.route('publik.kebudayaan').'"', false);
    }

    public function test_detail_404(): void
    {
        $this->get('/publik/kebudayaan/999')->assertNotFound();
        $this->get('/publik/kebudayaan/bukan-angka')->assertNotFound();
    }

    public function test_tanpa_data(): void
    {
        $this->get('/publik/kebudayaan')->assertOk()->assertSee('Belum ada data kesenian');
    }
}
