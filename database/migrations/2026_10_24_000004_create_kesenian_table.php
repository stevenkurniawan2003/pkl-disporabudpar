<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data kesenian (bagian dari Kebudayaan), hanya teks.
 * Pilihan kategori disimpan sebagai string; daftarnya ada di App\Models\Kesenian::KATEGORI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kesenian', function (Blueprint $table) {
            $table->id('id_kesenian');
            $table->string('nama_kesenian', 150);
            $table->string('kategori', 50);
            $table->string('asal_daerah', 150)->nullable();
            $table->text('deskripsi');
            $table->timestamps();

            $table->index('kategori', 'idx_kesenian_kategori');
            $table->index('nama_kesenian', 'idx_kesenian_nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kesenian');
    }
};
