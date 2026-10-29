<?php

/*
| Isi footer situs publik. Ubah di sini (atau lewat .env) tanpa menyentuh tampilan.
| Ikon media sosial selalu tampil di footer; yang URL-nya kosong tampil tanpa tautan.
*/

return [

    'nama_dinas' => 'Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember',

    'deskripsi' => 'Melayani urusan kepemudaan, olahraga, kebudayaan, dan pariwisata untuk mewujudkan Jember yang berdaya saing dan berbudaya.',

    // ikon: nama ikon Bootstrap Icons (https://icons.getbootstrap.com), tanpa awalan "bi-".
    'media_sosial' => [
        ['nama' => 'Instagram', 'ikon' => 'instagram', 'url' => env('SITE_INSTAGRAM', '')],
        ['nama' => 'YouTube', 'ikon' => 'youtube', 'url' => env('SITE_YOUTUBE', '')],
        ['nama' => 'TikTok', 'ikon' => 'tiktok', 'url' => env('SITE_TIKTOK', '')],
        ['nama' => 'Facebook', 'ikon' => 'facebook', 'url' => env('SITE_FACEBOOK', '')],
    ],

    // Dibuka di tab baru. Periksa lagi alamatnya sebelum situs dipublikasikan.
    'tautan_cepat' => [
        ['nama' => 'Pemkab Jember', 'url' => env('SITE_URL_PEMKAB', 'https://jemberkab.go.id')],
        ['nama' => 'PPID Kabupaten Jember', 'url' => env('SITE_URL_PPID', 'https://ppid.jemberkab.go.id')],
        ['nama' => 'Diskominfo Jember', 'url' => env('SITE_URL_DISKOMINFO', 'https://diskominfo.jemberkab.go.id')],
        ['nama' => 'Lapor Bupati / Wadul Guse', 'url' => env('SITE_URL_WADUL_GUSE', 'https://wadulguse.jemberkab.go.id')],
    ],

    // "Hubungi Kami" di footer. Email dan telepon kosong tidak ditampilkan.
    'kontak' => [
        'alamat' => env('SITE_ALAMAT', 'Jl. MH. Thamrin, Ajung Kulon, Kec. Ajung, Kab. Jember'),
        'telepon' => env('SITE_TELEPON', '0331 335 244'),
        'email' => env('SITE_EMAIL', ''),
    ],

    // URL untuk iframe peta lokasi kantor. Bisa diganti dengan link "Sematkan peta" dari Google Maps.
    'peta_embed' => env(
        'SITE_PETA_EMBED',
        'https://www.google.com/maps?q='.rawurlencode('Disporabudpar Kabupaten Jember, Jl. MH. Thamrin, Ajung, Jember').'&output=embed'
    ),

    // Lama (detik) angka pengunjung di footer di-cache.
    'cache_pengunjung' => 300,

    // Kop surat & penanda tangan dokumen unduhan (Kartu Induk Kesenian, cetak data). Ganti lewat .env saat pejabat berganti.
    'kop' => [
        'pemerintah' => 'Pemerintah Kabupaten Jember',
        'dinas' => 'Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata',
        'alamat' => env('SITE_KOP_ALAMAT', 'Jl. MH. Thamrin, Ajung Kulon, Kec. Ajung, Kab. Jember (68175)'),
        'alamat_2' => env('SITE_KOP_ALAMAT_2', 'Kompleks Stadion Jember Sport Garden (JSG)'),
    ],
    'pejabat' => [
        'kepala_dinas' => [
            'jabatan' => 'Kepala Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember',
            'nama' => env('KEPALA_DINAS_NAMA', 'BOBBY ARIE SANDY, S.STP., M.M.'),
            'nip' => env('KEPALA_DINAS_NIP', '197703071996121001'),
        ],
        'kabid_kebudayaan' => [
            'jabatan' => 'Kepala Bidang Kebudayaan',
            'nama' => env('KABID_KEBUDAYAAN_NAMA', ''),
            'nip' => env('KABID_KEBUDAYAAN_NIP', ''),
        ],
    ],

];
