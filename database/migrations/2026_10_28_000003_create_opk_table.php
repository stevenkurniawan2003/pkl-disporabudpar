<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Objek Pemajuan Kebudayaan (OPK) + galeri fotonya.
 * Pilihan jenis dan kondisi ada di App\Models\Opk::JENIS dan ::KONDISI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opk', function (Blueprint $table) {
            $table->id('id_opk');
            $table->string('nama', 150);
            $table->string('gambar_utama');
            $table->string('lokasi_persebaran', 255);
            $table->text('persebaran')->nullable();
            $table->string('jenis', 40);
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('pelaku_budaya', 255)->nullable();
            $table->string('kondisi', 30);
            $table->text('deskripsi');
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->timestamps();

            $table->index('jenis', 'idx_opk_jenis');
            $table->index('nama', 'idx_opk_nama');
            $table->foreign('dicatat_oleh', 'fk_opk_user')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('galeri_opk', function (Blueprint $table) {
            $table->id('id_galeri_opk');
            $table->unsignedBigInteger('id_opk');
            $table->string('gambar');
            $table->timestamps();

            $table->foreign('id_opk', 'fk_galeri_opk')->references('id_opk')->on('opk')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_opk');
        Schema::dropIfExists('opk');
    }
};
