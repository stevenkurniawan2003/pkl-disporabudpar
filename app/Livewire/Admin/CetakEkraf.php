<?php

namespace App\Livewire\Admin;

use App\Models\Desa;
use App\Models\Ekraf;
use App\Models\Kecamatan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Menu CETAK EKRAF: saring data master per kecamatan, desa, subsektor, dan status,
 * lalu unduh/cetak PDF bertanda tangan Kepala Dinas (CetakEkrafController).
 */
#[Layout('layouts.admin')]
#[Title('Cetak Data EKRAF · Admin Disporabudpar Jember')]
class CetakEkraf extends Component
{
    #[Url(except: '')]
    public string $kecamatan = '';

    #[Url(except: '')]
    public string $desa = '';

    #[Url(except: '')]
    public string $subsektor = '';

    #[Url(except: '')]
    public string $status = '';

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
            'subsektor' => isset(Ekraf::SUBSEKTOR[$this->subsektor]) ? $this->subsektor : '',
            'status' => isset(Ekraf::STATUS[$this->status]) ? $this->status : '',
        ], fn ($v) => $v !== '');
    }

    public function render()
    {
        $filter = $this->filter();

        return view('livewire.admin.cetak-ekraf', [
            'filter' => $filter,
            'daftarKecamatan' => Kecamatan::orderBy('nama')->get(['id', 'nama']),
            'daftarDesa' => $this->kecamatan !== '' ? Desa::where('kecamatan_id', (int) $this->kecamatan)->orderBy('nama')->get(['id', 'nama']) : collect(),
            'daftarSubsektor' => Ekraf::SUBSEKTOR,
            'daftarStatus' => Ekraf::STATUS,
            'daftar' => Ekraf::with(['kecamatan', 'desa'])->filter($filter)->orderBy('kode_subsektor')->orderBy('nomor_urut')->limit(500)->get(),
            'jumlah' => Ekraf::filter($filter)->count(),
        ]);
    }
}
