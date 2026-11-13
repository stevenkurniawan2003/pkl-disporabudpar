<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_kesenian', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('jenis_kesenian_id');
            $table->unsignedSmallInteger('kecamatan_id');
            $table->string('nama', 150);
            $table->string('no_kartu_induk', 40)->nullable();
            $table->unsignedSmallInteger('jumlah_anggota')->default(0);
            $table->string('alamat', 255)->nullable();
            $table->string('kontak', 60)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('no_kartu_induk', 'uq_kesenian_kartu');
            $table->index(['jenis_kesenian_id', 'status'], 'idx_kesenian_jenis');
            $table->index('kecamatan_id', 'idx_kesenian_kecamatan');
            if (DB::getDriverName() === 'mysql') {
                $table->fullText('nama', 'ft_kesenian_cari');
            }
            $table->foreign('jenis_kesenian_id', 'fk_kesenian_jenis')->references('id')->on('jenis_kesenian')->cascadeOnUpdate();
            $table->foreign('kecamatan_id', 'fk_kesenian_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_kesenian');
    }
};
