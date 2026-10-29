<?php

namespace App\Livewire\Admin;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kesenian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Modal "Input Data Master" Kartu Induk Kesenian. Ditempel di Admin\Kesenian, dibuka lewat event "buka-form-kesenian".
 * Nomor induk dibuat otomatis dari kode jenis kesenian + nomor urut per jenis.
 */
class KesenianForm extends Component
{
    use WithFileUploads;

    private const ATURAN_FOTO = ['image', 'mimes:jpg,jpeg,png', 'max:2048'];

    public bool $terbuka = false;

    public ?int $kesenianId = null;

    /** Nomor induk tersimpan (saat ubah). Tetap dipakai selama jenis kesenian tidak diganti. */
    public ?string $nomorIndukLama = null;

    public ?string $kodeJenisLama = null;

    public string $kode_jenis = '';

    public string $nama_kesenian = '';

    public string $nama_pemilik = '';

    public string $alamat = '';

    public string $kabupaten = 'Jember';

    public string $kecamatan_id = '';

    public string $desa_id = '';

    public string $jumlah_anggota = '';

    public string $no_telepon = '';

    public string $status = 'aktif';

    public string $latitude = '';

    public string $longitude = '';

    public ?string $dokumentasiLama = null;

    public $dokumentasiBaru = null;

    public bool $hapusDokumentasi = false;

    #[On('buka-form-kesenian')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $k = Kesenian::findOrFail($id);

            $this->kesenianId = $k->id_kesenian;
            $this->nomorIndukLama = $k->nomor_induk;
            $this->kodeJenisLama = $k->kode_jenis;
            $this->kode_jenis = $k->kode_jenis;
            $this->nama_kesenian = $k->nama_kesenian;
            $this->nama_pemilik = $k->nama_pemilik;
            $this->alamat = $k->alamat;
            $this->kabupaten = $k->kabupaten;
            $this->kecamatan_id = (string) $k->kecamatan_id;
            $this->desa_id = (string) $k->desa_id;
            $this->jumlah_anggota = (string) $k->jumlah_anggota;
            $this->no_telepon = (string) $k->no_telepon;
            $this->status = $k->status;
            $this->latitude = (string) $k->latitude;
            $this->longitude = (string) $k->longitude;
            $this->dokumentasiLama = $k->dokumentasi;
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

    /** Ganti kecamatan -> pilihan desa ikut berganti, desa lama dikosongkan. */
    public function updatedKecamatanId(): void
    {
        $this->desa_id = '';
    }

    public function updatedDokumentasiBaru(): void
    {
        $this->validateOnly('dokumentasiBaru');
    }

    public function batalDokumentasiBaru(): void
    {
        $this->dokumentasiBaru = null;
    }

    protected function rules(): array
    {
        return [
            'kode_jenis' => ['required', Rule::in(array_keys(Kesenian::JENIS))],
            'nama_kesenian' => ['required', 'string', 'max:150'],
            'nama_pemilik' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:60'],
            'kecamatan_id' => ['required', 'integer', Rule::exists('kecamatan', 'id')],
            'desa_id' => ['required', 'integer', Rule::exists('desa', 'id')->where('kecamatan_id', (int) $this->kecamatan_id)],
            'jumlah_anggota' => ['required', 'integer', 'min:1', 'max:65000'],
            'no_telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'status' => ['required', Rule::in(array_keys(Kesenian::STATUS))],
            // Titik peta opsional, tapi kalau diisi harus berpasangan.
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'dokumentasiBaru' => ['nullable', ...self::ATURAN_FOTO],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'kode_jenis.required' => 'Jenis kesenian wajib dipilih.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'desa_id.required' => 'Desa/kelurahan wajib dipilih.',
            'desa_id.exists' => 'Desa/kelurahan tidak ada di kecamatan yang dipilih.',
            'in' => 'Pilihan :attribute tidak valid.',
            'required_with' => 'Latitude dan longitude harus diisi berpasangan.',
            'numeric' => ':attribute harus berupa angka (contoh -8.1724).',
            'between' => ':attribute harus di antara :min dan :max.',
            'exists' => 'Pilihan :attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file' => ':attribute maksimal 2 MB.',
            'integer' => ':attribute harus berupa angka.',
            'min.numeric' => ':attribute minimal :min.',
            'max.numeric' => ':attribute maksimal :max.',
            'regex' => ':attribute hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG atau PNG.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'kode_jenis' => 'Jenis kesenian',
            'nama_kesenian' => 'Nama kesenian',
            'nama_pemilik' => 'Nama pemilik',
            'alamat' => 'Alamat',
            'kabupaten' => 'Kabupaten',
            'kecamatan_id' => 'Kecamatan',
            'desa_id' => 'Desa/kelurahan',
            'jumlah_anggota' => 'Jumlah anggota',
            'no_telepon' => 'Nomor telepon',
            'status' => 'Status',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'dokumentasiBaru' => 'Dokumentasi kegiatan',
        ];
    }

