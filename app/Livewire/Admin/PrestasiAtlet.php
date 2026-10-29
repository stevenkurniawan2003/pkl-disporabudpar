<?php

namespace App\Livewire\Admin;

use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\PrestasiAtlet as PrestasiModel;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Sorotan Prestasi > Data Atlet Berprestasi (dokumen konsep PPO poin 12 & 14): form input di atas tabel
 * (Nama, Jenis Medali, Cabor, Nomor Pertandingan), grafik jumlah atlet berprestasi tiap tahun, dan Extract.
 */
#[Layout('layouts.admin')]
#[Title('Data Atlet Berprestasi · Admin Disporabudpar Jember')]
class PrestasiAtlet extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: '')]
    public string $cabor = '';

    /** Baris yang sedang diubah lewat form di atas tabel. null = tambah baru. */
    public ?int $ubahId = null;

    public string $nama_atlet = '';

    public string $jenis_medali = '';

    public string $kode_cabor = '';

    public string $nomor_pertandingan = '';

    public string $nama_kejuaraan = '';

    public string $tahun = '';

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function mount(): void
    {
        $this->tahun = (string) now()->year;
    }

    public function updated(string $properti): void
    {
        if (in_array($properti, ['cari', 'cabor'], true)) {
            $this->resetPage();
        }
    }

    protected function rules(): array
    {
        return [
            'nama_atlet' => ['required', 'string', 'max:150'],
            'jenis_medali' => ['required', Rule::in(PrestasiModel::MEDALI)],
            'kode_cabor' => ['required', Rule::in(array_keys(Cabor::DAFTAR))],
            'nomor_pertandingan' => ['required', 'string', 'max:150'],
            'nama_kejuaraan' => ['nullable', 'string', 'max:200'],
            'tahun' => ['required', 'integer', 'between:1950,'.(now()->year + 1)],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'jenis_medali.required' => 'Jenis medali wajib dipilih.',
            'kode_cabor.required' => 'Cabor wajib dipilih.',
            'in' => 'Pilihan :attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'integer' => ':attribute harus berupa angka tahun.',
            'between' => ':attribute harus di antara :min dan :max.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama_atlet' => 'Nama',
            'jenis_medali' => 'Jenis medali',
            'kode_cabor' => 'Cabor',
            'nomor_pertandingan' => 'Nomor pertandingan',
            'nama_kejuaraan' => 'Kejuaraan',
            'tahun' => 'Tahun',
        ];
    }

    public function ubah(int $id): void
    {
        $p = PrestasiModel::find($id);

        if (! $p) {
            return;
        }

        $this->resetValidation();
        $this->ubahId = $p->id_prestasi;
        $this->nama_atlet = $p->nama_atlet;
        $this->jenis_medali = $p->jenis_medali;
        $this->kode_cabor = $p->kode_cabor;
        $this->nomor_pertandingan = $p->nomor_pertandingan;
        $this->nama_kejuaraan = (string) $p->nama_kejuaraan;
        $this->tahun = (string) $p->tahun;
    }

    public function batal(): void
    {
        $this->reset(['ubahId', 'nama_atlet', 'jenis_medali', 'kode_cabor', 'nomor_pertandingan', 'nama_kejuaraan']);
        $this->resetValidation();
        $this->tahun = (string) now()->year;
    }

    public function simpan(): void
    {
        $data = $this->validate();

        $atribut = [
            'nama_atlet' => trim($data['nama_atlet']),
            'jenis_medali' => $data['jenis_medali'],
            'kode_cabor' => $data['kode_cabor'],
            'nomor_pertandingan' => trim($data['nomor_pertandingan']),
            'nama_kejuaraan' => trim((string) $data['nama_kejuaraan']) ?: null,
            'tahun' => (int) $data['tahun'],
        ];

        try {
            $p = $this->ubahId ? tap(PrestasiModel::findOrFail($this->ubahId))->update($atribut) : PrestasiModel::create($atribut);
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan('Data prestasi gagal disimpan. Silakan coba lagi.', 'err');

            return;
        }

        $this->beriPesan($this->ubahId ? "Prestasi \"{$p->nama_atlet}\" diperbarui." : "Prestasi \"{$p->nama_atlet}\" ({$p->jenis_medali}) ditambahkan.");
        $this->batal();
    }

    public function hapus(int $id): void
    {
        $p = PrestasiModel::find($id);

        if (! $p) {
            $this->beriPesan('Data prestasi tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $p->delete();

        if ($this->ubahId === $id) {
            $this->batal();
        }

        $this->beriPesan("Prestasi \"{$p->nama_atlet}\" dihapus.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        $kata = trim($this->cari);

        $daftar = PrestasiModel::query()
            ->when(isset(Cabor::DAFTAR[$this->cabor]), fn ($q) => $q->where('kode_cabor', $this->cabor))
            ->when($kata !== '', fn ($q) => $q->where('nama_atlet', 'like', '%'.addcslashes($kata, '%_\\').'%'))
            ->orderByDesc('tahun')
            ->orderBy('nama_atlet')
            ->paginate(15);

        return view('livewire.admin.prestasi-atlet', [
            'daftar' => $daftar,
            'daftarCabor' => Cabor::pilihan(),
            'daftarMedali' => PrestasiModel::MEDALI,
            'daftarGrafik' => PrestasiModel::dataGrafik(),
            // Saran nama dari Database Atlet (boleh juga diketik bebas).
            'saranNama' => Atlet::orderBy('nama_lengkap')->limit(500)->pluck('nama_lengkap'),
        ]);
    }
}
