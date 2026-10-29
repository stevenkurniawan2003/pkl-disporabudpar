<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\FormBiodataOlahraga;
use App\Models\Pelatih;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Modal "Tambah Pelatih" / ubah pelatih. Ditempel di Admin\Pelatih, dibuka lewat event "buka-form-pelatih".
 * Sertifikat/lisensi "Punya" -> wajib nomor dan file (.jpeg/.pdf); "Tidak Punya" -> nomor & file lama dibuang.
 */
class PelatihForm extends Component
{
    use FormBiodataOlahraga;
    use WithFileUploads;

    public bool $terbuka = false;

    public ?int $pelatihId = null;

    /** 'ya' / 'tidak' (pilihan "Punya" / "Tidak Punya"). */
    public string $punya_lisensi = 'tidak';

    public string $nomor_lisensi = '';

    public ?string $fileLama = null;

    public $fileBaru = null;

    #[On('buka-form-pelatih')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $p = Pelatih::findOrFail($id);

            $this->pelatihId = $p->id_pelatih;
            $this->isiBiodata($p);
            $this->punya_lisensi = $p->punya_lisensi ? 'ya' : 'tidak';
            $this->nomor_lisensi = (string) $p->nomor_lisensi;
            $this->fileLama = $p->file_lisensi;
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

    public function updatedFileBaru(): void
    {
        $this->validateOnly('fileBaru');
    }

    protected function rules(): array
    {
        $punya = $this->punya_lisensi === 'ya';

        return [
            ...$this->aturanBiodata(),
            'punya_lisensi' => ['required', Rule::in(['ya', 'tidak'])],
            'nomor_lisensi' => [Rule::requiredIf($punya), 'nullable', 'string', 'max:100'],
            // File wajib saat punya lisensi dan belum ada file tersimpan.
            'fileBaru' => [Rule::requiredIf($punya && ! $this->fileLama), 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            ...$this->pesanBiodata(),
            'nomor_lisensi.required' => 'Nomor sertifikat/lisensi wajib diisi kalau memilih "Punya".',
            'fileBaru.required' => 'Foto/berkas sertifikat/lisensi wajib diunggah kalau memilih "Punya".',
            'mimes' => ':attribute harus berformat JPEG, PNG, atau PDF.',
            'max.file' => ':attribute maksimal 2 MB.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [...$this->atributBiodata(), 'punya_lisensi' => 'Sertifikat/lisensi', 'nomor_lisensi' => 'Nomor sertifikat/lisensi', 'fileBaru' => 'Foto sertifikat/lisensi'];
    }

    public function simpan(): void
    {
        $data = $this->validate();
        $punya = $data['punya_lisensi'] === 'ya';

        $atribut = [
            ...$this->dataBiodata($data),
            'punya_lisensi' => $punya,
            'nomor_lisensi' => $punya ? trim((string) $data['nomor_lisensi']) : null,
        ];

        $fileBaru = null;
        $fileDibuang = null;

        try {
            if ($punya && $this->fileBaru) {
                $atribut['file_lisensi'] = $fileBaru = $this->fileBaru->store('pelatih/lisensi', 'public');
                $fileDibuang = $this->fileLama;
            } elseif (! $punya) {
                $atribut['file_lisensi'] = null;
                $fileDibuang = $this->fileLama;
            }

            $pelatih = $this->pelatihId ? tap(Pelatih::findOrFail($this->pelatihId))->update($atribut) : Pelatih::create($atribut);
        } catch (\Throwable $e) {
            report($e);

            if ($fileBaru) {
                Storage::disk('public')->delete($fileBaru);
            }

            $this->addError('simpan', 'Data pelatih gagal disimpan. Silakan coba lagi.');

            return;
        }

        if ($fileDibuang) {
            Storage::disk('public')->delete($fileDibuang);
        }

        $pesan = $this->pelatihId ? "Pelatih \"{$pelatih->nama_lengkap}\" diperbarui." : "Pelatih \"{$pelatih->nama_lengkap}\" ditambahkan.";

        $this->resetForm();
        $this->dispatch('pelatih-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.pelatih-form', $this->viewBiodata());
    }
}
