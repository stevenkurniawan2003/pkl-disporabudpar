<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Samakan field wisata dengan hotel: deskripsi singkat (untuk popup peta publik) dan link Google Maps.
 * deskripsi_singkat nullable di database karena data lama belum punya; form admin tetap mewajibkannya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wisata', function (Blueprint $table) {
            $table->string('deskripsi_singkat', 200)->nullable()->after('slug');
            $table->string('link_google_maps', 255)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('wisata', function (Blueprint $table) {
            $table->dropColumn(['deskripsi_singkat', 'link_google_maps']);
        });
    }
};
