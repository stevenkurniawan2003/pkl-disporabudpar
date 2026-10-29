<?php

namespace App\Livewire\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Gambar utama + galeri foto untuk form modal admin (OPK, Cagar Budaya).
 * Alurnya sama dengan HotelForm: foto galeri baru diantre di $galeriBaru, foto lama hanya ditandai
 * hapus dan baru benar-benar dihapus saat Simpan. Komponen wajib punya rules() untuk 'gambarBaru'
 * dan 'galeriBaru.*', serta memakai WithFileUploads.
 */
trait KelolaGambarGaleri
{
    /** Batas jumlah foto galeri (tidak termasuk gambar utama). Sama dengan wisata/hotel. */
    public const MAKS_GALERI = 20;

    public const ATURAN_GAMBAR = ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

    public ?string $gambarLama = null;

    public $gambarBaru = null;

    /** Foto galeri yang sudah tersimpan: [['id', 'gambar', 'hapus'], ...]. */
    public array $galeri = [];

    /** Penampung input file (bisa pilih banyak). Isinya dipindah ke $galeriBaru setiap kali memilih. */
    public array $unggahGaleri = [];

    /** Foto galeri baru yang belum disimpan. */
    public array $galeriBaru = [];

    /** Periksa gambar utama begitu dipilih, supaya file yang salah (mis. PDF) langsung ketahuan. */
    public function updatedGambarBaru(): void
    {
        $this->validateOnly('gambarBaru');
    }

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

    /** Jumlah foto galeri setelah disimpan nanti (foto lama yang tidak ditandai hapus + foto baru). */
    private function jumlahGaleri(): int
    {
        return collect($this->galeri)->where('hapus', false)->count() + count($this->galeriBaru);
    }

    /**
     * Simpan perubahan galeri di dalam transaksi.
     *
     * @param  class-string<Model>  $modelGaleri
     * @param  array<int, string>  $fileBaru  file yang baru disimpan (dibersihkan kalau transaksi gagal)
     * @param  array<int, string>  $fileDibuang  file lama yang dihapus setelah commit
     */
    private function simpanGaleri(string $modelGaleri, string $kolomInduk, int $idInduk, string $folder, array &$fileBaru, array &$fileDibuang): void
    {
        foreach ($this->galeri as $foto) {
            if (! $foto['hapus']) {
                continue;
            }

            $baris = $modelGaleri::where($kolomInduk, $idInduk)->find($foto['id']);

            if ($baris) {
                $fileDibuang[] = $baris->gambar;
                $baris->delete();
            }
        }

        foreach ($this->galeriBaru as $file) {
            $modelGaleri::create([
                $kolomInduk => $idInduk,
                'gambar' => $fileBaru[] = $file->store($folder, 'public'),
            ]);
        }
    }

    /** Pesan validasi gambar yang sama untuk semua form bergambar. */
    private function pesanGambar(): array
    {
        return [
            'gambarBaru.required' => 'Gambar utama wajib diunggah.',
            'max.file' => ':attribute maksimal 2 MB.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
        ];
    }
}
