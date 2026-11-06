<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_wisata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destinasi_id');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedInteger('domestik')->default(0);
            $table->unsignedInteger('mancanegara')->default(0);
            $table->timestamps();

            $table->unique(['destinasi_id', 'tahun', 'bulan'], 'uq_kunj_wisata_periode');
            $table->index(['tahun', 'bulan'], 'idx_kunj_wisata_periode');
            $table->foreign('destinasi_id', 'fk_kunj_wisata_destinasi')->references('id')->on('destinasi')->cascadeOnDelete()->cascadeOnUpdate();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE kunjungan_wisata ADD CONSTRAINT ck_kunj_wisata_bulan CHECK (bulan BETWEEN 1 AND 12)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_wisata');
    }
};
