<?php

namespace App\Livewire\Admin;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kesenian;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Menu Cetak Kartu Induk Kesenian: saring data master per kecamatan, desa, jenis, status, dan tahun,
 * lalu unduh/cetak PDF bertanda tangan Kepala Bidang Kebudayaan (CetakKebudayaanController::dataKesenian).
 */
#[Layout('layouts.admin')]
#[Title('Cetak Data Kesenian · Admin Disporabudpar Jember')]
class CetakKesenian extends Component
{
    #[Url(except: '')]
    public string $kecamatan = '';

    #[Url(except: '')]
    public string $desa = '';

    #[Url(except: '')]
    public string $jenis = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $tahun = '';

    public function updatedKecamatan(): void
    {
        $this->desa = '';
    }

    public function resetFilter(): void
    {
        $this->reset();
    }

    /** @return array<string, string> filter yang terisi saja, untuk query string tautan PDF */
    private function filter(): array
    {
        return array_filter([
            'kecamatan' => $this->kecamatan,
            'desa' => $this->desa,
            'jenis' => isset(Kesenian::JENIS[$this->jenis]) ? $this->jenis : '',
            'status' => isset(Kesenian::STATUS[$this->status]) ? $this->status : '',
            'tahun' => $this->tahun,
        ], fn ($v) => $v !== '');
    }

    public function render()
    {
        $filter = $this->filter();

        return view('livewire.admin.cetak-kesenian', [
            'filter' => $filter,
            'daftarKecamatan' => Kecamatan::orderBy('nama')->get(['id', 'nama']),
            'daftarDesa' => $this->kecamatan !== '' ? Desa::where('kecamatan_id', (int) $this->kecamatan)->orderBy('nama')->get(['id', 'nama']) : collect(),
            'daftarJenis' => Kesenian::JENIS,
            'daftarStatus' => Kesenian::STATUS,
            'daftarTahun' => Kesenian::query()->selectRaw('DISTINCT YEAR(created_at) AS tahun')->orderByDesc('tahun')->pluck('tahun')
                ->push(now()->year)->unique()->sortDesc()->values(),
            'daftar' => Kesenian::with(['kecamatan', 'desa'])->filterCetak($filter)->orderBy('kode_jenis')->orderBy('nomor_urut')->limit(500)->get(),
            'jumlah' => Kesenian::filterCetak($filter)->count(),
        ]);
    }
}
