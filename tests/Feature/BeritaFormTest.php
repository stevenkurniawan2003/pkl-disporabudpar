<?php

namespace Tests\Feature;

use App\Livewire\Admin\Berita as BeritaPage;
use App\Livewire\Admin\BeritaForm;
use App\Models\Berita;
use App\Models\KategoriBerita;
use App\Models\User;
use Database\Seeders\KategoriSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BeritaFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(KategoriSeeder::class);
    }

    private function isiForm()
    {
        return Livewire::test(BeritaForm::class)
            ->call('buka')
            ->assertSet('tanggal', now()->format('Y-m-d'))
            ->set('judul', 'Berita Uji')
            ->set('kategori_berita_id', KategoriBerita::first()->id)
            ->set('isi', "Paragraf satu.\n\nParagraf dua.");
    }

    public function test_tulis_berita_lewat_modal(): void
    {
        $this->isiForm()
            ->set('gambarBaru', UploadedFile::fake()->image('sampul.jpg'))
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('terbuka', false)
            ->assertDispatched('berita-disimpan', pesan: 'Berita "Berita Uji" disimpan.');

        $berita = Berita::firstOrFail();
        Storage::disk('public')->assertExists($berita->gambar_sampul);
    }

    public function test_ubah_berita_ganti_sampul_menghapus_file_lama(): void
    {
        $this->isiForm()->set('gambarBaru', UploadedFile::fake()->image('lama.jpg'))->call('simpan');
        $berita = Berita::firstOrFail();
        $lama = $berita->gambar_sampul;

        Livewire::test(BeritaForm::class)
            ->call('buka', $berita->id)
            ->assertSet('judul', 'Berita Uji')
            ->set('gambarBaru', UploadedFile::fake()->image('baru.jpg'))
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('berita-disimpan');

        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($berita->fresh()->gambar_sampul);
    }

    public function test_detail_dan_hapus_dari_halaman_daftar(): void
    {
        $this->isiForm()->call('simpan');
        $berita = Berita::firstOrFail();

        Livewire::test(BeritaPage::class)
            ->call('lihatDetail', $berita->id)
            ->assertSee('Paragraf dua.')
            ->assertSee('Lihat di situs')
            ->call('hapus', $berita->id)
            ->assertSet('detailId', null)
            ->assertSee('Berita &quot;Berita Uji&quot; dihapus.', false);

        $this->assertSoftDeleted($berita);
    }

    public function test_halaman_berita_wajib_login_dan_tampil(): void
    {
        $this->get('/admin/berita')->assertRedirect(route('admin.login'));

        $admin = User::factory()->create(['aktif' => true]);
        $this->actingAs($admin)->get('/admin/berita')->assertOk()->assertSee('+ Tulis berita');
    }

    public function test_gambar_sampul_bukan_gambar_langsung_ditolak_tanpa_error(): void
    {
        Livewire::test(BeritaForm::class)
            ->call('buka')
            ->set('gambarBaru', UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'))
            ->assertHasErrors(['gambarBaru' => 'image'])
            ->assertOk();
    }

    public function test_gambar_sampul_valid_menampilkan_pratinjau(): void
    {
        Livewire::test(BeritaForm::class)
            ->call('buka')
            ->set('gambarBaru', UploadedFile::fake()->image('sampul.jpg'))
            ->assertHasNoErrors()
            ->assertSee('Pratinjau gambar baru');
    }

    public function test_isi_berita_html_dibersihkan_dan_teks_lama_jadi_paragraf(): void
    {
        $this->isiForm()
            ->set('isi', '<p><strong>Tebal</strong> dan <em>miring</em><script>alert(1)</script></p><p onclick="x()">Dua</p>')
            ->call('simpan')
            ->assertHasNoErrors();

        $isi = Berita::firstOrFail()->isi;
        $this->assertStringContainsString('<strong>Tebal</strong>', $isi);
        $this->assertStringContainsString('<em>miring</em>', $isi);
        $this->assertStringNotContainsString('<script>', $isi);
        $this->assertStringNotContainsString('onclick', $isi);

        // Teks lama (tanpa tag) dibuka di editor sebagai paragraf HTML.
        $lama = Berita::create([
            'kategori_berita_id' => KategoriBerita::first()->id, 'judul' => 'Lama', 'slug' => 'lama',
            'isi' => "Satu <tidak tag\n\nDua", 'status' => 'draf',
        ]);
        Livewire::test(BeritaForm::class)
            ->call('buka', $lama->id)
            ->assertSet('isi', "<p>Satu &lt;tidak tag</p>\n<p>Dua</p>");
    }

    public function test_isi_editor_kosong_ditolak(): void
    {
        $this->isiForm()->set('isi', '<p>&nbsp;</p>')->call('simpan')->assertHasErrors(['isi' => 'required']);
    }

    public function test_mini_galeri_tambah_dan_hapus_foto(): void
    {
        $this->isiForm()
            ->set('unggahGaleri', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
            ->assertCount('galeriBaru', 2)
            ->call('simpan')
            ->assertHasNoErrors();

        $berita = Berita::with('galeri')->firstOrFail();
        $this->assertCount(2, $berita->galeri);
        $dihapus = $berita->galeri->first()->gambar;
        Storage::disk('public')->assertExists($dihapus);

        Livewire::test(BeritaForm::class)
            ->call('buka', $berita->id)
            ->assertCount('galeri', 2)
            ->call('tandaiHapusGaleri', 0)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertCount(1, $berita->fresh()->galeri);
        Storage::disk('public')->assertMissing($dihapus);

        // Tampil di detail berita publik sebagai mini galeri.
        $berita->update(['status' => 'terbit', 'tanggal_terbit' => now()->subHour()]);
        $this->get(route('publik.detail-berita', $berita))->assertOk()->assertSee('Galeri foto')->assertSee('id="lightbox"', false);
    }

    public function test_mini_galeri_maksimal_10_foto(): void
    {
        $this->isiForm()
            ->set('unggahGaleri', array_map(fn ($i) => UploadedFile::fake()->image("f{$i}.jpg"), range(1, 11)))
            ->assertHasErrors('unggahGaleri')
            ->assertCount('galeriBaru', 0);
    }
}
