<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bidang Peningkatan Prestasi Olahraga (PPO): database atlet, pelatih, cabor, prestasi atlet, dan galeri prestasi.
 * Cabang olahraga disimpan sebagai kode (mis. "pencak-silat"); daftar 50 cabor + induk organisasinya ada di
 * App\Models\Cabor::DAFTAR, jadi atlet/pelatih tetap bisa diinput walau data cabornya belum dibuat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabor', function (Blueprint $table) {
            $table->id('id_cabor');
            $table->string('kode_cabor', 40);
            $table->string('ketua_pengkab', 150);
            $table->string('alamat_sekretariat', 255);
            $table->unsignedSmallInteger('kecamatan_id');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('prestasi_terakhir')->nullable();
            $table->string('kontak', 100)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->unique('kode_cabor', 'uq_cabor_kode');
            $table->foreign('kecamatan_id', 'fk_cabor_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
        });

        Schema::create('atlet', function (Blueprint $table) {
            $table->id('id_atlet');
            $table->string('nama_lengkap', 150);
            $table->string('tempat_lahir', 80);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('alamat', 255);
            $table->unsignedSmallInteger('kecamatan_id');
            $table->string('no_hp', 20)->nullable();
            $table->string('kode_cabor', 40);
            $table->string('klub', 150)->nullable();
            $table->text('prestasi_terakhir')->nullable();
            $table->enum('status_binaan', ['Pemula', 'Junior', 'Senior', 'Elite']);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index(['kode_cabor', 'status'], 'idx_atlet_cabor');
            $table->index('nama_lengkap', 'idx_atlet_nama');
            $table->foreign('kecamatan_id', 'fk_atlet_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
        });

        Schema::create('pelatih', function (Blueprint $table) {
            $table->id('id_pelatih');
            $table->string('nama_lengkap', 150);
            $table->string('tempat_lahir', 80);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('alamat', 255);
            $table->unsignedSmallInteger('kecamatan_id');
            $table->string('no_hp', 20)->nullable();
            $table->string('kode_cabor', 40);
            $table->boolean('punya_lisensi')->default(false);
            $table->string('nomor_lisensi', 100)->nullable();
            $table->string('file_lisensi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index(['kode_cabor', 'status'], 'idx_pelatih_cabor');
            $table->index('nama_lengkap', 'idx_pelatih_nama');
            $table->foreign('kecamatan_id', 'fk_pelatih_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
        });

        Schema::create('prestasi_atlet', function (Blueprint $table) {
            $table->id('id_prestasi');
            $table->string('nama_atlet', 150);
            $table->enum('jenis_medali', ['Emas', 'Perak', 'Perunggu']);
            $table->string('kode_cabor', 40);
            $table->string('nomor_pertandingan', 150);
            $table->string('nama_kejuaraan', 200)->nullable();
            $table->unsignedSmallInteger('tahun');
            $table->timestamps();

            $table->index('tahun', 'idx_prestasi_tahun');
            $table->index('kode_cabor', 'idx_prestasi_cabor');
        });

        Schema::create('galeri_prestasi', function (Blueprint $table) {
            $table->id('id_galeri_prestasi');
            $table->string('judul', 200);
            $table->text('keterangan')->nullable();
            $table->enum('jenis', ['foto', 'video']);
            $table->string('file');
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_prestasi');
        Schema::dropIfExists('prestasi_atlet');
        Schema::dropIfExists('pelatih');
        Schema::dropIfExists('atlet');
        Schema::dropIfExists('cabor');
    }
};
