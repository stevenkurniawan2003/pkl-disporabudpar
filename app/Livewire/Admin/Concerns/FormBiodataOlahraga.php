<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\Cabor;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Field biodata yang sama pada form Tambah Atlet dan Tambah Pelatih (dokumen konsep PPO poin 4 & 7):
 * nama lengkap, tempat & tanggal lahir, umur otomatis, jenis kelamin, alamat, kecamatan, No. HP, cabor, status.
 * Tampilannya ada di partial admin/partials/biodata-olahraga.
 */
trait FormBiodataOlahraga
{
    public string $nama_lengkap = '';

    public string $tempat_lahir = '';

    public string $tanggal_lahir = '';

    public string $jenis_kelamin = '';

    public string $alamat = '';

    public string $kecamatan_id = '';

    public string $no_hp = '';

    public string $kode_cabor = '';

    public string $status = 'aktif';

    private function isiBiodata(Model $m): void
    {
        $this->nama_lengkap = $m->nama_lengkap;
        $this->tempat_lahir = $m->tempat_lahir;
        $this->tanggal_lahir = $m->tanggal_lahir->toDateString();
        $this->jenis_kelamin = $m->jenis_kelamin;
        $this->alamat = $m->alamat;
        $this->kecamatan_id = (string) $m->kecamatan_id;
        $this->no_hp = (string) $m->no_hp;
        $this->kode_cabor = $m->kode_cabor;
        $this->status = $m->status;
    }

    private function aturanBiodata(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'tempat_lahir' => ['required', 'string', 'max:80'],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d', 'before:today', 'after:1900-01-01'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat' => ['required', 'string', 'max:255'],
            'kecamatan_id' => ['required', 'integer', Rule::exists('kecamatan', 'id')],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'kode_cabor' => ['required', Rule::in(array_keys(Cabor::DAFTAR))],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    private function pesanBiodata(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'kode_cabor.required' => 'Cabang olahraga wajib dipilih.',
            'in' => 'Pilihan :attribute tidak valid.',
            'exists' => 'Pilihan :attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'date_format' => ':attribute tidak valid.',
            'before' => ':attribute harus sebelum hari ini.',
            'after' => ':attribute tidak valid.',
            'regex' => ':attribute hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
        ];
    }

    private function atributBiodata(): array
    {
        return [
            'nama_lengkap' => 'Nama lengkap',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'jenis_kelamin' => 'Jenis kelamin',
            'alamat' => 'Alamat',
            'kecamatan_id' => 'Kecamatan',
            'no_hp' => 'No. HP',
            'kode_cabor' => 'Cabang olahraga',
            'status' => 'Status keaktifan',
        ];
    }

    /** Kolom tabel dari data yang sudah divalidasi. */
    private function dataBiodata(array $data): array
    {
        return [
            'nama_lengkap' => trim($data['nama_lengkap']),
            'tempat_lahir' => trim($data['tempat_lahir']),
            'tanggal_lahir' => $data['tanggal_lahir'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'alamat' => trim($data['alamat']),
            'kecamatan_id' => (int) $data['kecamatan_id'],
            'no_hp' => trim((string) $data['no_hp']) ?: null,
            'kode_cabor' => $data['kode_cabor'],
            'status' => $data['status'],
        ];
    }

    /** Umur otomatis dari tanggal lahir, ditampilkan read-only di form. */
    private function umurPratinjau(): ?int
    {
        try {
            $lahir = Carbon::createFromFormat('Y-m-d', $this->tanggal_lahir);
        } catch (\Throwable) {
            return null;
        }

        return $lahir->isPast() ? $lahir->age : null;
    }

    /** Data untuk partial biodata-olahraga. */
    private function viewBiodata(): array
    {
        return [
            'daftarKecamatan' => $this->terbuka ? Kecamatan::orderBy('nama')->get(['id', 'nama']) : collect(),
            'daftarCabor' => Cabor::pilihan(),
            'umur' => $this->umurPratinjau(),
        ];
    }
}
