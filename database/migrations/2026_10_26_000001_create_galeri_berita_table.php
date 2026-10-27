<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mini galeri berita (pola sama dengan galeri_hotel). Gambar sampul tetap di berita.gambar_sampul.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeri_berita', function (Blueprint $table) {
            $table->id('id_galeri_berita');
            $table->unsignedBigInteger('berita_id');
            $table->string('gambar', 255);
            $table->timestamps();

            $table->index('berita_id', 'idx_galeri_berita');
            $table->foreign('berita_id', 'fk_galeri_berita')->references('id')->on('berita')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_berita');
    }
};
