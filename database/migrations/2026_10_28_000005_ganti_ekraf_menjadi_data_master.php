<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data Master pelaku Ekonomi Kreatif (dokumen konsep Ekraf): tabel ekraf lama (nama usaha, pemilik, deskripsi,
 * media sosial) diganti total. Tabel pelaku_ekraf & subsektor_ekraf (rancangan awal, tidak pernah dipakai) ikut dihapus.
 * Daftar 17 subsektor beserta kodenya ada di App\Models\Ekraf::SUBSEKTOR.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pelaku_ekraf');
        Schema::dropIfExists('subsektor_ekraf');
        Schema::dropIfExists('ekraf');

        Schema::create('ekraf', function (Blueprint $table) {
            $table->id('id_ekraf');
            // Kode subsektor + nomor urut per subsektor, contoh 1.8-047 (Kriya). Dibuat otomatis oleh sistem.
            $table->string('nomor_ekraf', 20);
            $table->string('kode_subsektor', 10);
            $table->unsignedSmallInteger('nomor_urut');
            $table->string('nama_usaha', 200);
            $table->string('alamat', 255);
            $table->string('kabupaten', 60)->default('Jember');
            $table->unsignedSmallInteger('kecamatan_id');
            $table->unsignedSmallInteger('desa_id');
            $table->unsignedBigInteger('omzet')->default(0);
            $table->string('no_telepon', 20)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->unique('nomor_ekraf', 'uq_ekraf_nomor');
            $table->unique(['kode_subsektor', 'nomor_urut'], 'uq_ekraf_subsektor_urut');
            $table->index(['kecamatan_id', 'desa_id'], 'idx_ekraf_wilayah');
            $table->index('status', 'idx_ekraf_status');
            $table->foreign('kecamatan_id', 'fk_ekraf_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
            $table->foreign('desa_id', 'fk_ekraf_desa')->references('id')->on('desa')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekraf');

        // Kembali ke struktur tabel ekraf sebelumnya (datanya tidak bisa dikembalikan).
        Schema::create('ekraf', function (Blueprint $table) {
            $table->id('id_ekraf');
            $table->string('nama_usaha', 150);
            $table->string('nama_pemilik', 150);
            $table->string('subsektor', 50);
            $table->string('alamat', 255);
            $table->string('no_telepon', 20)->nullable();
            $table->string('media_sosial', 255)->nullable();
            $table->text('deskripsi');
            $table->timestamps();
        });
    }
};
