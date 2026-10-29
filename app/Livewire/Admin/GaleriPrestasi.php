<?php

namespace App\Livewire\Admin;

use App\Models\GaleriPrestasi as GaleriModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Sorotan Prestasi > Galeri Prestasi (dokumen konsep PPO poin 13): unggah dokumentasi foto/video prestasi olahraga.
 * Foto maks 2 MB; video MP4/WEBM maks 50 MB (batas unggahan sementara Livewire dinaikkan di config/livewire.php).
 */
#[Layout('layouts.admin')]
#[Title('Galeri Prestasi · Admin Disporabudpar Jember')]
class GaleriPrestasi extends Component
{
    use WithFileUploads;
    use WithPagination;

    public const MAKS_VIDEO_KB = 51200;

    public ?int $ubahId = null;

    public string $judul = '';

    public string $keterangan = '';

    public string $tahun = '';

    public $berkas = null;

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function mount(): void
    {
        $this->tahun = (string) now()->year;
    }

    /** Jenis berkas dari MIME: video/* -> video, selain itu foto. */
    private function jenisBerkas(): string
    {
        return str_starts_with((string) $this->berkas?->getMimeType(), 'video/') ? 'video' : 'foto';
    }

    protected function rules(): array
    {
        $aturanBerkas = $this->berkas && $this->jenisBerkas() === 'video'
            ? ['file', 'mimetypes:video/mp4,video/webm', 'max:'.self::MAKS_VIDEO_KB]
            : ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

        return [
            'judul' => ['required', 'string', 'max:200'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'tahun' => ['nullable', 'integer', 'between:1950,'.(now()->year + 1)],
            // Berkas wajib saat tambah; saat ubah hanya judul/keterangan/tahun yang diganti.
            'berkas' => [$this->ubahId ? 'nullable' : 'required', ...$aturanBerkas],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'berkas.required' => 'Pilih foto atau video yang akan diunggah.',
            'mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'mimetypes' => 'Video harus berformat MP4 atau WEBM.',
            'berkas.max' => 'Foto maksimal 2 MB, video maksimal 50 MB.',
            'max.string' => ':attribute maksimal :max karakter.',
            'integer' => ':attribute harus berupa angka tahun.',
            'between' => ':attribute harus di antara :min dan :max.',
        ];
    }

    protected function validationAttributes(): array
    {
        return ['judul' => 'Judul', 'keterangan' => 'Keterangan', 'tahun' => 'Tahun', 'berkas' => 'Berkas'];
    }

    public function updatedBerkas(): void
    {
        $this->validateOnly('berkas');
    }

    public function ubah(int $id): void
    {
        $g = GaleriModel::find($id);

        if (! $g) {
            return;
        }

        $this->resetValidation();
        $this->ubahId = $g->id_galeri_prestasi;
        $this->judul = $g->judul;
        $this->keterangan = (string) $g->keterangan;
        $this->tahun = (string) $g->tahun;
        $this->berkas = null;
    }

    public function batal(): void
    {
        $this->reset(['ubahId', 'judul', 'keterangan', 'berkas']);
        $this->resetValidation();
        $this->tahun = (string) now()->year;
    }

    public function simpan(): void
    {
        $data = $this->validate();

        $atribut = [
            'judul' => trim($data['judul']),
            'keterangan' => trim((string) $data['keterangan']) ?: null,
            'tahun' => $data['tahun'] !== '' && $data['tahun'] !== null ? (int) $data['tahun'] : null,
        ];

        try {
            if ($this->ubahId) {
                GaleriModel::findOrFail($this->ubahId)->update($atribut);
            } else {
                $jenis = $this->jenisBerkas();
                GaleriModel::create($atribut + ['jenis' => $jenis, 'file' => $this->berkas->store('prestasi/'.$jenis, 'public')]);
            }
        } catch (\Throwable $e) {
            report($e);
            $this->beriPesan('Dokumentasi gagal disimpan. Silakan coba lagi.', 'err');

            return;
        }

        $this->beriPesan($this->ubahId ? "\"{$atribut['judul']}\" diperbarui." : "\"{$atribut['judul']}\" diunggah ke galeri.");
        $this->batal();
        $this->resetPage();
    }

    public function hapus(int $id): void
    {
        $g = GaleriModel::find($id);

        if (! $g) {
            $this->beriPesan('Dokumentasi tidak ditemukan, mungkin sudah dihapus.', 'err');

            return;
        }

        $g->delete();
        Storage::disk('public')->delete($g->file);

        if ($this->ubahId === $id) {
            $this->batal();
        }

        $this->beriPesan("\"{$g->judul}\" dihapus dari galeri.");
    }

    private function beriPesan(string $pesan, string $jenis = 'ok'): void
    {
        $this->pesan = $pesan;
        $this->jenisPesan = $jenis;
    }

    public function render()
    {
        return view('livewire.admin.galeri-prestasi', [
            'daftar' => GaleriModel::latest('id_galeri_prestasi')->paginate(12),
        ]);
    }
}
