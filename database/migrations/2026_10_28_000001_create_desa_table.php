<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Desa/kelurahan per kecamatan (248 desa/kelurahan di 31 kecamatan Jember, diisi DesaSeeder).
 * Dipakai pilihan berjenjang Kecamatan -> Desa di form Kartu Induk Kesenian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desa', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('kecamatan_id');
            $table->string('nama', 60);

            // Nama desa bisa sama di kecamatan berbeda (mis. Ajung, Patemon), jadi unik per kecamatan.
            $table->unique(['kecamatan_id', 'nama'], 'uq_desa_kecamatan_nama');
            $table->foreign('kecamatan_id', 'fk_desa_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa');
    }
};
