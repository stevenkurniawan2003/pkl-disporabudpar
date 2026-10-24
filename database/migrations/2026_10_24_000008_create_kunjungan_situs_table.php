<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penghitung pengunjung situs publik: satu baris = satu sesi browser per hari.
 * Diisi oleh middleware App\Http\Middleware\CatatKunjunganSitus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_situs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 255);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->date('tanggal');
            $table->timestamps();

            // Pengaman kalau dua request pertama dari sesi yang sama datang bersamaan.
            $table->unique(['session_id', 'tanggal'], 'uq_kunjungan_situs_sesi_tanggal');
            $table->index('tanggal', 'idx_kunjungan_situs_tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_situs');
    }
};
