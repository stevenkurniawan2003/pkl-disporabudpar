<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed default settings dari config/site.php (untuk backward compatibility).
     */
    public function run(): void
    {
        $pejabat = config('site.pejabat');

        // Data Kepala Dinas
        Setting::set('kepala_dinas_nama', $pejabat['kepala_dinas']['nama']);
        Setting::set('kepala_dinas_nip', $pejabat['kepala_dinas']['nip']);

        // Data Kabid Kebudayaan
        Setting::set('kabid_kebudayaan_nama', $pejabat['kabid_kebudayaan']['nama']);
        Setting::set('kabid_kebudayaan_nip', $pejabat['kabid_kebudayaan']['nip']);
    }
}
