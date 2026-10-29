<?php

namespace App\Livewire\Admin;

use App\Models\LokasiPeta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Modal tambah/ubah lokasi peta (restoran, oleh-oleh, lainnya). Dibuka lewat event "buka-form-lokasi". */
class LokasiPetaForm extends Component
{
    use WithFileUploads;

    public bool $terbuka = false;

    public ?int $lokasiId = null;

    public string $nama = '';

    public string $kategori = '';

    public string $alamat = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $deskripsi_singkat = '';

    public string $no_telepon = '';

    public ?string $gambarLama = null;

    public $gambarBaru = null;

    #[On('buka-form-lokasi')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $l = LokasiPeta::findOrFail($id);

            $this->lokasiId = $l->id_lokasi;
            $this->nama = $l->nama;
            $this->kategori = $l->kategori;
            $this->alamat = $l->alamat;
            $this->latitude = (string) $l->latitude;
            $this->longitude = (string) $l->longitude;
            $this->deskripsi_singkat = (string) $l->deskripsi_singkat;
            $this->no_telepon = (string) $l->no_telepon;
            $this->gambarLama = $l->gambar;
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

    public function updatedGambarBaru(): void
    {
        $this->validateOnly('gambarBaru');
    }

    protected function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', Rule::in(array_keys(LokasiPeta::KATEGORI))],
            'alamat' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'deskripsi_singkat' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'gambarBaru' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'in' => 'Pilihan :attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file' => ':attribute maksimal 2 MB.',
            'numeric' => ':attribute harus berupa angka (contoh -8.2151).',
            'between' => ':attribute harus di antara :min dan :max.',
            'regex' => ':attribute hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'Nama tempat', 'kategori' => 'Kategori', 'alamat' => 'Alamat', 'latitude' => 'Latitude',
            'longitude' => 'Longitude', 'deskripsi_singkat' => 'Deskripsi singkat', 'no_telepon' => 'Nomor telepon', 'gambarBaru' => 'Foto',
        ];
    }

    public function simpan(): void
    {
        $this->latitude = str_replace(',', '.', trim($this->latitude));
        $this->longitude = str_replace(',', '.', trim($this->longitude));

        $data = $this->validate();

        $atribut = [
            'nama' => trim($data['nama']),
            'kategori' => $data['kategori'],
            'alamat' => trim($data['alamat']),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'deskripsi_singkat' => trim((string) $data['deskripsi_singkat']) ?: null,
            'no_telepon' => trim((string) $data['no_telepon']) ?: null,
        ];

        $fileBaru = null;

        try {
            if ($this->gambarBaru) {
                $atribut['gambar'] = $fileBaru = $this->gambarBaru->store('lokasi-peta', 'public');
            }

            $lokasi = $this->lokasiId ? tap(LokasiPeta::findOrFail($this->lokasiId))->update($atribut) : LokasiPeta::create($atribut);
        } catch (\Throwable $e) {
            report($e);

            if ($fileBaru) {
                Storage::disk('public')->delete($fileBaru);
            }

            $this->addError('simpan', 'Data lokasi gagal disimpan. Silakan coba lagi.');

            return;
        }

        if ($fileBaru && $this->gambarLama) {
            Storage::disk('public')->delete($this->gambarLama);
        }

        $pesan = $this->lokasiId ? "Lokasi \"{$lokasi->nama}\" diperbarui." : "Lokasi \"{$lokasi->nama}\" ditambahkan.";

        $this->resetForm();
        $this->dispatch('lokasi-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.lokasi-peta-form', ['daftarKategori' => LokasiPeta::KATEGORI]);
    }
}
