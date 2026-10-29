<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\KelolaGambarGaleri;
use App\Models\CagarBudaya;
use App\Models\GaleriCagarBudaya;
use App\Support\TeksKaya;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Modal tambah/ubah ODCB/Cagar Budaya. Ditempel di Admin\CagarBudaya, dibuka lewat event "buka-form-cagar-budaya".
 */
class CagarBudayaForm extends Component
{
    use KelolaGambarGaleri;
    use WithFileUploads;

    public bool $terbuka = false;

    public ?int $cagarBudayaId = null;

    public string $nama = '';

    public string $alamat = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $kategori = '';

    public string $tahun = '';

    public string $status = '';

    public string $kondisi = '';

    public string $deskripsi_sejarah = '';

    #[On('buka-form-cagar-budaya')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $cb = CagarBudaya::with('galeri')->findOrFail($id);

            $this->cagarBudayaId = $cb->id_cagar_budaya;
            $this->nama = $cb->nama;
            $this->alamat = $cb->alamat;
            $this->latitude = (string) $cb->latitude;
            $this->longitude = (string) $cb->longitude;
            $this->kategori = $cb->kategori;
            $this->tahun = (string) $cb->tahun;
            $this->status = $cb->status;
            $this->kondisi = $cb->kondisi;
            $this->deskripsi_sejarah = TeksKaya::html($cb->deskripsi_sejarah);
            $this->gambarLama = $cb->gambar_utama;
            $this->galeri = $cb->galeri->map(fn (GaleriCagarBudaya $g) => [
                'id' => $g->id_galeri_cagar_budaya,
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
            'nama' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'kategori' => ['required', Rule::in(CagarBudaya::KATEGORI)],
            'tahun' => ['nullable', 'integer', 'between:100,'.now()->year],
            'status' => ['required', Rule::in(array_keys(CagarBudaya::STATUS))],
            'kondisi' => ['required', Rule::in(CagarBudaya::KONDISI)],
            'deskripsi_sejarah' => ['required', 'string', 'max:20000'],
            'gambarBaru' => [Rule::requiredIf(! $this->cagarBudayaId), 'nullable', ...self::ATURAN_GAMBAR],
            'galeriBaru.*' => self::ATURAN_GAMBAR,
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
            'kondisi.required' => 'Kondisi wajib dipilih.',
            'in' => 'Pilihan :attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'numeric' => ':attribute harus berupa angka (contoh -8.2151).',
            'integer' => ':attribute harus berupa angka tahun (contoh 1930).',
            'between' => ':attribute harus di antara :min dan :max.',
            ...$this->pesanGambar(),
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'Nama ODCB/CB',
            'alamat' => 'Alamat',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'kategori' => 'Kategori',
            'tahun' => 'Tahun',
            'status' => 'Status',
            'kondisi' => 'Kondisi',
            'deskripsi_sejarah' => 'Deskripsi sejarah',
            'gambarBaru' => 'Gambar utama',
            'unggahGaleri.*' => 'Gambar tambahan',
            'galeriBaru.*' => 'Gambar tambahan',
        ];
    }

    public function simpan(): void
    {
        $this->latitude = str_replace(',', '.', trim($this->latitude));
        $this->longitude = str_replace(',', '.', trim($this->longitude));
        $this->tahun = trim($this->tahun);

        if (TeksKaya::kosong($this->deskripsi_sejarah)) {
            $this->deskripsi_sejarah = '';
        }

        $data = $this->validate();

        if ($this->jumlahGaleri() > self::MAKS_GALERI) {
            $this->addError('unggahGaleri', 'Galeri maksimal '.self::MAKS_GALERI.' foto. Hapus sebagian foto dulu.');

            return;
        }

        $atribut = [
            'nama' => trim($data['nama']),
            'alamat' => trim($data['alamat']),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'kategori' => $data['kategori'],
            'tahun' => $data['tahun'] !== '' ? (int) $data['tahun'] : null,
            'status' => $data['status'],
            'kondisi' => $data['kondisi'],
            'deskripsi_sejarah' => TeksKaya::html($data['deskripsi_sejarah']),
        ];

        $fileBaru = [];
        $fileDibuang = [];

        try {
            if ($this->gambarBaru) {
                $atribut['gambar_utama'] = $fileBaru[] = $this->gambarBaru->store('cagar-budaya', 'public');
            }

            $cb = DB::transaction(function () use ($atribut, &$fileBaru, &$fileDibuang) {
                if ($this->cagarBudayaId) {
                    $cb = CagarBudaya::findOrFail($this->cagarBudayaId);

                    if (isset($atribut['gambar_utama'])) {
                        $fileDibuang[] = $cb->gambar_utama;
                    }

                    $cb->update($atribut);
                } else {
                    $cb = CagarBudaya::create($atribut + ['dicatat_oleh' => auth()->id()]);
                }

                $this->simpanGaleri(GaleriCagarBudaya::class, 'id_cagar_budaya', $cb->id_cagar_budaya, 'cagar-budaya/galeri', $fileBaru, $fileDibuang);

                return $cb;
            });
        } catch (\Throwable $e) {
            report($e);
            Storage::disk('public')->delete($fileBaru);
            $this->addError('simpan', 'Data ODCB/CB gagal disimpan. Silakan coba lagi.');

            return;
        }

        Storage::disk('public')->delete(array_filter($fileDibuang));

        $pesan = $this->cagarBudayaId ? "\"{$cb->nama}\" diperbarui." : "\"{$cb->nama}\" ditambahkan.";

        $this->resetForm();
        $this->dispatch('cagar-budaya-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.cagar-budaya-form', [
            'maksGaleri' => self::MAKS_GALERI,
            'daftarKategori' => CagarBudaya::KATEGORI,
            'daftarStatus' => CagarBudaya::STATUS,
            'daftarKondisi' => CagarBudaya::KONDISI,
        ]);
    }
}
