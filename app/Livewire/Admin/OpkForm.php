<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\KelolaGambarGaleri;
use App\Models\GaleriOpk;
use App\Models\Opk;
use App\Support\TeksKaya;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Modal tambah/ubah Objek Pemajuan Kebudayaan. Ditempel di Admin\Opk, dibuka lewat event "buka-form-opk".
 */
class OpkForm extends Component
{
    use KelolaGambarGaleri;
    use WithFileUploads;

    public bool $terbuka = false;

    public ?int $opkId = null;

    public string $nama = '';

    public string $lokasi_persebaran = '';

    public string $persebaran = '';

    public string $jenis = '';

    public string $tahun = '';

    public string $pelaku_budaya = '';

    public string $kondisi = '';

    public string $deskripsi = '';

    #[On('buka-form-opk')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $opk = Opk::with('galeri')->findOrFail($id);

            $this->opkId = $opk->id_opk;
            $this->nama = $opk->nama;
            $this->lokasi_persebaran = $opk->lokasi_persebaran;
            $this->persebaran = (string) $opk->persebaran;
            $this->jenis = $opk->jenis;
            $this->tahun = (string) $opk->tahun;
            $this->pelaku_budaya = (string) $opk->pelaku_budaya;
            $this->kondisi = $opk->kondisi;
            $this->deskripsi = TeksKaya::html($opk->deskripsi);
            $this->gambarLama = $opk->gambar_utama;
            $this->galeri = $opk->galeri->map(fn (GaleriOpk $g) => [
                'id' => $g->id_galeri_opk,
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
            'lokasi_persebaran' => ['required', 'string', 'max:255'],
            'persebaran' => ['nullable', 'string', 'max:2000'],
            'jenis' => ['required', Rule::in(Opk::JENIS)],
            'tahun' => ['nullable', 'integer', 'between:1000,'.now()->year],
            'pelaku_budaya' => ['nullable', 'string', 'max:255'],
            'kondisi' => ['required', Rule::in(Opk::KONDISI)],
            'deskripsi' => ['required', 'string', 'max:20000'],
            'gambarBaru' => [Rule::requiredIf(! $this->opkId), 'nullable', ...self::ATURAN_GAMBAR],
            'galeriBaru.*' => self::ATURAN_GAMBAR,
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'jenis.required' => 'Jenis OPK wajib dipilih.',
            'kondisi.required' => 'Kondisi wajib dipilih.',
            'in' => 'Pilihan :attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'integer' => ':attribute harus berupa angka tahun (contoh 1950).',
            'between' => ':attribute harus di antara :min dan :max.',
            ...$this->pesanGambar(),
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'Nama OPK',
            'lokasi_persebaran' => 'Lokasi persebaran',
            'persebaran' => 'Persebaran',
            'jenis' => 'Jenis OPK',
            'tahun' => 'Tahun',
            'pelaku_budaya' => 'Pelaku budaya',
            'kondisi' => 'Kondisi',
            'deskripsi' => 'Deskripsi OPK',
            'gambarBaru' => 'Gambar utama',
            'unggahGaleri.*' => 'Gambar OPK tambahan',
            'galeriBaru.*' => 'Gambar OPK tambahan',
        ];
    }

    public function simpan(): void
    {
        $this->tahun = trim($this->tahun);

        if (TeksKaya::kosong($this->deskripsi)) {
            $this->deskripsi = '';
        }

        $data = $this->validate();

        if ($this->jumlahGaleri() > self::MAKS_GALERI) {
            $this->addError('unggahGaleri', 'Galeri maksimal '.self::MAKS_GALERI.' foto. Hapus sebagian foto dulu.');

            return;
        }

        $atribut = [
            'nama' => trim($data['nama']),
            'lokasi_persebaran' => trim($data['lokasi_persebaran']),
            'persebaran' => trim((string) $data['persebaran']) ?: null,
            'jenis' => $data['jenis'],
            'tahun' => $data['tahun'] !== '' ? (int) $data['tahun'] : null,
            'pelaku_budaya' => trim((string) $data['pelaku_budaya']) ?: null,
            'kondisi' => $data['kondisi'],
            'deskripsi' => TeksKaya::html($data['deskripsi']),
        ];

        $fileBaru = [];
        $fileDibuang = [];

        try {
            if ($this->gambarBaru) {
                $atribut['gambar_utama'] = $fileBaru[] = $this->gambarBaru->store('opk', 'public');
            }

            $opk = DB::transaction(function () use ($atribut, &$fileBaru, &$fileDibuang) {
                if ($this->opkId) {
                    $opk = Opk::findOrFail($this->opkId);

                    if (isset($atribut['gambar_utama'])) {
                        $fileDibuang[] = $opk->gambar_utama;
                    }

                    $opk->update($atribut);
                } else {
                    $opk = Opk::create($atribut + ['dicatat_oleh' => auth()->id()]);
                }

                $this->simpanGaleri(GaleriOpk::class, 'id_opk', $opk->id_opk, 'opk/galeri', $fileBaru, $fileDibuang);

                return $opk;
            });
        } catch (\Throwable $e) {
            report($e);
            Storage::disk('public')->delete($fileBaru);
            $this->addError('simpan', 'Data OPK gagal disimpan. Silakan coba lagi.');

            return;
        }

        Storage::disk('public')->delete(array_filter($fileDibuang));

        $pesan = $this->opkId ? "OPK \"{$opk->nama}\" diperbarui." : "OPK \"{$opk->nama}\" ditambahkan.";

        $this->resetForm();
        $this->dispatch('opk-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.opk-form', [
            'maksGaleri' => self::MAKS_GALERI,
            'daftarJenis' => Opk::JENIS,
            'daftarKondisi' => Opk::KONDISI,
        ]);
    }
}
