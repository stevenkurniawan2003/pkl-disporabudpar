<?php

namespace App\Livewire\Admin;

use App\Models\Desa;
use App\Models\Ekraf;
use App\Models\Kecamatan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal "Input Data Master" pelaku Ekonomi Kreatif. Ditempel di Admin\Ekraf, dibuka lewat event "buka-form-ekraf".
 * ID usaha/pelaku dibuat otomatis dari kode subsektor + nomor urut per subsektor.
 */
class EkrafForm extends Component
{
    /** Batas atas slider omzet. Omzet yang lebih besar tetap bisa diketik di kotak angka. */
    public const MAKS_SLIDER_OMZET = 50000000;

    public bool $terbuka = false;

    public ?int $ekrafId = null;

    /** ID tersimpan (saat ubah). Tetap dipakai selama subsektor tidak diganti. */
    public ?string $nomorLama = null;

    public ?string $kodeSubsektorLama = null;

    public string $kode_subsektor = '';

    public string $nama_usaha = '';

    public string $alamat = '';

    public string $kabupaten = 'Jember';

    public string $kecamatan_id = '';

    public string $desa_id = '';

    public int|string $omzet = 0;

    public string $no_telepon = '';

    public string $status = 'aktif';

    public string $latitude = '';

    public string $longitude = '';

    #[On('buka-form-ekraf')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $e = Ekraf::findOrFail($id);

            $this->ekrafId = $e->id_ekraf;
            $this->nomorLama = $e->nomor_ekraf;
            $this->kodeSubsektorLama = $e->kode_subsektor;
            $this->kode_subsektor = $e->kode_subsektor;
            $this->nama_usaha = $e->nama_usaha;
            $this->alamat = $e->alamat;
            $this->kabupaten = $e->kabupaten;
            $this->kecamatan_id = (string) $e->kecamatan_id;
            $this->desa_id = (string) $e->desa_id;
            $this->omzet = $e->omzet;
            $this->no_telepon = (string) $e->no_telepon;
            $this->status = $e->status;
            $this->latitude = (string) $e->latitude;
            $this->longitude = (string) $e->longitude;
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

    protected function rules(): array
    {
        return [
            'kode_subsektor' => ['required', Rule::in(array_keys(Ekraf::SUBSEKTOR))],
            'nama_usaha' => ['required', 'string', 'max:200'],
            'alamat' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:60'],
            'kecamatan_id' => ['required', 'integer', Rule::exists('kecamatan', 'id')],
            'desa_id' => ['required', 'integer', Rule::exists('desa', 'id')->where('kecamatan_id', (int) $this->kecamatan_id)],
            'omzet' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'no_telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'status' => ['required', Rule::in(array_keys(Ekraf::STATUS))],
            // Titik peta opsional, tapi kalau diisi harus berpasangan.
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'kode_subsektor.required' => 'Subsektor wajib dipilih.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'desa_id.required' => 'Desa/kelurahan wajib dipilih.',
            'desa_id.exists' => 'Desa/kelurahan tidak ada di kecamatan yang dipilih.',
            'in' => 'Pilihan :attribute tidak valid.',
            'required_with' => 'Latitude dan longitude harus diisi berpasangan.',
            'numeric' => ':attribute harus berupa angka (contoh -8.1724).',
            'between' => ':attribute harus di antara :min dan :max.',
            'exists' => 'Pilihan :attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'integer' => ':attribute harus berupa angka bulat (tanpa titik atau koma).',
            'min.numeric' => ':attribute tidak boleh negatif.',
            'max.numeric' => ':attribute terlalu besar.',
            'regex' => ':attribute hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'kode_subsektor' => 'Subsektor',
            'nama_usaha' => 'Nama usaha/pelaku',
            'alamat' => 'Alamat',
            'kabupaten' => 'Kabupaten',
            'kecamatan_id' => 'Kecamatan',
            'desa_id' => 'Desa/kelurahan',
            'omzet' => 'Omzet',
            'no_telepon' => 'Telp',
            'status' => 'Status',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
        ];
    }

