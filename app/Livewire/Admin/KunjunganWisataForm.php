<?php

namespace App\Livewire\Admin;

use App\Models\KunjunganWisata;
use App\Models\Wisata;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal tambah/ubah data kunjungan wisata. Ditempel di halaman Admin\KunjunganWisata
 * dan dibuka lewat event "buka-form-kunjungan-wisata".
 */
class KunjunganWisataForm extends Component
{
    public const PESAN_DUPLIKAT = 'Data kunjungan untuk wisata ini pada bulan dan tahun tersebut sudah ada.';

    /** Batas atas jumlah pengunjung per bulan, sesuai kolom INT UNSIGNED dan supaya salah ketik ketahuan. */
    private const MAKS_JUMLAH = 99999999;

    public bool $terbuka = false;

    public ?int $kunjunganId = null;

    public ?int $wisata_id = null;

    public ?int $bulan = null;

    public string $tahun = '';

    public string $domestik = '';

    public string $mancanegara = '';

    public string $keterangan = '';

    #[On('buka-form-kunjungan-wisata')]
    public function buka(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $k = KunjunganWisata::findOrFail($id);

            $this->kunjunganId = $k->id;
            $this->wisata_id = $k->wisata_id;
            $this->bulan = $k->bulan;
            $this->tahun = (string) $k->tahun;
            $this->domestik = (string) $k->domestik;
            $this->mancanegara = (string) $k->mancanegara;
            $this->keterangan = (string) $k->keterangan;
        } else {
            // Biasanya yang diinput data bulan lalu.
            $bulanLalu = now()->subMonthNoOverflow();
            $this->bulan = $bulanLalu->month;
            $this->tahun = (string) $bulanLalu->year;
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
            'wisata_id' => [
                'required',
                Rule::exists('wisata', 'id')->whereNull('deleted_at'),
                // Satu wisata hanya punya satu data per bulan per tahun.
                Rule::unique('kunjungan_wisata', 'wisata_id')
                    ->where('tahun', (int) $this->tahun)
                    ->where('bulan', (int) $this->bulan)
                    ->ignore($this->kunjunganId),
            ],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2000,'.now()->year],
            'domestik' => ['required', 'integer', 'min:0', 'max:'.self::MAKS_JUMLAH],
            'mancanegara' => ['required', 'integer', 'min:0', 'max:'.self::MAKS_JUMLAH],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute tidak boleh kurang dari :min.',
            'max.numeric' => ':attribute terlalu besar, periksa lagi angkanya.',
            'max.string' => ':attribute maksimal :max karakter.',
            'between' => ':attribute harus di antara :min dan :max.',
            'wisata_id.exists' => 'Wisata yang dipilih tidak ditemukan.',
            'wisata_id.unique' => self::PESAN_DUPLIKAT,
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'wisata_id' => 'Wisata',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
            'domestik' => 'Jumlah domestik',
            'mancanegara' => 'Jumlah mancanegara',
            'keterangan' => 'Keterangan',
        ];
    }

    public function simpan(): void
    {
        // "1.250" atau "1 250" -> 1250 (admin sering mengetik pemisah ribuan).
        $this->domestik = $this->rapikanAngka($this->domestik);
        $this->mancanegara = $this->rapikanAngka($this->mancanegara);

        $data = $this->validate();

        if ((int) $data['tahun'] === now()->year && (int) $data['bulan'] > now()->month) {
            $this->addError('bulan', 'Bulan tersebut belum berjalan, data kunjungannya belum bisa diisi.');

            return;
        }

        $atribut = [
            'wisata_id' => $data['wisata_id'],
            'bulan' => (int) $data['bulan'],
            'tahun' => (int) $data['tahun'],
            'domestik' => (int) $data['domestik'],
            'mancanegara' => (int) $data['mancanegara'],
            'keterangan' => trim((string) $data['keterangan']) ?: null,
        ];

        try {
            if ($this->kunjunganId) {
                $kunjungan = KunjunganWisata::findOrFail($this->kunjunganId);
                $kunjungan->update($atribut);
            } else {
                $atribut['dicatat_oleh'] = auth()->id();
                $kunjungan = KunjunganWisata::create($atribut);
            }
        } catch (UniqueConstraintViolationException) {
            // Data yang sama baru saja disimpan admin lain di antara validasi dan simpan.
            $this->addError('wisata_id', self::PESAN_DUPLIKAT);

            return;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('simpan', 'Data kunjungan gagal disimpan. Silakan coba lagi.');

            return;
        }

        $kunjungan->load('wisata');
        $label = "{$kunjungan->wisata?->nama} ({$kunjungan->nama_bulan} {$kunjungan->tahun})";
        $pesan = $this->kunjunganId ? "Data kunjungan {$label} diperbarui." : "Data kunjungan {$label} ditambahkan.";

        $this->resetForm();
        $this->dispatch('kunjungan-wisata-disimpan', pesan: $pesan);
    }

    private function rapikanAngka(string $angka): string
    {
        $angka = trim($angka);

        return preg_match('/^\d{1,3}([.\s]\d{3})+$/', $angka) ? preg_replace('/[.\s]/', '', $angka) : $angka;
    }

    public function render()
    {
        return view('livewire.admin.kunjungan-wisata-form', [
            'daftarWisata' => $this->terbuka ? Wisata::orderBy('nama')->get(['id', 'nama']) : collect(),
            'daftarBulan' => KunjunganWisata::BULAN,
        ]);
    }
}
