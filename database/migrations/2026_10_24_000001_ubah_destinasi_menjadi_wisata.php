<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ganti istilah "destinasi" menjadi "wisata" tanpa menghapus data:
 *   kategori_destinasi -> kategori_wisata
 *   destinasi          -> wisata
 *   destinasi_galeri   -> galeri_wisata (kolom destinasi_id -> wisata_id)
 *   kunjungan_wisata.destinasi_id -> wisata_id
 * Nama index, foreign key, dan constraint CHECK ikut disesuaikan.
 */
return new class extends Migration
{
    private const INDEX_KATEGORI = [
        'uq_kat_destinasi_nama' => 'uq_kat_wisata_nama',
        'uq_kat_destinasi_slug' => 'uq_kat_wisata_slug',
    ];

    private const INDEX_WISATA = [
        'uq_destinasi_slug' => 'uq_wisata_slug',
        'idx_destinasi_unggulan' => 'idx_wisata_unggulan',
        'idx_destinasi_kategori' => 'idx_wisata_kategori',
        'idx_destinasi_kecamatan' => 'idx_wisata_kecamatan',
        'idx_destinasi_koordinat' => 'idx_wisata_koordinat',
        'idx_destinasi_dibuat_oleh' => 'idx_wisata_dibuat_oleh',
    ];

    public function up(): void
    {
        $mysql = DB::getDriverName() === 'mysql';

        // 1. Lepas foreign key dan CHECK lama (dibuat ulang dengan nama baru di langkah 4).
        Schema::table('destinasi_galeri', fn (Blueprint $t) => $t->dropForeign('fk_galeri_destinasi'));
        Schema::table('kunjungan_wisata', fn (Blueprint $t) => $t->dropForeign('fk_kunj_wisata_destinasi'));
        Schema::table('destinasi', function (Blueprint $t) {
            $t->dropForeign('fk_destinasi_kategori');
            $t->dropForeign('fk_destinasi_kecamatan');
            $t->dropForeign('fk_destinasi_user');
        });

        if ($mysql) {
            DB::statement('ALTER TABLE destinasi DROP CHECK ck_destinasi_lat');
            DB::statement('ALTER TABLE destinasi DROP CHECK ck_destinasi_lng');
        }

        // 2. Ganti nama tabel.
        Schema::rename('kategori_destinasi', 'kategori_wisata');
        Schema::rename('destinasi', 'wisata');
        Schema::rename('destinasi_galeri', 'galeri_wisata');

        // 3. Ganti nama kolom dan index.
        Schema::table('kategori_wisata', function (Blueprint $t) {
            foreach (self::INDEX_KATEGORI as $lama => $baru) {
                $t->renameIndex($lama, $baru);
            }
        });

        Schema::table('wisata', function (Blueprint $t) use ($mysql) {
            foreach (self::INDEX_WISATA as $lama => $baru) {
                $t->renameIndex($lama, $baru);
            }
            if ($mysql) {
                $t->renameIndex('ft_destinasi_cari', 'ft_wisata_cari');
            }
        });

        Schema::table('galeri_wisata', function (Blueprint $t) {
            $t->renameColumn('destinasi_id', 'wisata_id');
            $t->renameIndex('idx_galeri_destinasi', 'idx_galeri_wisata');
        });

        Schema::table('kunjungan_wisata', fn (Blueprint $t) => $t->renameColumn('destinasi_id', 'wisata_id'));

        // 4. Pasang lagi foreign key dan CHECK dengan nama baru.
        Schema::table('wisata', function (Blueprint $t) {
            $t->foreign('kategori_id', 'fk_wisata_kategori')->references('id')->on('kategori_wisata')->cascadeOnUpdate();
            $t->foreign('kecamatan_id', 'fk_wisata_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
            $t->foreign('dibuat_oleh', 'fk_wisata_user')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });
        Schema::table('galeri_wisata', function (Blueprint $t) {
            $t->foreign('wisata_id', 'fk_galeri_wisata')->references('id')->on('wisata')->cascadeOnDelete()->cascadeOnUpdate();
        });
        Schema::table('kunjungan_wisata', function (Blueprint $t) {
            $t->foreign('wisata_id', 'fk_kunj_wisata_wisata')->references('id')->on('wisata')->cascadeOnDelete()->cascadeOnUpdate();
        });

        if ($mysql) {
            DB::statement('ALTER TABLE wisata ADD CONSTRAINT ck_wisata_lat CHECK (latitude BETWEEN -90 AND 90)');
            DB::statement('ALTER TABLE wisata ADD CONSTRAINT ck_wisata_lng CHECK (longitude BETWEEN -180 AND 180)');
        }
    }

    public function down(): void
    {
        $mysql = DB::getDriverName() === 'mysql';

        Schema::table('galeri_wisata', fn (Blueprint $t) => $t->dropForeign('fk_galeri_wisata'));
        Schema::table('kunjungan_wisata', fn (Blueprint $t) => $t->dropForeign('fk_kunj_wisata_wisata'));
        Schema::table('wisata', function (Blueprint $t) {
            $t->dropForeign('fk_wisata_kategori');
            $t->dropForeign('fk_wisata_kecamatan');
            $t->dropForeign('fk_wisata_user');
        });

        if ($mysql) {
            DB::statement('ALTER TABLE wisata DROP CHECK ck_wisata_lat');
            DB::statement('ALTER TABLE wisata DROP CHECK ck_wisata_lng');
        }

        Schema::rename('kategori_wisata', 'kategori_destinasi');
        Schema::rename('wisata', 'destinasi');
        Schema::rename('galeri_wisata', 'destinasi_galeri');

        Schema::table('kategori_destinasi', function (Blueprint $t) {
            foreach (self::INDEX_KATEGORI as $lama => $baru) {
                $t->renameIndex($baru, $lama);
            }
        });

        Schema::table('destinasi', function (Blueprint $t) use ($mysql) {
            foreach (self::INDEX_WISATA as $lama => $baru) {
                $t->renameIndex($baru, $lama);
            }
            if ($mysql) {
                $t->renameIndex('ft_wisata_cari', 'ft_destinasi_cari');
            }
        });

        Schema::table('destinasi_galeri', function (Blueprint $t) {
            $t->renameColumn('wisata_id', 'destinasi_id');
            $t->renameIndex('idx_galeri_wisata', 'idx_galeri_destinasi');
        });

        Schema::table('kunjungan_wisata', fn (Blueprint $t) => $t->renameColumn('wisata_id', 'destinasi_id'));

        Schema::table('destinasi', function (Blueprint $t) {
            $t->foreign('kategori_id', 'fk_destinasi_kategori')->references('id')->on('kategori_destinasi')->cascadeOnUpdate();
            $t->foreign('kecamatan_id', 'fk_destinasi_kecamatan')->references('id')->on('kecamatan')->cascadeOnUpdate();
            $t->foreign('dibuat_oleh', 'fk_destinasi_user')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });
        Schema::table('destinasi_galeri', function (Blueprint $t) {
            $t->foreign('destinasi_id', 'fk_galeri_destinasi')->references('id')->on('destinasi')->cascadeOnDelete()->cascadeOnUpdate();
        });
        Schema::table('kunjungan_wisata', function (Blueprint $t) {
            $t->foreign('destinasi_id', 'fk_kunj_wisata_destinasi')->references('id')->on('destinasi')->cascadeOnDelete()->cascadeOnUpdate();
        });

        if ($mysql) {
            DB::statement('ALTER TABLE destinasi ADD CONSTRAINT ck_destinasi_lat CHECK (latitude BETWEEN -90 AND 90)');
            DB::statement('ALTER TABLE destinasi ADD CONSTRAINT ck_destinasi_lng CHECK (longitude BETWEEN -180 AND 180)');
        }
    }
};