    /** Nomor induk yang akan dipakai, untuk ditampilkan (read-only) di form. */
    private function pratinjauNomorInduk(): ?string
    {
        if (! isset(Kesenian::JENIS[$this->kode_jenis])) {
            return null;
        }

        if ($this->kesenianId && $this->kode_jenis === $this->kodeJenisLama) {
            return $this->nomorIndukLama;
        }

        return Kesenian::formatNomorInduk($this->kode_jenis, Kesenian::nomorUrutBerikutnya($this->kode_jenis));
    }

    public function simpan(): void
    {
        $this->latitude = str_replace(',', '.', trim($this->latitude));
        $this->longitude = str_replace(',', '.', trim($this->longitude));

        $data = $this->validate();

        $atribut = [
            'kode_jenis' => $data['kode_jenis'],
            'nama_kesenian' => trim($data['nama_kesenian']),
            'nama_pemilik' => trim($data['nama_pemilik']),
            'alamat' => trim($data['alamat']),
            'kabupaten' => trim($data['kabupaten']),
            'kecamatan_id' => (int) $data['kecamatan_id'],
            'desa_id' => (int) $data['desa_id'],
            'jumlah_anggota' => (int) $data['jumlah_anggota'],
            'no_telepon' => trim((string) $data['no_telepon']) ?: null,
            'status' => $data['status'],
            'latitude' => $data['latitude'] !== '' ? $data['latitude'] : null,
            'longitude' => $data['longitude'] !== '' ? $data['longitude'] : null,
        ];

        $fileBaru = null;
        $fileDibuang = null;

        try {
            if ($this->dokumentasiBaru) {
                $atribut['dokumentasi'] = $fileBaru = $this->dokumentasiBaru->store('kesenian', 'public');
            } elseif ($this->hapusDokumentasi) {
                $atribut['dokumentasi'] = null;
            }

            $kesenian = DB::transaction(function () use ($atribut, &$fileDibuang) {
                $lama = $this->kesenianId ? Kesenian::lockForUpdate()->findOrFail($this->kesenianId) : null;

                // Nomor induk baru hanya untuk data baru atau saat jenis kesenian diganti.
                if (! $lama || $lama->kode_jenis !== $atribut['kode_jenis']) {
                    // Kunci baris sejenis supaya dua admin yang menyimpan bersamaan tidak mendapat nomor yang sama.
                    $urut = (int) Kesenian::where('kode_jenis', $atribut['kode_jenis'])->lockForUpdate()->max('nomor_urut') + 1;
                    $atribut['nomor_urut'] = $urut;
                    $atribut['nomor_induk'] = Kesenian::formatNomorInduk($atribut['kode_jenis'], $urut);
                }

                if ($lama) {
                    if (array_key_exists('dokumentasi', $atribut) && $lama->dokumentasi) {
                        $fileDibuang = $lama->dokumentasi;
                    }

                    $lama->update($atribut);

                    return $lama;
                }

                return Kesenian::create($atribut);
            });
        } catch (\Throwable $e) {
            report($e);

            if ($fileBaru) {
                Storage::disk('public')->delete($fileBaru);
            }

            $this->addError('simpan', 'Data kesenian gagal disimpan. Silakan coba lagi.');

            return;
        }

        if ($fileDibuang) {
            Storage::disk('public')->delete($fileDibuang);
        }

        $pesan = $this->kesenianId
            ? "Kesenian \"{$kesenian->nama_kesenian}\" ({$kesenian->nomor_induk}) diperbarui."
            : "Kesenian \"{$kesenian->nama_kesenian}\" ditambahkan dengan nomor induk {$kesenian->nomor_induk}.";

        $this->resetForm();
        $this->dispatch('kesenian-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.kesenian-form', [
            'daftarJenis' => Kesenian::JENIS,
            'daftarStatus' => Kesenian::STATUS,
            'daftarKecamatan' => $this->terbuka ? Kecamatan::orderBy('nama')->get(['id', 'nama']) : collect(),
            'daftarDesa' => $this->kecamatan_id !== '' ? Desa::where('kecamatan_id', (int) $this->kecamatan_id)->orderBy('nama')->get(['id', 'nama']) : collect(),
            'nomorInduk' => $this->terbuka ? $this->pratinjauNomorInduk() : null,
        ]);
    }
}
