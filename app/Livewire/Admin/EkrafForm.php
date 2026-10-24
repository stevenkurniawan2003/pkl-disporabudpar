<?php

namespace App\Livewire\Admin;

use App\Models\Ekraf;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal tambah/ubah pelaku ekonomi kreatif. Ditempel di halaman daftar ekraf (Admin\Ekraf)
 * dan dibuka lewat event "buka-form-ekraf".
 */
class EkrafForm extends Component
{
    public bool $terbuka = false;

    public ?int $ekrafId = null;

    public string $nama_usaha = '';

    public string $nama_pemilik = '';

    public string $subsektor = '';

    public string $alamat = '';

    public string $no_telepon = '';

    public string $media_sosial = '';

    public string $deskripsi = '';

    #[On('buka-form-ekraf')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $ekraf = Ekraf::findOrFail($id);

            $this->ekrafId = $ekraf->id_ekraf;
            $this->nama_usaha = $ekraf->nama_usaha;
            $this->nama_pemilik = $ekraf->nama_pemilik;
            $this->subsektor = $ekraf->subsektor;
            $this->alamat = $ekraf->alamat;
            $this->no_telepon = (string) $ekraf->no_telepon;
            $this->media_sosial = (string) $ekraf->media_sosial;
            $this->deskripsi = $ekraf->deskripsi;
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
            'nama_usaha' => ['required', 'string', 'max:150'],
            'nama_pemilik' => ['required', 'string', 'max:150'],
            'subsektor' => ['required', Rule::in(Ekraf::SUBSEKTOR)],
            'alamat' => ['required', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'media_sosial' => ['nullable', 'url:http,https', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:5000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'in' => ':attribute tidak valid.',
            'url' => ':attribute harus berupa alamat URL yang valid (contoh https://instagram.com/usaha).',
            'regex' => ':attribute hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama_usaha' => 'Nama usaha',
            'nama_pemilik' => 'Nama pemilik',
            'subsektor' => 'Subsektor',
            'alamat' => 'Alamat',
            'no_telepon' => 'Nomor telepon',
            'media_sosial' => 'Link media sosial',
            'deskripsi' => 'Deskripsi',
        ];
    }

    public function simpan(): void
    {
        $this->media_sosial = $this->rapikanLink($this->media_sosial);

        $data = $this->validate();

        $atribut = [
            'nama_usaha' => trim($data['nama_usaha']),
            'nama_pemilik' => trim($data['nama_pemilik']),
            'subsektor' => $data['subsektor'],
            'alamat' => trim($data['alamat']),
            'no_telepon' => trim((string) $data['no_telepon']) ?: null,
            'media_sosial' => $data['media_sosial'] ?: null,
            'deskripsi' => trim($data['deskripsi']),
        ];

        try {
            if ($this->ekrafId) {
                $ekraf = Ekraf::findOrFail($this->ekrafId);
                $ekraf->update($atribut);
                $pesan = "Data \"{$ekraf->nama_usaha}\" diperbarui.";
            } else {
                $ekraf = Ekraf::create($atribut);
                $pesan = "Data \"{$ekraf->nama_usaha}\" ditambahkan.";
            }
        } catch (\Throwable $e) {
            report($e);
            $this->addError('simpan', 'Data ekraf gagal disimpan. Silakan coba lagi.');

            return;
        }

        $this->resetForm();
        $this->dispatch('ekraf-disimpan', pesan: $pesan);
    }

    /** "instagram.com/usaha" -> "https://instagram.com/usaha" supaya admin tidak wajib mengetik https://. */
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
        return view('livewire.admin.ekraf-form', [
            'daftarSubsektor' => Ekraf::SUBSEKTOR,
        ]);
    }
}
