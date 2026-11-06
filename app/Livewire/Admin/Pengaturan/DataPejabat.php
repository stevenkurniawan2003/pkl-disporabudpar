<?php

namespace App\Livewire\Admin\Pengaturan;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Menu Pengaturan Data Pejabat: edit nama dan NIP pejabat untuk dokumen PDF.
 * Data disimpan di database, tidak perlu edit .env setiap kali pejabat berganti.
 */
#[Layout('layouts.admin')]
#[Title('Pengaturan Data Pejabat · Admin Disporabudpar Jember')]
class DataPejabat extends Component
{
    public string $kepalaDinasNama = '';

    public string $kepalaDinasNip = '';

    public string $kabidKebudayaanNama = '';

    public string $kabidKebudayaanNip = '';

    public function mount(): void
    {
        $pejabat = Setting::getPejabat();

        $this->kepalaDinasNama = $pejabat['kepala_dinas']['nama'];
        $this->kepalaDinasNip = $pejabat['kepala_dinas']['nip'];
        $this->kabidKebudayaanNama = $pejabat['kabid_kebudayaan']['nama'];
        $this->kabidKebudayaanNip = $pejabat['kabid_kebudayaan']['nip'];
    }

    public function simpan(): void
    {
        $this->validate([
            'kepalaDinasNama' => ['required', 'string', 'max:255'],
            'kepalaDinasNip' => ['required', 'string', 'max:50'],
            'kabidKebudayaanNama' => ['required', 'string', 'max:255'],
            'kabidKebudayaanNip' => ['required', 'string', 'max:50'],
        ], [
            'kepalaDinasNama.required' => 'Nama Kepala Dinas wajib diisi',
            'kepalaDinasNip.required' => 'NIP Kepala Dinas wajib diisi',
            'kabidKebudayaanNama.required' => 'Nama Kabid Kebudayaan wajib diisi',
            'kabidKebudayaanNip.required' => 'NIP Kabid Kebudayaan wajib diisi',
        ]);

        Setting::set('kepala_dinas_nama', $this->kepalaDinasNama);
        Setting::set('kepala_dinas_nip', $this->kepalaDinasNip);
        Setting::set('kabid_kebudayaan_nama', $this->kabidKebudayaanNama);
        Setting::set('kabid_kebudayaan_nip', $this->kabidKebudayaanNip);

        session()->flash('sukses', 'Data pejabat berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.admin.pengaturan.data-pejabat');
    }
}
