<?php

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
        Route::get('/kesenian', Admin\Kesenian::class)->name('admin.kesenian');
        Route::get('/ekonomi-kreatif', Admin\Ekraf::class)->name('admin.ekonomi-kreatif');
        Route::get('/kunjungan-wisata', Admin\KunjunganWisata::class)->name('admin.kunjungan-wisata');
        Route::get('/kunjungan-hotel', Admin\KunjunganHotel::class)->name('admin.kunjungan-hotel');
    });
});

// PUBLIK: tanpa login. catat.kunjungan menghitung pengunjung situs (sekali per sesi per hari).
Route::middleware('catat.kunjungan')->prefix('publik')->name('publik.')->group(function () {
    Route::get('/beranda', Publik\Beranda::class)->name('beranda');

    // Destinasi
    Route::get('/peta-wisata', Publik\PetaWisata::class)->name('peta-wisata');
    Route::get('/wisata/{wisata:slug}', Publik\DetailWisata::class)->name('detail-wisata');
    Route::get('/hotel/{hotel}', Publik\DetailHotel::class)->name('detail-hotel');
    Route::get('/kunjungan-wisata', Publik\KunjunganWisata::class)->name('kunjungan-wisata');
    Route::get('/kunjungan-hotel', Publik\KunjunganHotel::class)->name('kunjungan-hotel');

    Route::get('/berita', Publik\Berita::class)->name('berita');
    Route::get('/berita/{berita:slug}', Publik\DetailBerita::class)->name('detail-berita');

    Route::get('/kebudayaan', Publik\Kebudayaan::class)->name('kebudayaan');
    Route::get('/kebudayaan/{kesenian}', Publik\DetailKesenian::class)->name('detail-kesenian');
    Route::get('/ekonomi-kreatif', Publik\EkonomiKreatif::class)->name('ekonomi-kreatif');
    Route::get('/ekonomi-kreatif/{ekraf}', Publik\DetailEkraf::class)->name('detail-ekraf');

    // Profil (statis): satu komponen, halaman ditentukan lewat defaults('halaman', ...).
    foreach (array_keys(Publik\Profil::HALAMAN) as $halaman) {
        Route::get('/profil/'.$halaman, Publik\Profil::class)->name('profil.'.$halaman)->defaults('halaman', $halaman);
    }
});
