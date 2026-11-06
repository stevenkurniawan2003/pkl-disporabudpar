<?php

use App\Http\Controllers\Admin\CetakEkrafController;
use App\Http\Controllers\Admin\CetakKebudayaanController;
use App\Http\Controllers\Admin\EksporPpoController;
use App\Http\Controllers\Admin\SesiController;
use App\Livewire\Admin;
use App\Livewire\Publik;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/publik/beranda');

// ADMIN: semua alamat diawali /admin. Belum login -> otomatis ke /admin/login.
Route::prefix('admin')->group(function () {
    Route::redirect('/', '/admin/dashboard');

    Route::get('/login', Admin\Login::class)->middleware('guest')->name('admin.login');
    Route::post('/logout', [SesiController::class, 'keluar'])->middleware('auth')->name('admin.logout');

    Route::middleware(['auth', 'admin.aktif'])->group(function () {
        Route::get('/dashboard', Admin\Dashboard::class)->name('admin.dashboard');

        // Tambah, ubah, dan detail memakai modal di halaman daftar masing-masing.
        Route::get('/wisata', Admin\Wisata::class)->name('admin.wisata');
        Route::get('/hotel', Admin\Hotel::class)->name('admin.hotel');
        Route::get('/berita', Admin\Berita::class)->name('admin.berita');
        // Pariwisata: titik peta lain (restoran, oleh-oleh, lainnya), paket wisata, dan e-booklet.
        Route::get('/lokasi-peta', Admin\LokasiPeta::class)->name('admin.lokasi-peta');
        Route::get('/paket-wisata', Admin\PaketWisata::class)->name('admin.paket-wisata');
        Route::get('/ebooklet', Admin\Ebooklet::class)->name('admin.ebooklet');

        // Sistem EKRAF: data master pelaku ekonomi kreatif dan cetak.
        Route::get('/ekonomi-kreatif', Admin\Ekraf::class)->name('admin.ekonomi-kreatif');
        Route::get('/cetak-ekraf', Admin\CetakEkraf::class)->name('admin.cetak-ekraf');
        Route::get('/cetak-ekraf/pdf', CetakEkrafController::class)->name('admin.cetak-ekraf.pdf');

        // PPO (Peningkatan Prestasi Olahraga): atlet, pelatih, cabor, sorotan prestasi, dan Extract PDF/Excel.
        Route::get('/ppo/atlet', Admin\Atlet::class)->name('admin.ppo-atlet');
        Route::get('/ppo/pelatih', Admin\Pelatih::class)->name('admin.ppo-pelatih');
        Route::get('/ppo/cabor', Admin\Cabor::class)->name('admin.ppo-cabor');
        Route::get('/ppo/prestasi', Admin\PrestasiAtlet::class)->name('admin.ppo-prestasi');
        Route::get('/ppo/galeri', Admin\GaleriPrestasi::class)->name('admin.ppo-galeri');
        Route::get('/ppo/ekspor/{jenis}', EksporPpoController::class)->whereIn('jenis', EksporPpoController::JENIS)->name('admin.ppo.ekspor');

        // Kebudayaan: OPK, ODCB/CB, dan Kartu Induk Kesenian (data master, perpanjangan, cetak).
        Route::get('/opk', Admin\Opk::class)->name('admin.opk');
        Route::get('/cagar-budaya', Admin\CagarBudaya::class)->name('admin.cagar-budaya');
        Route::get('/kesenian', Admin\Kesenian::class)->name('admin.kesenian');
        Route::get('/perpanjangan-kik', Admin\PerpanjanganKik::class)->name('admin.perpanjangan-kik');
        Route::get('/perpanjangan-kik/{perpanjangan}/kartu', [CetakKebudayaanController::class, 'kartuKik'])->name('admin.kartu-kik');
        Route::get('/cetak-kesenian', Admin\CetakKesenian::class)->name('admin.cetak-kesenian');
        Route::get('/cetak-kesenian/pdf', [CetakKebudayaanController::class, 'dataKesenian'])->name('admin.cetak-kesenian.pdf');
        Route::get('/kunjungan-wisata', Admin\KunjunganWisata::class)->name('admin.kunjungan-wisata');
        Route::get('/kunjungan-hotel', Admin\KunjunganHotel::class)->name('admin.kunjungan-hotel');

        // Pengaturan: data pejabat untuk dokumen PDF.
        Route::get('/pengaturan/data-pejabat', Admin\Pengaturan\DataPejabat::class)->name('admin.pengaturan.data-pejabat');
    });
});

