<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelaku_ekraf', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('subsektor_id');
            $table->unsignedSmallInteger('kecamatan_id');
            $table->string('nama_usaha', 150);
            $table->string('nama_pemilik', 120)->nullable();
            $table->string('kontak', 60)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subsektor_id', 'status'], 'idx_ekraf_subsektor');
            $table->index('kecamatan_id', 'idx_ekraf_kecamatan');
            if (DB::getDriverName() === 'mysql') {
                $table->fullText(['nama_usaha', 'nama_pemilik'], 'ft_ekraf_cari');
            }
            $table->foreign('subsektor_id', 'fk_ekraf_subsektor')->references('id')->on('subsektor_ekraf')->cascadeOnUpdate();
            $table->foreign('kecamatan_id', 'fk_ekraf_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelaku_ekraf');
    }
};
