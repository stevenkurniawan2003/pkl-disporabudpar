<?php

namespace App\Livewire\Admin;

use App\Models\Kesenian;
use App\Models\PerpanjanganKik as PerpanjanganModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Perpanjangan Kartu Induk Kesenian: cari nomor induk dari data master, CEK, isi tanggal pengajuan,
 * tahun berakhir, dan foto ketua, lalu "Ajukan Perpanjangan" menghasilkan kartu PDF (lihat CetakKebudayaanController).
 */
#[Layout('layouts.admin')]
#[Title('Perpanjangan KIK · Admin Disporabudpar Jember')]
class PerpanjanganKik extends Component
{
    use WithFileUploads;
    use WithPagination;

    /** Pilihan masa berlaku kartu: 1 sampai 5 tahun setelah tanggal pengajuan. */
    public const MAKS_TAHUN_BERLAKU = 5;

    public string $nomorInduk = '';

    /** Kesenian yang sudah dipastikan lewat tombol CEK. */
    public ?int $kesenianId = null;

    public string $tanggal_pengajuan = '';

    public string $tahun_berakhir = '';

    public $fotoKetua = null;

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    /** Perpanjangan yang baru diajukan, untuk tombol unduh kartu. */
    public ?int $baruDiajukan = null;

    public function mount(): void
    {
        $this->isiBawaan();
    }

    private function isiBawaan(): void
    {
        $this->tanggal_pengajuan = now()->toDateString();
        $this->tahun_berakhir = (string) (now()->year + 2);
    }

    /** Mengetik ulang nomor induk membatalkan hasil CEK sebelumnya. */
    public function updatedNomorInduk(): void
    {
        $this->kesenianId = null;
        $this->resetValidation();
    }

    public function updatedTanggalPengajuan(): void
    {
        // Tahun berakhir harus tetap setelah tahun pengajuan.
        $tahun = $this->tahunPengajuan();

        if ($tahun && (int) $this->tahun_berakhir <= $tahun) {
            $this->tahun_berakhir = (string) ($tahun + 2);
        }
    }

    public function updatedFotoKetua(): void
    {
        $this->validateOnly('fotoKetua');
    }

    public function pilih(string $nomor): void
    {
        $this->nomorInduk = $nomor;
        $this->cek();
    }

    public function cek(): void
    {
        $this->resetValidation();
        $this->baruDiajukan = null;

        $nomor = trim($this->nomorInduk);
        $kesenian = $nomor !== '' ? Kesenian::where('nomor_induk', $nomor)->first() : null;

        if (! $kesenian) {
            $this->kesenianId = null;
            $this->addError('nomorInduk', 'Nomor induk tidak ditemukan. Kesenian yang belum terdaftar harus diinput di Data Master dulu untuk mendapat nomor induk.');

            return;
        }

        $this->kesenianId = $kesenian->id_kesenian;
        $this->nomorInduk = $kesenian->nomor_induk;
    }

    public function batal(): void
    {
        $this->reset(['nomorInduk', 'kesenianId', 'fotoKetua']);
        $this->resetValidation();
        $this->isiBawaan();
    }

    private function tahunPengajuan(): ?int
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $this->tanggal_pengajuan)->year;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function rules(): array
    {
        $tahun = $this->tahunPengajuan() ?? now()->year;

        return [
            'tanggal_pengajuan' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now()->addYear()->toDateString()],
            'tahun_berakhir' => ['required', 'integer', 'between:'.($tahun + 1).','.($tahun + self::MAKS_TAHUN_BERLAKU)],
            'fotoKetua' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'fotoKetua.required' => 'Foto diri ketua wajib diunggah.',
            'date_format' => ':attribute tidak valid.',
            'before_or_equal' => ':attribute terlalu jauh di masa depan.',
            'between' => ':attribute harus antara :min dan :max.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG atau PNG.',
            'max.file' => ':attribute maksimal 2 MB.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'tanggal_pengajuan' => 'Tanggal pengajuan',
            'tahun_berakhir' => 'Tahun berakhir',
            'fotoKetua' => 'Foto diri ketua',
        ];
    }

    public function ajukan(): void
    {
        $kesenian = $this->kesenianId ? Kesenian::find($this->kesenianId) : null;

        if (! $kesenian) {
            $this->addError('nomorInduk', 'Klik CEK untuk memastikan data kesenian dulu.');

            return;
        }

        $data = $this->validate();

        $mulai = Carbon::createFromFormat('Y-m-d', $data['tanggal_pengajuan'])->startOfDay();
        $foto = null;

        try {
            $foto = $this->fotoKetua->store('kesenian/ketua', 'public');

            $perpanjangan = PerpanjanganModel::create([
                'id_kesenian' => $kesenian->id_kesenian,
                'tanggal_pengajuan' => $mulai,
                'berlaku_sampai' => $mulai->copy()->setYear((int) $data['tahun_berakhir']),
                'foto_ketua' => $foto,
                'dicatat_oleh' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            if ($foto) {
                Storage::disk('public')->delete($foto);
            }

            $this->pesan = 'Perpanjangan gagal diajukan. Silakan coba lagi.';
            $this->jenisPesan = 'err';

            return;
        }

        $this->batal();
        $this->baruDiajukan = $perpanjangan->id_perpanjangan;
        $this->pesan = "Perpanjangan KIK \"{$kesenian->nama_kesenian}\" ({$kesenian->nomor_induk}) berhasil diajukan. Kartu siap diunduh.";
        $this->jenisPesan = 'ok';
        $this->resetPage();
    }

    public function render()
    {
        $kata = trim($this->nomorInduk);

        return view('livewire.admin.perpanjangan-kik', [
            'kesenian' => $this->kesenianId ? Kesenian::with(['kecamatan', 'desa', 'perpanjanganTerakhir'])->find($this->kesenianId) : null,
            // Saran saat mengetik: cocokkan nomor induk atau nama kesenian.
            'saran' => (! $this->kesenianId && mb_strlen($kata) >= 2)
                ? Kesenian::where(fn ($q) => $q->where('nomor_induk', 'like', addcslashes($kata, '%_\\').'%')->orWhere('nama_kesenian', 'like', '%'.addcslashes($kata, '%_\\').'%'))
                    ->orderBy('nomor_induk')->limit(8)->get(['id_kesenian', 'nomor_induk', 'nama_kesenian'])
                : collect(),
            'pilihanTahun' => range(($this->tahunPengajuan() ?? now()->year) + 1, ($this->tahunPengajuan() ?? now()->year) + self::MAKS_TAHUN_BERLAKU),
            'riwayat' => PerpanjanganModel::with('kesenian')->latest('id_perpanjangan')->paginate(10),
        ]);
    }
}
