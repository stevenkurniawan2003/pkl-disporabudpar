<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kartu Induk Kesenian (dokumen konsep Kebudayaan): tabel kesenian lama (nama, kategori, deskripsi)
 * diganti total menjadi data master sanggar/komunitas/lembaga kesenian, ditambah riwayat perpanjangan KIK.
 * Tabel jenis_kesenian & kelompok_kesenian (rancangan awal, tidak pernah dipakai) ikut dihapus.
 * Daftar jenis kesenian beserta kodenya ada di App\Models\Kesenian::JENIS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('kelompok_kesenian');
        Schema::dropIfExists('jenis_kesenian');
        Schema::dropIfExists('kesenian');

        Schema::create('kesenian', function (Blueprint $table) {
            $table->id('id_kesenian');
            // Kode jenis + nomor urut per jenis, contoh 2.5-048. Dibuat otomatis oleh sistem.
            $table->string('nomor_induk', 20);
            $table->string('kode_jenis', 10);
            $table->unsignedSmallInteger('nomor_urut');
            $table->string('nama_kesenian', 150);
            $table->string('nama_pemilik', 150);
            $table->string('alamat', 255);
            $table->string('kabupaten', 60)->default('Jember');
            $table->unsignedSmallInteger('kecamatan_id');
            $table->unsignedSmallInteger('desa_id');
            $table->unsignedSmallInteger('jumlah_anggota');
            $table->string('no_telepon', 20)->nullable();
            $table->string('dokumentasi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->unique('nomor_induk', 'uq_kesenian_nomor_induk');
            $table->unique(['kode_jenis', 'nomor_urut'], 'uq_kesenian_jenis_urut');
            $table->index(['kecamatan_id', 'desa_id'], 'idx_kesenian_wilayah');
            $table->index('status', 'idx_kesenian_status');
            $table->foreign('kecamatan_id', 'fk_kesenian_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
            $table->foreign('desa_id', 'fk_kesenian_desa')->references('id')->on('desa')->cascadeOnUpdate();
        });

        Schema::create('perpanjangan_kik', function (Blueprint $table) {
            $table->id('id_perpanjangan');
            $table->unsignedBigInteger('id_kesenian');
            $table->date('tanggal_pengajuan');
            $table->date('berlaku_sampai');
            $table->string('foto_ketua');
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->timestamps();

            $table->index(['id_kesenian', 'tanggal_pengajuan'], 'idx_perpanjangan_kesenian');
            $table->foreign('id_kesenian', 'fk_perpanjangan_kesenian')->references('id_kesenian')->on('kesenian')->cascadeOnDelete();
            $table->foreign('dicatat_oleh', 'fk_perpanjangan_user')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perpanjangan_kik');
        Schema::dropIfExists('kesenian');

        // Kembali ke struktur tabel kesenian sebelumnya (datanya tidak bisa dikembalikan).
        Schema::create('kesenian', function (Blueprint $table) {
            $table->id('id_kesenian');
            $table->string('nama_kesenian', 150);
            $table->string('kategori', 50);
            $table->string('asal_daerah', 150)->nullable();
            $table->text('deskripsi');
            $table->timestamps();
        });
    }
};
