<?php

namespace App\Livewire\Admin;

use App\Models\GaleriWisata;
use App\Models\KategoriWisata;
use App\Models\Kecamatan;
use App\Models\Wisata;
use App\Support\TeksKaya;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Modal tambah/ubah wisata. Ditempel di halaman daftar wisata (Admin\Wisata)
 * dan dibuka lewat event "buka-form-wisata".
 */
class WisataForm extends Component
{
    use WithFileUploads;

    /** Batas jumlah foto galeri per wisata (tidak termasuk gambar utama). */
    public const MAKS_GALERI = 20;

    private const ATURAN_GAMBAR = ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

    public bool $terbuka = false;

    public ?int $wisataId = null;

    public string $nama = '';

    public ?int $kategori_id = null;

    public ?int $kecamatan_id = null;

    public string $deskripsi_singkat = '';

    public string $narasi = '';

    public string $alamat = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $link_google_maps = '';

    public string $status = 'tayang';

    public bool $unggulan = false;

    public ?string $gambarLama = null;

    public $gambarBaru = null;

    /** Foto galeri yang sudah tersimpan: [['id', 'gambar', 'keterangan', 'hapus'], ...]. */
    public array $galeri = [];

    /** Penampung input file (bisa pilih banyak). Isinya dipindah ke $galeriBaru setiap kali memilih. */
    public array $unggahGaleri = [];

    /** Foto galeri baru yang belum disimpan, berpasangan per indeks dengan $keteranganBaru. */
    public array $galeriBaru = [];

    public array $keteranganBaru = [];

    #[On('buka-form-wisata')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $wisata = Wisata::with('galeri')->findOrFail($id);