// PUBLIK: tanpa login. catat.kunjungan menghitung pengunjung situs (sekali per sesi per hari).
Route::middleware('catat.kunjungan')->prefix('publik')->name('publik.')->group(function () {
    Route::get('/beranda', Publik\Beranda::class)->name('beranda');

    // Destinasi
    Route::get('/peta-wisata', Publik\PetaWisata::class)->name('peta-wisata');
    Route::get('/wisata/{wisata:slug}', Publik\DetailWisata::class)->name('detail-wisata');
    Route::get('/hotel/{hotel}', Publik\DetailHotel::class)->name('detail-hotel');
    Route::get('/desa-wisata', Publik\DesaWisata::class)->name('desa-wisata');
    Route::get('/paket-wisata', Publik\PaketWisata::class)->name('paket-wisata');
    Route::get('/ebooklet', Publik\Ebooklet::class)->name('ebooklet');
    Route::get('/kunjungan-wisata', Publik\KunjunganWisata::class)->name('kunjungan-wisata');
    Route::get('/kunjungan-hotel', Publik\KunjunganHotel::class)->name('kunjungan-hotel');

    Route::get('/berita', Publik\Berita::class)->name('berita');
    Route::get('/berita/{berita:slug}', Publik\DetailBerita::class)->name('detail-berita');

    // Kebudayaan: dashboard grafik + daftar kesenian, OPK, dan cagar budaya.
    Route::get('/kebudayaan', Publik\Kebudayaan::class)->name('kebudayaan');
    Route::get('/kebudayaan/kesenian', Publik\DataKesenian::class)->name('data-kesenian');
    Route::get('/kebudayaan/opk', Publik\DaftarOpk::class)->name('opk');
    Route::get('/kebudayaan/opk/{opk}', Publik\DetailOpk::class)->name('detail-opk');
    Route::get('/kebudayaan/cagar-budaya', Publik\DaftarCagarBudaya::class)->name('cagar-budaya');
    Route::get('/kebudayaan/cagar-budaya/{cagarBudaya}', Publik\DetailCagarBudaya::class)->name('detail-cagar-budaya');
    // Ekonomi Kreatif: Data EKRAF Jember (grafik) dan pencarian data. "Sistem EKRAF" ada di admin.
    Route::get('/ekonomi-kreatif', Publik\EkonomiKreatif::class)->name('ekonomi-kreatif');
    Route::get('/ekonomi-kreatif/pencarian', Publik\PencarianEkraf::class)->name('pencarian-ekraf');

    // PPO: database atlet, pelatih, cabor, dan sorotan prestasi (read-only).
    Route::get('/ppo/atlet', Publik\PpoAtlet::class)->name('ppo-atlet');
    Route::get('/ppo/pelatih', Publik\PpoPelatih::class)->name('ppo-pelatih');
    Route::get('/ppo/cabor', Publik\PpoCabor::class)->name('ppo-cabor');
    Route::get('/ppo/sorotan-prestasi', Publik\SorotanPrestasi::class)->name('ppo-prestasi');

    // UPTD (statis, isi dari config/uptd.php).
    foreach (Publik\Uptd::HALAMAN as $halaman) {
        Route::get('/uptd/'.$halaman, Publik\Uptd::class)->name('uptd.'.$halaman)->defaults('halaman', $halaman);
    }

    // Profil (statis): satu komponen, halaman ditentukan lewat defaults('halaman', ...).
    foreach (array_keys(Publik\Profil::HALAMAN) as $halaman) {
        Route::get('/profil/'.$halaman, Publik\Profil::class)->name('profil.'.$halaman)->defaults('halaman', $halaman);
    }
});
