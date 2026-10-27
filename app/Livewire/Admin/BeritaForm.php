<?php

namespace App\Livewire\Admin;

use App\Models\Berita;
use App\Models\GaleriBerita;
use App\Models\KategoriBerita;
use App\Support\TeksKaya;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Modal tulis/ubah berita. Ditempel di halaman daftar berita (Admin\Berita)
 * dan dibuka lewat event "buka-form-berita".
 */
class BeritaForm extends Component
{
    use WithFileUploads;

    /** Batas foto mini galeri per berita (tidak termasuk gambar sampul). */
    public const MAKS_GALERI = 10;

    private const ATURAN_GAMBAR = ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

    public bool $terbuka = false;

    public ?int $beritaId = null;

    public string $judul = '';

    public ?int $kategori_berita_id = null;

    public string $tanggal = '';

    public string $status = 'terbit';

    public string $isi = '';

    public ?string $gambarLama = null;

    public $gambarBaru = null;

    /** Foto galeri yang sudah tersimpan: [['id', 'gambar', 'hapus'], ...]. */
    public array $galeri = [];

    /** Penampung input file (bisa pilih banyak). Isinya dipindah ke $galeriBaru setiap kali memilih. */
    public array $unggahGaleri = [];

    /** Foto galeri baru yang belum disimpan. */
    public array $galeriBaru = [];

    #[On('buka-form-berita')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $berita = Berita::with('galeri')->findOrFail($id);

