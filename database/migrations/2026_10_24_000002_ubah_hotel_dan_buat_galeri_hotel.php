<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Susun ulang tabel hotel untuk fitur CRUD Hotel (gambar utama + galeri).
 * Tabel hotel lama belum pernah dipakai (belum ada halaman admin-nya), jadi dibuat ulang.
 * kunjungan_hotel.hotel_id tetap, hanya foreign key-nya diarahkan ke hotel.id_hotel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kunjungan_hotel', fn (Blueprint $t) => $t->dropForeign('fk_kunj_hotel_hotel'));
        Schema::dropIfExists('hotel');

        Schema::create('hotel', function (Blueprint $table) {
            $table->id('id_hotel');
            $table->string('nama_hotel', 150);
            $table->string('gambar_utama', 255);
            $table->string('deskripsi_singkat', 200);
            $table->text('narasi');
            $table->string('alamat', 255);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('link_google_maps', 255)->nullable();
            $table->string('link_website', 255)->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->timestamps();

            $table->index('nama_hotel', 'idx_hotel_nama');
            $table->index(['latitude', 'longitude'], 'idx_hotel_koordinat');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE hotel ADD CONSTRAINT ck_hotel_lat CHECK (latitude BETWEEN -90 AND 90)');
            DB::statement('ALTER TABLE hotel ADD CONSTRAINT ck_hotel_lng CHECK (longitude BETWEEN -180 AND 180)');
        }

        Schema::create('galeri_hotel', function (Blueprint $table) {
            $table->id('id_galeri_hotel');
            $table->unsignedBigInteger('id_hotel');
            $table->string('gambar', 255);
            $table->timestamps();

            $table->index('id_hotel', 'idx_galeri_hotel');
            $table->foreign('id_hotel', 'fk_galeri_hotel')->references('id_hotel')->on('hotel')->cascadeOnDelete()->cascadeOnUpdate();
        });

        Schema::table('kunjungan_hotel', function (Blueprint $t) {
            $t->foreign('hotel_id', 'fk_kunj_hotel_hotel')->references('id_hotel')->on('hotel')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('kunjungan_hotel', fn (Blueprint $t) => $t->dropForeign('fk_kunj_hotel_hotel'));
        Schema::dropIfExists('galeri_hotel');
        Schema::dropIfExists('hotel');

        // Struktur lama dari 2026_10_23_000017_create_hotel_table.
        Schema::create('hotel', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('kecamatan_id');
            $table->string('nama', 150);
            $table->string('kelas', 30)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('nama', 'uq_hotel_nama');
            $table->index('kecamatan_id', 'idx_hotel_kecamatan');
            $table->foreign('kecamatan_id', 'fk_hotel_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
        });

        Schema::table('kunjungan_hotel', function (Blueprint $t) {
            $t->foreign('hotel_id', 'fk_kunj_hotel_hotel')->references('id')->on('hotel')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }
};
