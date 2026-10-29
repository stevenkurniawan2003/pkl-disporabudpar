<?php

/*
| Isi halaman publik UPTD (dari docs/dokumen/UPTD-Olahraga Jember.pdf dan UPTD-Wisata-Terpadu-Jember.pdf).
| Halaman statis: ubah teks di sini tanpa menyentuh tampilan. Foto sarana olahraga ada di public/images/uptd/.
*/

return [

    'olahraga' => [
        'judul' => 'UPTD Sarana dan Prasarana Olahraga',
        'ringkas' => 'Mengelola sarana dan prasarana olahraga di Kabupaten Jember, Jawa Timur.',

        // [nama, foto (public/images/uptd/...) atau null]
        'sarana' => [
            ['Stadion Jember Sport Garden', 'stadion-jsg.jpg'],
            ['Stadion Notohadinegoro', 'stadion-notohadinegoro.jpg'],
            ['GOR PKPSO', 'gor-pkpso.jpg'],
            ['Lapangan Tenis', 'lapangan-tenis.jpg'],
            ['Lapangan Alun-Alun Rambipuji', 'lapangan-rambipuji.jpg'],
            ['Lapangan Alun-Alun Bangsalsari', 'lapangan-bangsalsari.jpg'],
            ['Stadion Tanggul', 'stadion-tanggul.jpg'],
            ['Lapangan Balung', 'lapangan-balung.jpg'],
            ['Stadion Glory Ambulu', 'stadion-glory-ambulu.jpg'],
            ['GOR Argopuro', null],
        ],

        // SOP peminjaman & pemanfaatan sarana: [uraian, pelaksana, kelengkapan, waktu, output, keterangan]
        'sop' => [
            ['Menerima surat/proposal permohonan pemakaian venue, mencatat nomor, tanggal, dan perihal pada Buku Agenda Surat Masuk, memberikan Lembar Disposisi, serta mengantar surat permohonan ke Kepala Dinas.', ['Staf Bagian Umum'], ['Surat/Proposal Permohonan', 'Buku Agenda Surat Masuk', 'Lembar Disposisi'], '30 menit', 'Surat/proposal telah dicatat di buku agenda surat masuk dinas', 'Petugas penerima surat meminta nomor kontak calon peminjam venue.'],
            ['Memberikan disposisi surat/proposal yang berisi arahan/instruksi kepada Kepala UPT Pengelola Sarana Olahraga.', ['Kepala Dinas'], ['Lembar Disposisi', 'Surat/Proposal Permohonan'], '30 menit', 'Arahan/instruksi telaah staf berisi poin-poin teknis dan nilai retribusi', 'Lembar disposisi dan surat masuk difotokopi 2 rangkap.'],
            ['Menurunkan disposisi ke Bagian Umum untuk pengarsipan arahan/instruksi dan meneruskan disposisi ke Kepala UPT Pengelola Sarana Olahraga.', ['Staf Bagian Umum'], ['Lembar Disposisi', 'Surat/Proposal Permohonan'], '30 menit', 'Tersedianya arsip surat masuk', 'Lembar disposisi dan surat masuk difotokopi 2 rangkap.'],
            ['Perhitungan retribusi pemakaian dan menghubungi calon pemakai untuk persetujuan nilai retribusi. Jika tidak setuju, proses tidak dilanjutkan dan berkas diarsipkan.', ['Kepala UPTD', 'Staf UPTD'], ['Perda Nomor 1 Tahun 2024'], '60 menit', 'Tersedianya rincian jumlah tarif retribusi', 'Kepala/Staf UPTD menghubungi calon peminjam.'],
            ['Calon peminjam setuju dengan nilai retribusi; proses dilanjutkan dengan meminta fotokopi KTP penanggung jawab kegiatan.', ['Kepala UPTD', 'Staf UPTD'], ['Fotokopi KTP Pemohon'], '30 menit', 'Kelengkapan dokumen', 'Kepala/Staf UPTD menghubungi calon peminjam.'],
            ['Membuat Surat Izin Pemakaian dan melakukan registrasi ke dalam jadwal pemakaian.', ['Staf UPTD'], ['Surat Izin Pemakaian Tempat', 'Buku Jadwal Registrasi Pemakaian Tempat'], '60 menit', 'Tersedianya jadwal pemakaian venue oleh peminjam', 'Staf UPTD memasukkan kegiatan ke jadwal pemakaian venue.'],
            ['Menyusun seluruh kelengkapan administrasi pemakaian venue, memberikan post-it pada tanda tangan Kadis, dan meminta paraf pejabat terkait.', ['Staf UPTD'], ['Naskah Surat Izin Pemakaian Tempat yang sudah diberi post-it'], '30 menit', 'Tersedianya dokumen administrasi yang telah diparaf', 'Staf UPTD memberi post-it dan meminta paraf.'],
            ['Menaikkan seluruh kelengkapan administrasi pemakaian venue kepada Kadis untuk ditandatangani.', ['Kepala Dinas', 'Staf UPTD'], ['Dokumen administrasi pemakaian venue'], '240 menit', 'Dokumen administrasi pemakaian venue', 'Staf UPTD memasukkan dokumen ke Kadis.'],
            ['Calon peminjam menerima surat izin pemakaian tempat sebagai dokumen kelengkapan permohonan perizinan kegiatan di Polsek/Polres.', ['Kepala UPTD', 'Staf UPTD'], ['Surat Izin Pemakaian Tempat'], '30 menit', 'Dokumen administrasi pemakaian venue ditandatangani', 'Kepala/Staf UPTD memberikan dokumen perizinan ke pemohon.'],
            ['Calon peminjam melakukan pembayaran kepada Bendahara Penerimaan atau melalui transfer ke rekening DISPORA/QRIS.', ['Bendahara Penerimaan'], ['Kuitansi pembayaran, bukti transfer, bukti QRIS'], '30 menit', 'Bukti pembayaran', 'Bendahara Penerimaan menerima pembayaran/bukti pembayaran.'],
        ],

        // [ikon Bootstrap Icons, nama, keterangan]
        'fasilitas' => [
            ['p-square', 'Area Parkir', 'Parkir luas roda 2 dan 4'],
            ['droplet', 'Toilet & Bilas', 'Fasilitas bersih di setiap venue'],
            ['info-circle', 'Info Penggunaan Venue', 'Papan info dan petugas siap membantu'],
            ['shield-check', 'Keamanan 24 Jam', 'Petugas berjaga sepanjang waktu'],
        ],

        'kontak' => [
            'alamat' => 'Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember, Jawa Timur',
            'telepon' => env('UPTD_OLAHRAGA_TELEPON', '0851 0000 9955'),
            'jam' => 'Senin–Jumat, 08.00–16.00 WIB',
        ],
    ],

    'wisata-terpadu' => [
        'judul' => 'UPTD Destinasi Wisata Terpadu',
        'ringkas' => 'Mengelola empat destinasi wisata unggulan di Kabupaten Jember, Jawa Timur.',
        'tentang' => 'Unit Pelaksana Teknis Daerah yang mengelola destinasi wisata di bawah Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember.',

        // [angka, keterangan]
        'angka' => [
            ['4', 'Destinasi wisata dikelola'],
            ['365', 'Hari operasional per tahun'],
            ['Ribuan', 'Pengunjung setiap bulan'],
            ['1', 'Pengelola terpadu & profesional'],
        ],

        'destinasi' => [
            [
                'nama' => 'Wisata Rembangan',
                'jenis' => 'Wisata Alam Pegunungan',
                'lokasi' => 'Jl. Rembangan, Arjasa, Jember',
                'ikon' => 'image-alt',
                'tentang' => 'Kawasan wisata alam pegunungan yang menawarkan kesejukan udara dan panorama indah khas Jember. Terletak di ketinggian dengan pemandangan perkebunan yang memesona, menjadi tujuan favorit keluarga dan wisatawan.',
                'fasilitas' => [
                    ['Kolam Renang', 'Kolam renang bersih dengan beragam ukuran'],
                    ['Penginapan', 'Vila dan cottage nyaman di ketinggian'],
                    ['Alam Terbuka', 'Panorama perkebunan dan pegunungan hijau'],
                    ['Spot Foto', 'Berbagai titik foto dengan latar alam indah'],
                ],
                'buka' => 'Setiap hari, 07.00–17.00 WIB',
                'tiket' => 'Dewasa Rp 15.000 · Anak Rp 8.000',
            ],
            [
                'nama' => 'Pantai Watu Ulo',
                'jenis' => 'Wisata Pantai',
                'lokasi' => 'Ambulu, Jember Selatan',
                'ikon' => 'water',
                'tentang' => 'Pantai eksotis dengan batuan karang unik berbentuk seperti sisik ular raksasa di tepi laut selatan Jember. Menawarkan ombak khas pantai selatan dengan pesona alam yang masih alami dan menawan.',
                'fasilitas' => [
                    ['Batu Karang Unik', 'Formasi batu sisik ular yang ikonik dan langka'],
                    ['Pantai Selatan', 'Ombak khas pantai selatan yang menakjubkan'],
                    ['Kuliner Laut', 'Aneka hidangan ikan segar hasil tangkapan lokal'],
                    ['Panorama Sunset', 'Spot terbaik menikmati matahari terbenam'],
                ],
                'buka' => 'Setiap hari, 07.00–17.00 WIB',
                'tiket' => 'Dewasa Rp 10.000 · Anak Rp 5.000',
            ],
            [
                'nama' => 'Pemandian & Hotel Kebonagung',
                'jenis' => 'Pemandian & Hotel',
                'lokasi' => 'Kebonagung, Jember',
                'ikon' => 'building',
                'tentang' => 'Destinasi lengkap yang memadukan fasilitas pemandian air bersih dengan penginapan berkelas. Cocok untuk keluarga yang ingin menikmati liburan nyaman sekaligus menyegarkan di lingkungan yang asri dan terawat.',
                'fasilitas' => [
                    ['Kolam Pemandian', 'Pemandian air bersih berstandar tinggi'],
                    ['Hotel & Penginapan', 'Kamar nyaman dengan berbagai tipe fasilitas'],
                    ['Restoran', 'Hidangan lezat khas Jawa Timur'],
                    ['Wisata Keluarga', 'Area bermain dan kolam anak yang aman'],
                ],
                'buka' => 'Setiap hari, 07.00–17.00 WIB',
                'tiket' => 'Dewasa Rp 20.000 · Anak Rp 10.000',
            ],
            [
                'nama' => 'Pemandian Patemon',
                'jenis' => 'Pemandian Alam',
                'lokasi' => 'Patemon, Tanggul, Jember',
                'ikon' => 'droplet-half',
                'tentang' => 'Pemandian alam dengan sumber air jernih dan menyegarkan di kawasan Patemon. Dikelilingi pepohonan rindang yang menciptakan suasana teduh dan sejuk, menjadi tempat favorit relaksasi warga Jember dan sekitarnya.',
                'fasilitas' => [
                    ['Air Sumber Alami', 'Sumber mata air alami yang jernih dan segar'],
                    ['Teduh & Asri', 'Pepohonan rindang menciptakan suasana nyaman'],
                    ['Relaksasi', 'Tempat ideal untuk melepas penat'],
                    ['Alam Terbuka', 'Lingkungan alami yang masih terjaga kelestariannya'],
                ],
                'buka' => 'Setiap hari, 07.00–17.00 WIB',
                'tiket' => 'Dewasa Rp 10.000 · Anak Rp 5.000',
            ],
        ],

        // [ikon Bootstrap Icons, nama, keterangan]
        'fasilitas_umum' => [
            ['p-square', 'Area Parkir', 'Parkir luas roda 2 dan 4, terpantau'],
            ['droplet', 'Toilet & Bilas', 'Fasilitas bersih di setiap destinasi'],
            ['cup-hot', 'Kuliner', 'Warung dan restoran menu lokal'],
            ['shield-check', 'Keamanan 24 Jam', 'Petugas berjaga sepanjang waktu'],
            ['heart-pulse', 'Pos Kesehatan', 'P3K dan tenaga medis siaga'],
            ['emoji-smile', 'Area Bermain', 'Zona bermain aman untuk anak'],
            ['house-door', 'Gazebo & Aula', 'Untuk acara dan gathering keluarga'],
            ['info-circle', 'Info Pengunjung', 'Papan info dan petugas siap membantu'],
        ],

        // [label, isi]
        'kunjungan' => [
            ['Hari buka', 'Senin–Minggu (termasuk hari libur nasional)'],
            ['Jam buka', '07.00–17.00 WIB'],
            ['Tiket dewasa', 'Rp 10.000–Rp 20.000 (bervariasi per destinasi)'],
            ['Tiket anak', 'Rp 5.000–Rp 10.000 (bervariasi per destinasi)'],
            ['Parkir motor', 'Rp 3.000/kendaraan'],
            ['Parkir mobil', 'Rp 5.000/kendaraan'],
        ],

        'kontak' => [
            'alamat' => 'Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember, Jawa Timur',
            // Nomor di dokumen belum lengkap ("(0331) --"); isi lewat .env setelah nomornya pasti.
            'telepon' => env('UPTD_WISATA_TELEPON', ''),
            'jam' => 'Senin–Jumat, 08.00–16.00 WIB',
            'email' => 'wisata@jemberkab.go.id',
            'website' => 'https://www.jemberkab.go.id/pariwisata',
            'medsos' => '@pariwisatajember (Instagram & Facebook)',
        ],
    ],

];
