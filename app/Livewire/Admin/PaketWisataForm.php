<?php

namespace App\Livewire\Admin;

use App\Models\PaketWisata;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Modal tambah/ubah paket wisata (flyer + CP). Dibuka lewat event "buka-form-paket". */
class PaketWisataForm extends Component
{
    use WithFileUploads;

    public bool $terbuka = false;

    public ?int $paketId = null;

    public string $nama_paket = '';

    public string $deskripsi = '';

    public string $harga = '';

    public string $durasi = '';

    public string $nama_cp = '';

    public string $no_cp = '';

    public bool $tayang = true;

    public ?string $flyerLama = null;

    public $flyerBaru = null;

    #[On('buka-form-paket')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $p = PaketWisata::findOrFail($id);

            $this->paketId = $p->id_paket;
            $this->nama_paket = $p->nama_paket;
            $this->deskripsi = $p->deskripsi;
            $this->harga = (string) $p->harga;
            $this->durasi = (string) $p->durasi;
            $this->nama_cp = $p->nama_cp;
            $this->no_cp = $p->no_cp;
            $this->tayang = $p->tayang;
            $this->flyerLama = $p->flyer;
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

    public function updatedFlyerBaru(): void
    {
        $this->validateOnly('flyerBaru');
    }

    protected function rules(): array
    {
        return [
            'nama_paket' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'harga' => ['nullable', 'string', 'max:100'],
            'durasi' => ['nullable', 'string', 'max:100'],
            'nama_cp' => ['required', 'string', 'max:120'],
            'no_cp' => ['required', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'tayang' => ['boolean'],
            'flyerBaru' => [Rule::requiredIf(! $this->paketId), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'flyerBaru.required' => 'Flyer paket wisata wajib diunggah.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file' => ':attribute maksimal 4 MB.',
            'regex' => ':attribute hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama_paket' => 'Nama paket', 'deskripsi' => 'Deskripsi/itinerary', 'harga' => 'Harga', 'durasi' => 'Durasi',
            'nama_cp' => 'Nama contact person', 'no_cp' => 'Nomor contact person', 'flyerBaru' => 'Flyer',
        ];
    }

    public function simpan(): void
    {
        $data = $this->validate();

        $atribut = [
            'nama_paket' => trim($data['nama_paket']),
            'deskripsi' => trim($data['deskripsi']),
            'harga' => trim((string) $data['harga']) ?: null,
            'durasi' => trim((string) $data['durasi']) ?: null,
            'nama_cp' => trim($data['nama_cp']),
            'no_cp' => trim($data['no_cp']),
            'tayang' => (bool) $data['tayang'],
        ];

        $fileBaru = null;

        try {
            if ($this->flyerBaru) {
                $atribut['flyer'] = $fileBaru = $this->flyerBaru->store('paket-wisata', 'public');
            }

            $paket = $this->paketId ? tap(PaketWisata::findOrFail($this->paketId))->update($atribut) : PaketWisata::create($atribut);
        } catch (\Throwable $e) {
            report($e);

            if ($fileBaru) {
                Storage::disk('public')->delete($fileBaru);
            }

            $this->addError('simpan', 'Paket wisata gagal disimpan. Silakan coba lagi.');

            return;
        }

        if ($fileBaru && $this->flyerLama) {
            Storage::disk('public')->delete($this->flyerLama);
        }

        $pesan = $this->paketId ? "Paket \"{$paket->nama_paket}\" diperbarui." : "Paket \"{$paket->nama_paket}\" ditambahkan.";

        $this->resetForm();
        $this->dispatch('paket-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.paket-wisata-form');
    }
}
