<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lengkapi tabel kunjungan_hotel untuk fitur Data Kunjungan Hotel
 * (sama dengan kunjungan_wisata): catatan opsional per periode dan admin yang mencatat datanya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kunjungan_hotel', function (Blueprint $table) {
            $table->string('keterangan', 255)->nullable()->after('mancanegara');
            $table->unsignedBigInteger('dicatat_oleh')->nullable()->after('keterangan');

            $table->index('dicatat_oleh', 'idx_kunj_hotel_pencatat');
            $table->foreign('dicatat_oleh', 'fk_kunj_hotel_user')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('kunjungan_hotel', function (Blueprint $table) {
            $this->lepasForeign($table, 'fk_kunj_hotel_user', 'dicatat_oleh');
            $table->dropIndex('idx_kunj_hotel_pencatat');
            $table->dropColumn(['keterangan', 'dicatat_oleh']);
        });
    }

    /**
     * Lepas foreign key: MySQL memakai nama constraint, SQLite hanya bisa lewat nama kolom.
     */
    private function lepasForeign(Blueprint $table, string $namaForeign, string $kolom): void
    {
        if (DB::getDriverName() === 'mysql') {
            $table->dropForeign($namaForeign);
        } else {
            $table->dropForeign([$kolom]);
        }
    }
};
