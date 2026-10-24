<?php

namespace App\Livewire\Admin;

use App\Models\Kesenian;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal tambah/ubah kesenian. Ditempel di halaman daftar kesenian (Admin\Kesenian)
 * dan dibuka lewat event "buka-form-kesenian".
 */
class KesenianForm extends Component
{
    public bool $terbuka = false;

    public ?int $kesenianId = null;

    public string $nama_kesenian = '';

    public string $kategori = '';

    public string $asal_daerah = '';

    public string $deskripsi = '';

    #[On('buka-form-kesenian')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $kesenian = Kesenian::findOrFail($id);

            $this->kesenianId = $kesenian->id_kesenian;
            $this->nama_kesenian = $kesenian->nama_kesenian;
            $this->kategori = $kesenian->kategori;
            $this->asal_daerah = (string) $kesenian->asal_daerah;
            $this->deskripsi = $kesenian->deskripsi;
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
            'nama_kesenian' => ['required', 'string', 'max:150'],
            'kategori' => ['required', Rule::in(Kesenian::KATEGORI)],
            'asal_daerah' => ['nullable', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'max:5000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'in' => ':attribute tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama_kesenian' => 'Nama kesenian',
            'kategori' => 'Kategori',
            'asal_daerah' => 'Asal daerah',
            'deskripsi' => 'Deskripsi',
        ];
    }

    public function simpan(): void
    {
        $data = $this->validate();

        $atribut = [
            'nama_kesenian' => trim($data['nama_kesenian']),
            'kategori' => $data['kategori'],
            'asal_daerah' => trim((string) $data['asal_daerah']) ?: null,
            'deskripsi' => trim($data['deskripsi']),
        ];

        try {
            if ($this->kesenianId) {
                $kesenian = Kesenian::findOrFail($this->kesenianId);
                $kesenian->update($atribut);
                $pesan = "Kesenian \"{$kesenian->nama_kesenian}\" diperbarui.";
            } else {
                $kesenian = Kesenian::create($atribut);
                $pesan = "Kesenian \"{$kesenian->nama_kesenian}\" ditambahkan.";
            }
        } catch (\Throwable $e) {
            report($e);
            $this->addError('simpan', 'Data kesenian gagal disimpan. Silakan coba lagi.');

            return;
        }

        $this->resetForm();
        $this->dispatch('kesenian-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.kesenian-form', [
            'daftarKategori' => Kesenian::KATEGORI,
        ]);
    }
}
