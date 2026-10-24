<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data pelaku ekonomi kreatif / UMKM, hanya teks.
 * Pilihan subsektor disimpan sebagai string; daftarnya ada di App\Models\Ekraf::SUBSEKTOR.
 */
return new class extends Migration
{
    public function up(): void
    {
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

            $table->index('subsektor', 'idx_ekraf_subsektor');
            $table->index('nama_usaha', 'idx_ekraf_nama_usaha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekraf');
    }
};