    /** ID usaha/pelaku yang akan dipakai, untuk ditampilkan (read-only) di form. */
    private function pratinjauNomor(): ?string
    {
        if (! isset(Ekraf::SUBSEKTOR[$this->kode_subsektor])) {
            return null;
        }

        if ($this->ekrafId && $this->kode_subsektor === $this->kodeSubsektorLama) {
            return $this->nomorLama;
        }

        return Ekraf::formatNomor($this->kode_subsektor, Ekraf::nomorUrutBerikutnya($this->kode_subsektor));
    }

    public function simpan(): void
    {
        // Omzet boleh diketik "8.000.000" atau "Rp 8.000.000": ambil angkanya saja.
        if (is_string($this->omzet)) {
            $angka = preg_replace('/[^0-9]/', '', $this->omzet);
            $this->omzet = $angka === '' ? '' : $angka;
        }

        $this->latitude = str_replace(',', '.', trim($this->latitude));
        $this->longitude = str_replace(',', '.', trim($this->longitude));

        $data = $this->validate();

        $atribut = [
            'kode_subsektor' => $data['kode_subsektor'],
            'nama_usaha' => trim($data['nama_usaha']),
            'alamat' => trim($data['alamat']),
            'kabupaten' => trim($data['kabupaten']),
            'kecamatan_id' => (int) $data['kecamatan_id'],
            'desa_id' => (int) $data['desa_id'],
            'omzet' => (int) $data['omzet'],
            'no_telepon' => trim((string) $data['no_telepon']) ?: null,
            'status' => $data['status'],
            'latitude' => $data['latitude'] !== '' ? $data['latitude'] : null,
            'longitude' => $data['longitude'] !== '' ? $data['longitude'] : null,
        ];

        try {
            $ekraf = DB::transaction(function () use ($atribut) {
                $lama = $this->ekrafId ? Ekraf::lockForUpdate()->findOrFail($this->ekrafId) : null;

                // ID baru hanya untuk data baru atau saat subsektor diganti.
                if (! $lama || $lama->kode_subsektor !== $atribut['kode_subsektor']) {
                    // Kunci baris sesubsektor supaya dua admin yang menyimpan bersamaan tidak mendapat nomor yang sama.
                    $urut = (int) Ekraf::where('kode_subsektor', $atribut['kode_subsektor'])->lockForUpdate()->max('nomor_urut') + 1;
                    $atribut['nomor_urut'] = $urut;
                    $atribut['nomor_ekraf'] = Ekraf::formatNomor($atribut['kode_subsektor'], $urut);
                }

                if ($lama) {
                    $lama->update($atribut);

                    return $lama;
                }

                return Ekraf::create($atribut);
            });
        } catch (\Throwable $e) {
            report($e);
            $this->addError('simpan', 'Data pelaku ekraf gagal disimpan. Silakan coba lagi.');

            return;
        }

        $pesan = $this->ekrafId
            ? "Data \"{$ekraf->nama_usaha}\" ({$ekraf->nomor_ekraf}) diperbarui."
            : "Data \"{$ekraf->nama_usaha}\" ditambahkan dengan ID {$ekraf->nomor_ekraf}.";

        $this->resetForm();
        $this->dispatch('ekraf-disimpan', pesan: $pesan);
    }

    public function render()
    {
        return view('livewire.admin.ekraf-form', [
            'daftarSubsektor' => Ekraf::SUBSEKTOR,
            'daftarStatus' => Ekraf::STATUS,
            'daftarKecamatan' => $this->terbuka ? Kecamatan::orderBy('nama')->get(['id', 'nama']) : collect(),
            'daftarDesa' => $this->kecamatan_id !== '' ? Desa::where('kecamatan_id', (int) $this->kecamatan_id)->orderBy('nama')->get(['id', 'nama']) : collect(),
            'nomor' => $this->terbuka ? $this->pratinjauNomor() : null,
            'maksSlider' => self::MAKS_SLIDER_OMZET,
        ]);
    }
}
