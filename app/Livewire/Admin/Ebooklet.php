<?php

namespace App\Livewire\Admin;

use App\Models\Ebooklet as EbookletModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/** E-Booklet wisata Jember: unggah PDF (maks 20 MB) + sampul opsional. Form langsung di atas daftar. */
#[Layout('layouts.admin')]
#[Title('E-Booklet · Admin Disporabudpar Jember')]
class Ebooklet extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $judul = '';

    public string $tahun = '';

    public string $deskripsi = '';

    public $pdf = null;

    public $sampul = null;

    public ?string $pesan = null;

    public string $jenisPesan = 'ok';

    public function mount(): void
    {
        $this->tahun = (string) now()->year;
    }

    protected function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'tahun' => ['required', 'integer', 'between:2000,'.(now()->year + 1)],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'sampul' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'pdf.required' => 'Berkas PDF e-booklet wajib diunggah.',
            'pdf.mimes' => 'E-booklet harus berformat PDF.',
            'pdf.max' => 'E-booklet maksimal 20 MB.',
            'sampul.max' => 'Sampul maksimal 2 MB.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPG, PNG, atau WEBP.',
            'max.string' => ':attribute maksimal :max karakter.',
            'integer' => ':attribute harus berupa angka tahun.',
            'between' => ':attribute harus di antara :min dan :max.',
        ];
    }

    protected function validationAttributes(): array
    {
        return ['judul' => 'Judul', 'tahun' => 'Tahun', 'deskripsi' => 'Deskripsi', 'pdf' => 'Berkas PDF', 'sampul' => 'Sampul'];
    }

    public function simpan(): void
    {
        $data = $this->validate();
        $file = [];

        try {
            $file[] = $pdf = $this->pdf->store('ebooklet', 'public');
            $sampul = $this->sampul ? ($file[] = $this->sampul->store('ebooklet/sampul', 'public')) : null;

            EbookletModel::create([
                'judul' => trim($data['judul']),
                'tahun' => (int) $data['tahun'],
                'deskripsi' => trim((string) $data['deskripsi']) ?: null,
                'file_pdf' => $pdf,
                'sampul' => $sampul,
            ]);
        } catch (\Throwable $e) {
            report($e);
            Storage::disk('public')->delete($file);
            $this->pesan = 'E-booklet gagal diunggah. Silakan coba lagi.';
            $this->jenisPesan = 'err';

            return;
        }

        $this->pesan = "E-booklet \"{$data['judul']}\" diunggah.";
        $this->jenisPesan = 'ok';
        $this->reset(['judul', 'deskripsi', 'pdf', 'sampul']);
        $this->resetPage();
    }

    public function hapus(int $id): void
    {
        $e = EbookletModel::find($id);

        if (! $e) {
            return;
        }

        $e->delete();
        Storage::disk('public')->delete(array_filter([$e->file_pdf, $e->sampul]));

        $this->pesan = "E-booklet \"{$e->judul}\" dihapus.";
        $this->jenisPesan = 'ok';
    }

    public function render()
    {
        return view('livewire.admin.ebooklet', [
            'daftar' => EbookletModel::orderByDesc('tahun')->latest('id_ebooklet')->paginate(10),
        ]);
    }
}
