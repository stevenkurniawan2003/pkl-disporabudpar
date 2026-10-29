<?php

namespace App\Livewire\Admin;

use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Kecamatan;
use App\Models\Pelatih;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal "Tambah Cabor" / ubah cabor. Ditempel di Admin\Cabor, dibuka lewat event "buka-form-cabor".
 * Induk organisasi mengikuti cabor yang dipilih; jumlah atlet & pelatih aktif dihitung dari Database Atlet/Pelatih.
 */
class CaborForm extends Component
{
    public bool $terbuka = false;

    public ?int $caborId = null;

    public string $kode_cabor = '';

    public string $ketua_pengkab = '';

    public string $alamat_sekretariat = '';

    public string $kecamatan_id = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $prestasi_terakhir = '';

    public string $kontak = '';

    public string $status = 'aktif';

    #[On('buka-form-cabor')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $c = Cabor::findOrFail($id);

            $this->caborId = $c->id_cabor;
            $this->kode_cabor = $c->kode_cabor;
            $this->ketua_pengkab = $c->ketua_pengkab;
            $this->alamat_sekretariat = $c->alamat_sekretariat;
            $this->kecamatan_id = (string) $c->kecamatan_id;
            $this->latitude = (string) $c->latitude;
            $this->longitude = (string) $c->longitude;
            $this->prestasi_terakhir = (string) $c->prestasi_terakhir;
            $this->kontak = (string) $c->kontak;
            $this->status = $c->status;
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
            'kode_cabor' => ['required', Rule::in(array_keys(Cabor::DAFTAR)), Rule::unique('cabor', 'kode_cabor')->ignore($this->caborId, 'id_cabor')],
            'ketua_pengkab' => ['required', 'string', 'max:150'],
            'alamat_sekretariat' => ['required', 'string', 'max:255'],
            'kecamatan_id' => ['required', 'integer', Rule::exists('kecamatan', 'id')],
            // Titik sekretariat opsional, tapi kalau diisi harus berpasangan.
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'prestasi_terakhir' => ['nullable', 'string', 'max:1000'],
            'kontak' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(Cabor::STATUS))],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'kode_cabor.required' => 'Cabang olahraga wajib dipilih.',
            'kode_cabor.unique' => 'Data cabor ini sudah ada. Ubah data yang sudah ada di daftar.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'required_with' => 'Latitude dan longitude harus diisi berpasangan.',
            'in' => 'Pilihan :attribute tidak valid.',
            'exists' => 'Pilihan :attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'numeric' => ':attribute harus berupa angka (contoh -8.1724).',
            'between' => ':attribute harus di antara :min dan :max.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'kode_cabor' => 'Nama cabang olahraga',
            'ketua_pengkab' => 'Ketua Pengkab',
            'alamat_sekretariat' => 'Alamat sekretariat',
            'kecamatan_id' => 'Kecamatan',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'prestasi_terakhir' => 'Prestasi terakhir',
            'kontak' => 'Info kontak cabor',
            'status' => 'Status keaktifan',
        ];
    }

    public function simpan(): void
    {
        $this->latitude = str_replace(',', '.', trim($this->latitude));
        $this->longitude = str_replace(',', '.', trim($this->longitude));

        $data = $this->validate();

        $atribut = [
            'kode_cabor' => $data['kode_cabor'],
            'ketua_pengkab' => trim($data['ketua_pengkab']),
            'alamat_sekretariat' => trim($data['alamat_sekretariat']),
            'kecamatan_id' => (int) $data['kecamatan_id'],
            'latitude' => $data['latitude'] !== '' ? $data['latitude'] : null,
            'longitude' => $data['longitude'] !== '' ? $data['longitude'] : null,
            'prestasi_terakhir' => trim((string) $data['prestasi_terakhir']) ?: null,
            'kontak' => trim((string) $data['kontak']) ?: null,
            'status' => $data['status'],
        ];

        try {
            $cabor = $this->caborId ? tap(Cabor::findOrFail($this->caborId))->update($atribut) : Cabor::create($atribut);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('simpan', 'Data cabor gagal disimpan. Silakan coba lagi.');

            return;
        }

        $pesan = $this->caborId ? "Cabor \"{$cabor->nama_cabor}\" diperbarui." : "Cabor \"{$cabor->nama_cabor}\" ditambahkan.";

        $this->resetForm();
        $this->dispatch('cabor-disimpan', pesan: $pesan);
    }

    public function render()
    {
        $terpilih = isset(Cabor::DAFTAR[$this->kode_cabor]) ? $this->kode_cabor : null;

        return view('livewire.admin.cabor-form', [
            'daftarCabor' => array_map(fn ($c) => $c[0], Cabor::DAFTAR),
            'daftarKecamatan' => $this->terbuka ? Kecamatan::orderBy('nama')->get(['id', 'nama']) : collect(),
            'induk' => $terpilih ? Cabor::DAFTAR[$terpilih][1].' ('.Cabor::DAFTAR[$terpilih][2].')' : '',
            'jumlahAtlet' => $terpilih ? Atlet::aktif()->where('kode_cabor', $terpilih)->count() : null,
            'jumlahPelatih' => $terpilih ? Pelatih::aktif()->where('kode_cabor', $terpilih)->count() : null,
        ]);
    }
}
