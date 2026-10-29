<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Objek Diduga Cagar Budaya (ODCB) dan Cagar Budaya (CB) + galeri fotonya.
 * Pilihan kategori, status, dan kondisi ada di App\Models\CagarBudaya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cagar_budaya', function (Blueprint $table) {
            $table->id('id_cagar_budaya');
            $table->string('nama', 150);
            $table->string('gambar_utama');
            $table->string('alamat', 255);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('kategori', 20);
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->enum('status', ['ODCB', 'CB']);
            $table->enum('kondisi', ['Terpelihara', 'Tidak Terpelihara']);
            $table->text('deskripsi_sejarah');
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->timestamps();

            $table->index(['kategori', 'status'], 'idx_cagar_budaya_kategori');
            $table->index('nama', 'idx_cagar_budaya_nama');
            $table->foreign('dicatat_oleh', 'fk_cagar_budaya_user')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('galeri_cagar_budaya', function (Blueprint $table) {
            $table->id('id_galeri_cagar_budaya');
            $table->unsignedBigInteger('id_cagar_budaya');
            $table->string('gambar');
            $table->timestamps();

            $table->foreign('id_cagar_budaya', 'fk_galeri_cagar_budaya')->references('id_cagar_budaya')->on('cagar_budaya')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_cagar_budaya');
        Schema::dropIfExists('cagar_budaya');
    }
};
