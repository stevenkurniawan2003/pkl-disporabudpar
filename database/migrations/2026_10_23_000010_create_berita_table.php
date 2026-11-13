<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('kategori_berita_id');
            $table->string('judul', 200);
            $table->string('slug', 230);
            $table->string('ringkasan', 300)->nullable();
            $table->longText('isi');
            $table->string('gambar_sampul', 255)->nullable();
            $table->enum('status', ['draf', 'terbit'])->default('draf');
            $table->dateTime('tanggal_terbit')->nullable();
            $table->unsignedInteger('jumlah_dibaca')->default(0);
            $table->unsignedBigInteger('penulis_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'uq_berita_slug');
            $table->index(['status', 'tanggal_terbit'], 'idx_berita_terbit');
            $table->index(['kategori_berita_id', 'status', 'tanggal_terbit'], 'idx_berita_kategori');
            $table->index('jumlah_dibaca', 'idx_berita_populer');
            $table->index('penulis_id', 'idx_berita_penulis');
            if (DB::getDriverName() === 'mysql') {
                $table->fullText(['judul', 'ringkasan'], 'ft_berita_cari');
            }
            $table->foreign('kategori_berita_id', 'fk_berita_kategori')->references('id')->on('kategori_berita')->cascadeOnUpdate();
            $table->foreign('penulis_id', 'fk_berita_penulis')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