            $this->beritaId = $berita->id;
            $this->judul = $berita->judul;
            $this->kategori_berita_id = $berita->kategori_berita_id;
            $this->tanggal = $berita->tanggal_terbit?->format('Y-m-d') ?? '';
            $this->status = $berita->status;
            $this->isi = TeksKaya::html($berita->isi);
            $this->gambarLama = $berita->gambar_sampul;
            $this->galeri = $berita->galeri->map(fn (GaleriBerita $g) => [
                'id' => $g->id_galeri_berita,
                'gambar' => $g->gambar,
                'hapus' => false,
            ])->all();
        } else {
            $this->tanggal = now()->format('Y-m-d');
        }

        $this->terbuka = true;
    }

    public function tutup(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset();
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'kategori_berita_id' => ['required', 'exists:kategori_berita,id'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['required', 'in:terbit,draf'],
            'isi' => ['required', 'string', 'max:100000'],
            'gambarBaru' => ['nullable', ...self::ATURAN_GAMBAR],
            'galeriBaru.*' => self::ATURAN_GAMBAR,
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file' => ':attribute maksimal 2 MB.',
            'date_format' => ':attribute tidak valid.',
            'exists' => ':attribute tidak valid.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'judul' => 'Judul',
            'kategori_berita_id' => 'Kategori',
            'tanggal' => 'Tanggal terbit',
            'status' => 'Status',
            'isi' => 'Isi berita',
            'gambarBaru' => 'Gambar sampul',
            'unggahGaleri.*' => 'Foto galeri',
            'galeriBaru.*' => 'Foto galeri',
        ];
    }

    public function simpan(): void
    {
        // Editor mengirim "<p></p>" kalau isinya dihapus semua: anggap kosong.
        if (TeksKaya::kosong($this->isi)) {
            $this->isi = '';
        }

        $data = $this->validate();

        if ($this->jumlahGaleri() > self::MAKS_GALERI) {
            $this->addError('unggahGaleri', 'Galeri maksimal '.self::MAKS_GALERI.' foto. Hapus sebagian foto dulu.');

            return;
        }

        $berita = $this->beritaId ? Berita::find($this->beritaId) : null;

        if ($this->beritaId && ! $berita) {
            $this->addError('simpan', 'Berita tidak ditemukan, mungkin sudah dihapus.');

            return;
        }

        // Tanggal terbit: pakai tanggal yang dipilih; kalau kosong dan status terbit, pakai sekarang.
        $tanggalTerbit = null;
        if ($data['tanggal'] !== null && $data['tanggal'] !== '') {
            $dipilih = Carbon::createFromFormat('Y-m-d', $data['tanggal'])->startOfDay();
            // Hari yang sama dengan data lama: pertahankan jam aslinya.
            $tanggalTerbit = ($berita?->tanggal_terbit && $berita->tanggal_terbit->isSameDay($dipilih))
                ? $berita->tanggal_terbit
                : $dipilih;
        } elseif ($data['status'] === 'terbit') {
            $tanggalTerbit = $berita?->tanggal_terbit ?? now();
        }

        $atribut = [
            'kategori_berita_id' => $data['kategori_berita_id'],
            'judul' => trim($data['judul']),
            'isi' => TeksKaya::html($data['isi']),
            'status' => $data['status'],
            'tanggal_terbit' => $tanggalTerbit,
        ];

        // File yang baru diunggah (dibersihkan lagi kalau penyimpanan gagal) dan file lama yang dibuang.
        $fileBaru = [];
        $fileDibuang = [];
        $aksi = $berita ? 'diperbarui' : 'disimpan';

        try {
            if ($this->gambarBaru) {
                $atribut['gambar_sampul'] = $fileBaru[] = $this->gambarBaru->store('berita', 'public');
            }

            $berita = DB::transaction(function () use ($berita, $atribut, &$fileBaru, &$fileDibuang) {
                if ($berita) {
                    if (isset($atribut['gambar_sampul']) && $berita->gambar_sampul) {
                        $fileDibuang[] = $berita->gambar_sampul;
                    }

                    $berita->update($atribut);
                } else {
                    $atribut['slug'] = $this->buatSlug($atribut['judul']);
                    $atribut['penulis_id'] = auth()->id();

                    $berita = Berita::create($atribut);
                }

                foreach ($this->galeri as $foto) {
                    if (! $foto['hapus']) {
                        continue;
                    }

                    $baris = GaleriBerita::where('berita_id', $berita->id)->find($foto['id']);

                    if ($baris) {
                        $fileDibuang[] = $baris->gambar;
                        $baris->delete();
                    }
                }

                foreach ($this->galeriBaru as $file) {
                    GaleriBerita::create([
                        'berita_id' => $berita->id,
                        'gambar' => $fileBaru[] = $file->store('berita/galeri', 'public'),
                    ]);
                }

                return $berita;
            });
        } catch (\Throwable $e) {
            report($e);
            Storage::disk('public')->delete($fileBaru);
            $this->addError('simpan', 'Berita gagal disimpan. Silakan coba lagi.');

            return;
        }

        // File lama baru dihapus setelah data pasti tersimpan.
        Storage::disk('public')->delete($fileDibuang);

        $pesan = "Berita \"{$berita->judul}\" {$aksi}.";

        if ($berita->status === 'draf') {
            $pesan .= ' Status masih draf, belum tampil di publik.';
        } elseif ($berita->terjadwal) {
            $pesan .= ' Tanggal terbit di masa depan, tampil di publik mulai tanggal tersebut.';
        }

        $this->resetForm();
        $this->dispatch('berita-disimpan', pesan: $pesan);
    }

    /** Periksa gambar sampul begitu dipilih, supaya file yang salah langsung ketahuan. */
    public function updatedGambarBaru(): void
    {
        $this->validateOnly('gambarBaru');
    }

    /** Dipanggil Livewire setiap kali admin memilih file galeri: periksa, lalu tambahkan ke antrean. */
    public function updatedUnggahGaleri(): void
    {
        try {
            $this->validate(['unggahGaleri.*' => self::ATURAN_GAMBAR]);

            $sisa = self::MAKS_GALERI - $this->jumlahGaleri();

            if (count($this->unggahGaleri) > $sisa) {
                $this->addError('unggahGaleri', 'Galeri maksimal '.self::MAKS_GALERI." foto. Sisa tempat: {$sisa} foto.");

                return;
            }

            foreach ($this->unggahGaleri as $file) {
                $this->galeriBaru[] = $file;
            }
        } finally {
            $this->unggahGaleri = [];
        }
    }

    /** Foto lama tidak langsung dihapus; baru dihapus saat tombol Simpan ditekan. */
    public function tandaiHapusGaleri(int $i): void
    {
        if (isset($this->galeri[$i])) {
            $this->galeri[$i]['hapus'] = ! $this->galeri[$i]['hapus'];
        }
    }

    public function batalGaleriBaru(int $i): void
    {
        unset($this->galeriBaru[$i]);

        $this->galeriBaru = array_values($this->galeriBaru);
        $this->resetValidation('galeriBaru.*');
    }

    /** Jumlah foto galeri setelah disimpan nanti (foto lama yang tidak ditandai hapus + foto baru). */
    private function jumlahGaleri(): int
    {
        return collect($this->galeri)->where('hapus', false)->count() + count($this->galeriBaru);
    }

    public function hapusGambar(): void
    {
        $this->gambarBaru = null;
    }

    private function buatSlug(string $judul): string
    {
        $dasar = Str::limit(Str::slug($judul), 200, '') ?: 'berita';
        $slug = $dasar;
        $i = 2;

        while (Berita::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.admin.berita-form', [
            'daftarKategori' => $this->terbuka ? KategoriBerita::orderBy('nama')->get() : collect(),
            'maksGaleri' => self::MAKS_GALERI,
        ]);
    }
}
