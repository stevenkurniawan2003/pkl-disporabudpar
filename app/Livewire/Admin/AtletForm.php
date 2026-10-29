<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\FormBiodataOlahraga;
use App\Models\Atlet;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

/** Modal "Tambah Atlet" / ubah atlet. Ditempel di Admin\Atlet, dibuka lewat event "buka-form-atlet". */
class AtletForm extends Component
{
    use FormBiodataOlahraga;

    public bool $terbuka = false;

    public ?int $atletId = null;

    public string $klub = '';

    public string $prestasi_terakhir = '';

    public string $status_binaan = '';

    #[On('buka-form-atlet')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $a = Atlet::findOrFail($id);

            $this->atletId = $a->id_atlet;
            $this->isiBiodata($a);
            $this->klub = (string) $a->klub;
            $this->prestasi_terakhir = (string) $a->prestasi_terakhir;
            $this->status_binaan = $a->status_binaan;
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
            ...$this->aturanBiodata(),
            'klub' => ['nullable', 'string', 'max:150'],
            'prestasi_terakhir' => ['nullable', 'string', 'max:1000'],
            'status_binaan' => ['required', Rule::in(Atlet::STATUS_BINAAN)],
        ];
    }

    protected function messages(): array
    {
        return [...$this->pesanBiodata(), 'status_binaan.required' => 'Status binaan wajib dipilih.'];
    }

    protected function validationAttributes(): array
    {
        return [...$this->atributBiodata(), 'klub' => 'Klub', 'prestasi_terakhir' => 'Prestasi terakhir', 'status_binaan' => 'Status binaan'];
    }

    public function simpan(): void
    {
        $data = $this->validate();

        $atribut = [
            ...$this->dataBiodata($data),
            'klub' => trim((string) $data['klub']) ?: null,
            'prestasi_terakhir' => trim((string) $data['prestasi_terakhir']) ?: null,
            'status_binaan' => $data['status_binaan'],
        ];

        try {
            $atlet = $this->atletId ? tap(Atlet::findOrFail($this->atletId))->update($atribut) : Atlet::create($atribut);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('simpan', 'Data atlet gagal disimpan. Silakan coba lagi.');

            return;
        }

        $pesan = $this->atletId ? "Atlet \"{$atlet->nama_lengkap}\" diperbarui." : "Atlet \"{$atlet->nama_lengkap}\" ditambahkan.";

        $this->resetForm();
        $this->dispatch('atlet-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.atlet-form', [...$this->viewBiodata(), 'daftarBinaan' => Atlet::STATUS_BINAAN]);
    }
}