            $this->wisataId = $wisata->id;
            $this->nama = $wisata->nama;
            $this->kategori_id = $wisata->kategori_id;
            $this->kecamatan_id = $wisata->kecamatan_id;
            $this->deskripsi_singkat = (string) $wisata->deskripsi_singkat;
            $this->narasi = TeksKaya::html($wisata->narasi);
            $this->alamat = $wisata->alamat;
            $this->latitude = (string) $wisata->latitude;
            $this->longitude = (string) $wisata->longitude;
            $this->link_google_maps = (string) $wisata->link_google_maps;
            $this->status = $wisata->status;
            $this->unggulan = $wisata->unggulan;
            $this->gambarLama = $wisata->gambar_utama;
            $this->galeri = $wisata->galeri->map(fn (GaleriWisata $g) => [
                'id' => $g->id,
                'gambar' => $g->gambar,
                'keterangan' => (string) $g->keterangan,
                'hapus' => false,
            ])->all();
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
            'nama' => ['required', 'string', 'max:150'],
            'kategori_id' => ['required', 'exists:kategori_wisata,id'],
            'kecamatan_id' => ['required', 'exists:kecamatan,id'],
            'deskripsi_singkat' => ['required', 'string', 'max:200'],
            'narasi' => ['nullable', 'string', 'max:20000'],
            'alamat' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'link_google_maps' => ['nullable', 'url:http,https', 'max:255'],
            'status' => ['required', 'in:tayang,draf'],
            'unggulan' => ['boolean'],
            'gambarBaru' => ['nullable', ...self::ATURAN_GAMBAR],
            'galeri.*.keterangan' => ['nullable', 'string', 'max:200'],
            'galeriBaru.*' => self::ATURAN_GAMBAR,
            'keteranganBaru.*' => ['nullable', 'string', 'max:200'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file' => ':attribute maksimal 2 MB.',
            'numeric' => ':attribute harus berupa angka (contoh -8.2151).',
            'between' => ':attribute harus di antara :min dan :max.',
            'exists' => ':attribute tidak valid.',
            'url' => ':attribute harus berupa alamat URL yang valid (contoh https://contoh.com).',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'Nama wisata',
            'kategori_id' => 'Kategori',
            'kecamatan_id' => 'Kecamatan',
            'deskripsi_singkat' => 'Deskripsi singkat',
            'narasi' => 'Narasi',
            'alamat' => 'Alamat',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'link_google_maps' => 'Link Google Maps',
            'status' => 'Status',
            'gambarBaru' => 'Gambar utama',
            'galeri.*.keterangan' => 'Keterangan foto',
            'unggahGaleri.*' => 'Foto galeri',
            'galeriBaru.*' => 'Foto galeri',
            'keteranganBaru.*' => 'Keterangan foto',
        ];
    }

    /** Periksa gambar utama begitu dipilih, supaya file yang salah langsung ketahuan. */
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
                $this->keteranganBaru[] = '';
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
        unset($this->galeriBaru[$i], $this->keteranganBaru[$i]);

        $this->galeriBaru = array_values($this->galeriBaru);
        $this->keteranganBaru = array_values($this->keteranganBaru);
        $this->resetValidation(['galeriBaru.*', 'keteranganBaru.*']);
    }

    public function hapusGambar(): void
    {
        $this->gambarBaru = null;
    }

    public function simpan(): void
    {
        // Izinkan koma desimal (-8,2151) dan hilangkan spasi.
        $this->latitude = str_replace(',', '.', trim($this->latitude));
        $this->longitude = str_replace(',', '.', trim($this->longitude));
        $this->link_google_maps = $this->rapikanLink($this->link_google_maps);

        // Editor mengirim "<p></p>" kalau isinya dihapus semua: anggap kosong.
        if (TeksKaya::kosong($this->narasi)) {
            $this->narasi = '';
        }

        $data = $this->validate();

        if ($this->jumlahGaleri() > self::MAKS_GALERI) {
            $this->addError('unggahGaleri', 'Galeri maksimal '.self::MAKS_GALERI.' foto. Hapus sebagian foto dulu.');

            return;
        }

        $atribut = [
            'kategori_id' => $data['kategori_id'],
            'kecamatan_id' => $data['kecamatan_id'],
            'nama' => trim($data['nama']),
            'deskripsi_singkat' => trim($data['deskripsi_singkat']),
            'narasi' => $data['narasi'] !== '' ? TeksKaya::html($data['narasi']) : null,
            'alamat' => trim($data['alamat']),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'link_google_maps' => $data['link_google_maps'] ?: null,
            'status' => $data['status'],
            'unggulan' => $data['unggulan'],
        ];

        // File yang baru diunggah (dibersihkan lagi kalau penyimpanan gagal) dan file lama yang dibuang.
        $fileBaru = [];
        $fileDibuang = [];

        try {
            if ($this->gambarBaru) {
                $atribut['gambar_utama'] = $fileBaru[] = $this->gambarBaru->store('wisata', 'public');
            }

            $wisata = DB::transaction(function () use ($atribut, &$fileBaru, &$fileDibuang) {
                if ($this->wisataId) {
                    $wisata = Wisata::findOrFail($this->wisataId);

                    if (isset($atribut['gambar_utama']) && $wisata->gambar_utama) {
                        $fileDibuang[] = $wisata->gambar_utama;
                    }

                    $wisata->update($atribut);
                } else {
                    $atribut['slug'] = $this->buatSlug($atribut['nama']);
                    $atribut['dibuat_oleh'] = auth()->id();

                    $wisata = Wisata::create($atribut);
                }

                foreach ($this->galeri as $foto) {
                    $baris = GaleriWisata::where('wisata_id', $wisata->id)->find($foto['id']);

                    if (! $baris) {
                        continue;
                    }

                    if ($foto['hapus']) {
                        $fileDibuang[] = $baris->gambar;
                        $baris->delete();
                    } else {
                        $baris->update(['keterangan' => $this->rapikanKeterangan($foto['keterangan'])]);
                    }
                }

                $urutan = (int) GaleriWisata::where('wisata_id', $wisata->id)->max('urutan');

                foreach ($this->galeriBaru as $i => $file) {
                    GaleriWisata::create([
                        'wisata_id' => $wisata->id,
                        'gambar' => $fileBaru[] = $file->store('wisata/galeri', 'public'),
                        'keterangan' => $this->rapikanKeterangan($this->keteranganBaru[$i] ?? ''),
                        'urutan' => ++$urutan,
                    ]);
                }

                return $wisata;
            });
        } catch (\Throwable $e) {
            report($e);
            Storage::disk('public')->delete($fileBaru);
            $this->addError('simpan', 'Data wisata gagal disimpan. Silakan coba lagi.');

            return;
        }

        // File lama baru dihapus setelah data pasti tersimpan.
        Storage::disk('public')->delete($fileDibuang);

        $pesan = $this->wisataId
            ? "Wisata \"{$wisata->nama}\" diperbarui."
            : "Wisata \"{$wisata->nama}\" ditambahkan.";

        if ($wisata->status === 'draf') {
            $pesan .= ' Status masih draf, belum tampil di publik.';
        }

        $this->resetForm();
        $this->dispatch('wisata-disimpan', pesan: $pesan);
    }

    /** Jumlah foto galeri setelah disimpan nanti (foto lama yang tidak ditandai hapus + foto baru). */
    private function jumlahGaleri(): int
    {
        return collect($this->galeri)->where('hapus', false)->count() + count($this->galeriBaru);
    }

    private function rapikanKeterangan(?string $teks): ?string
    {
        $teks = trim((string) $teks);

        return $teks !== '' ? $teks : null;
    }

    /** "maps.app.goo.gl/abc" -> "https://maps.app.goo.gl/abc" supaya admin tidak wajib mengetik https://. */
    private function rapikanLink(string $link): string
    {
        $link = trim($link);

        if ($link !== '' && ! preg_match('#^[a-z][a-z0-9+.-]*://#i', $link)) {
            $link = 'https://'.$link;
        }

        return $link;
    }

    private function buatSlug(string $nama): string
    {
        $dasar = Str::slug($nama) ?: 'wisata';
        $slug = $dasar;
        $i = 2;

        // withTrashed: indeks unik slug juga berlaku untuk data yang sudah dihapus.
        while (Wisata::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.admin.wisata-form', [
            'daftarKategori' => $this->terbuka ? KategoriWisata::orderBy('nama')->get() : collect(),
            'daftarKecamatan' => $this->terbuka ? Kecamatan::orderBy('nama')->get() : collect(),
            'maksGaleri' => self::MAKS_GALERI,
        ]);
    }
}
