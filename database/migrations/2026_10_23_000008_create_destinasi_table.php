<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('kategori_id');
            $table->unsignedSmallInteger('kecamatan_id');
            $table->string('nama', 150);
            $table->string('slug', 180);
            $table->text('narasi')->nullable();
            $table->string('alamat', 255);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('gambar_utama', 255)->nullable();
            $table->boolean('unggulan')->default(false);
            $table->enum('status', ['tayang', 'draf'])->default('tayang');
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'uq_destinasi_slug');
            $table->index(['unggulan', 'status'], 'idx_destinasi_unggulan');
            $table->index(['kategori_id', 'status'], 'idx_destinasi_kategori');
            $table->index('kecamatan_id', 'idx_destinasi_kecamatan');
            $table->index(['latitude', 'longitude'], 'idx_destinasi_koordinat');
            $table->index('dibuat_oleh', 'idx_destinasi_dibuat_oleh');
            $table->fullText(['nama', 'alamat'], 'ft_destinasi_cari');
            $table->foreign('kategori_id', 'fk_destinasi_kategori')->references('id')->on('kategori_destinasi')->cascadeOnUpdate();
            $table->foreign('kecamatan_id', 'fk_destinasi_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
            $table->foreign('dibuat_oleh', 'fk_destinasi_user')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE destinasi ADD CONSTRAINT ck_destinasi_lat CHECK (latitude BETWEEN -90 AND 90)');
            DB::statement('ALTER TABLE destinasi ADD CONSTRAINT ck_destinasi_lng CHECK (longitude BETWEEN -180 AND 180)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('destinasi');
    }
};
