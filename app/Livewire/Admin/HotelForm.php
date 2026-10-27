<?php

namespace App\Livewire\Admin;

use App\Models\GaleriHotel;
use App\Models\Hotel;
use App\Support\TeksKaya;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Modal tambah/ubah hotel. Ditempel di halaman daftar hotel (Admin\Hotel)
 * dan dibuka lewat event "buka-form-hotel".
 */
class HotelForm extends Component
{
    use WithFileUploads;

    /** Batas jumlah foto galeri per hotel (tidak termasuk gambar utama). Sama dengan wisata. */
    public const MAKS_GALERI = 20;

    private const ATURAN_GAMBAR = ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

    public bool $terbuka = false;

    public ?int $hotelId = null;

    public string $nama_hotel = '';

    public string $deskripsi_singkat = '';

    public string $narasi = '';

    public string $alamat = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $link_google_maps = '';

    public string $link_website = '';

    public string $no_telepon = '';

    public ?string $gambarLama = null;

    public $gambarBaru = null;

    /** Foto galeri yang sudah tersimpan: [['id', 'gambar', 'hapus'], ...]. */
    public array $galeri = [];

    /** Penampung input file (bisa pilih banyak). Isinya dipindah ke $galeriBaru setiap kali memilih. */
    public array $unggahGaleri = [];

    /** Foto galeri baru yang belum disimpan. */
    public array $galeriBaru = [];

    #[On('buka-form-hotel')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $hotel = Hotel::with('galeri')->findOrFail($id);

            $this->hotelId = $hotel->id_hotel;
            $this->nama_hotel = $hotel->nama_hotel;
            $this->deskripsi_singkat = $hotel->deskripsi_singkat;
            $this->narasi = TeksKaya::html($hotel->narasi);
            $this->alamat = $hotel->alamat;
            $this->latitude = (string) $hotel->latitude;
            $this->longitude = (string) $hotel->longitude;
            $this->link_google_maps = (string) $hotel->link_google_maps;
            $this->link_website = (string) $hotel->link_website;
            $this->no_telepon = (string) $hotel->no_telepon;
            $this->gambarLama = $hotel->gambar_utama;
            $this->galeri = $hotel->galeri->map(fn (GaleriHotel $g) => [
                'id' => $g->id_galeri_hotel,
                'gambar' => $g->gambar,
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
            'nama_hotel' => ['required', 'string', 'max:150'],
            'deskripsi_singkat' => ['required', 'string', 'max:200'],
            'narasi' => ['required', 'string', 'max:20000'],
            'alamat' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'link_google_maps' => ['nullable', 'url:http,https', 'max:255'],
            'link_website' => ['nullable', 'url:http,https', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            // Gambar utama wajib saat tambah; saat ubah boleh dikosongkan (pakai gambar lama).
            'gambarBaru' => [Rule::requiredIf(! $this->hotelId), 'nullable', ...self::ATURAN_GAMBAR],
            'galeriBaru.*' => self::ATURAN_GAMBAR,
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'gambarBaru.required' => 'Gambar utama wajib diunggah.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file' => ':attribute maksimal 2 MB.',
            'numeric' => ':attribute harus berupa angka (contoh -8.2151).',
            'between' => ':attribute harus di antara :min dan :max.',
            'url' => ':attribute harus berupa alamat URL yang valid (contoh https://contoh.com).',
            'regex' => ':attribute hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama_hotel' => 'Nama hotel',
            'deskripsi_singkat' => 'Deskripsi singkat',
            'narasi' => 'Narasi',
            'alamat' => 'Alamat',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'link_google_maps' => 'Link Google Maps',
            'link_website' => 'Link website',
            'no_telepon' => 'Nomor telepon',
            'gambarBaru' => 'Gambar utama',
            'unggahGaleri.*' => 'Foto galeri',
            'galeriBaru.*' => 'Foto galeri',
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
        $this->link_website = $this->rapikanLink($this->link_website);

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
            'nama_hotel' => trim($data['nama_hotel']),
            'deskripsi_singkat' => trim($data['deskripsi_singkat']),
            'narasi' => TeksKaya::html($data['narasi']),
            'alamat' => trim($data['alamat']),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'link_google_maps' => $data['link_google_maps'] ?: null,
            'link_website' => $data['link_website'] ?: null,
            'no_telepon' => trim((string) $data['no_telepon']) ?: null,
        ];

        // File yang baru diunggah (dibersihkan lagi kalau penyimpanan gagal) dan file lama yang dibuang.
        $fileBaru = [];
        $fileDibuang = [];

        try {
            if ($this->gambarBaru) {
                $atribut['gambar_utama'] = $fileBaru[] = $this->gambarBaru->store('hotel', 'public');
            }

            $hotel = DB::transaction(function () use ($atribut, &$fileBaru, &$fileDibuang) {
                if ($this->hotelId) {
                    $hotel = Hotel::findOrFail($this->hotelId);

                    if (isset($atribut['gambar_utama']) && $hotel->gambar_utama) {
                        $fileDibuang[] = $hotel->gambar_utama;
                    }

                    $hotel->update($atribut);
                } else {
                    $hotel = Hotel::create($atribut);
                }

                foreach ($this->galeri as $foto) {
                    if (! $foto['hapus']) {
                        continue;
                    }

                    $baris = GaleriHotel::where('id_hotel', $hotel->id_hotel)->find($foto['id']);

                    if ($baris) {
                        $fileDibuang[] = $baris->gambar;
                        $baris->delete();
                    }
                }

                foreach ($this->galeriBaru as $file) {
                    GaleriHotel::create([
                        'id_hotel' => $hotel->id_hotel,
                        'gambar' => $fileBaru[] = $file->store('hotel/galeri', 'public'),
                    ]);
                }

                return $hotel;
            });
        } catch (\Throwable $e) {
            report($e);
            Storage::disk('public')->delete($fileBaru);
            $this->addError('simpan', 'Data hotel gagal disimpan. Silakan coba lagi.');

            return;
        }

        // File lama baru dihapus setelah data pasti tersimpan.
        Storage::disk('public')->delete($fileDibuang);

        $pesan = $this->hotelId
            ? "Hotel \"{$hotel->nama_hotel}\" diperbarui."
            : "Hotel \"{$hotel->nama_hotel}\" ditambahkan.";

        $this->resetForm();
        $this->dispatch('hotel-disimpan', pesan: $pesan);
    }

    /** Jumlah foto galeri setelah disimpan nanti (foto lama yang tidak ditandai hapus + foto baru). */
    private function jumlahGaleri(): int
    {
        return collect($this->galeri)->where('hapus', false)->count() + count($this->galeriBaru);
    }

    /** "www.hotel.com" -> "https://www.hotel.com" supaya admin tidak wajib mengetik https://. */
    private function rapikanLink(string $link): string
    {
        $link = trim($link);

        if ($link !== '' && ! preg_match('#^[a-z][a-z0-9+.-]*://#i', $link)) {
            $link = 'https://'.$link;
        }

        return $link;
    }

    public function render()
    {
        return view('livewire.admin.hotel-form', [
            'maksGaleri' => self::MAKS_GALERI,
        ]);
    }
}
