<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dokumen konsep Pariwisata:
 * - Peta Wisata berkategori: titik lokasi (opsional) untuk pelaku ekraf dan kesenian, plus tabel lokasi_peta
 *   untuk Restoran & Rumah Makan, Pusat Oleh-oleh, dan Lainnya yang belum punya tabel sendiri.
 * - Saran: Desa Wisata sesuai SK Bupati (nomor SK di wisata), Paket Wisata (flyer + CP), dan E-Booklet.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['ekraf' => 'status', 'kesenian' => 'status'] as $tabel => $setelah) {
            Schema::table($tabel, function (Blueprint $table) use ($setelah) {
                $table->decimal('latitude', 10, 7)->nullable()->after($setelah);
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }

        Schema::table('wisata', function (Blueprint $table) {
            $table->string('nomor_sk_desa_wisata', 120)->nullable()->after('link_google_maps');
        });

        Schema::create('lokasi_peta', function (Blueprint $table) {
            $table->id('id_lokasi');
            $table->string('nama', 150);
            $table->enum('kategori', ['restoran', 'oleh-oleh', 'lainnya']);
            $table->string('alamat', 255);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('deskripsi_singkat', 255)->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->string('gambar')->nullable();
            $table->timestamps();

            $table->index('kategori', 'idx_lokasi_peta_kategori');
        });

        Schema::create('paket_wisata', function (Blueprint $table) {
            $table->id('id_paket');
            $table->string('nama_paket', 150);
            $table->string('flyer');
            $table->text('deskripsi');
            $table->string('harga', 100)->nullable();
            $table->string('durasi', 100)->nullable();
            $table->string('nama_cp', 120);
            $table->string('no_cp', 20);
            $table->boolean('tayang')->default(true);
            $table->timestamps();

            $table->index('tayang', 'idx_paket_tayang');
        });

        Schema::create('ebooklet', function (Blueprint $table) {
            $table->id('id_ebooklet');
            $table->string('judul', 200);
            $table->unsignedSmallInteger('tahun');
            $table->text('deskripsi')->nullable();
            $table->string('sampul')->nullable();
            $table->string('file_pdf');
            $table->timestamps();

            $table->index('tahun', 'idx_ebooklet_tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebooklet');
        Schema::dropIfExists('paket_wisata');
        Schema::dropIfExists('lokasi_peta');

        Schema::table('wisata', fn (Blueprint $table) => $table->dropColumn('nomor_sk_desa_wisata'));

        foreach (['ekraf', 'kesenian'] as $tabel) {
            Schema::table($tabel, fn (Blueprint $table) => $table->dropColumn(['latitude', 'longitude']));
        }
    }
};
