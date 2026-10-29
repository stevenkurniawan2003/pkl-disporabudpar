<?php

namespace App\Models\Concerns;

use App\Models\Cabor;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bagian yang sama pada atlet dan pelatih: tempat/tanggal lahir, umur otomatis, jenis kelamin,
 * kecamatan, cabor, status keaktifan, serta filter daftar (cabor + pencarian nama/cabor).
 * Model wajib punya kolom tempat_lahir, tanggal_lahir (cast date), jenis_kelamin, kecamatan_id, kode_cabor, status.
 */
trait BiodataOlahraga
{
    public const JENIS_KELAMIN = ['L' => 'Laki-laki', 'P' => 'Perempuan'];

    public const STATUS = ['aktif' => 'Aktif', 'nonaktif' => 'Tidak Aktif'];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('status', 'aktif');
    }

    /**
     * Filter Database Atlet/Pelatih: pilihan cabor dan pencarian nama atau nama cabor.
     *
     * @param  array{cabor?: string|null, cari?: string|null}  $filter
     */
    public function scopeFilter(Builder $query, array $filter): void
    {
        $kata = trim((string) ($filter['cari'] ?? ''));

        $query
            ->when(! empty($filter['cabor']), fn ($q) => $q->where('kode_cabor', $filter['cabor']))
            ->when($kata !== '', function ($q) use ($kata) {
                // Cari juga lewat nama cabor: "silat" cocok dengan kode "pencak-silat".
                $kodeCocok = array_keys(array_filter(Cabor::DAFTAR, fn ($c) => str_contains(mb_strtolower($c[0]), mb_strtolower($kata))));
                $q->where(fn ($w) => $w->where('nama_lengkap', 'like', '%'.addcslashes($kata, '%_\\').'%')->orWhereIn('kode_cabor', $kodeCocok));
            });
    }

    /** Umur dihitung otomatis dari tanggal lahir. */
    public function getUmurAttribute(): ?int
    {
        return $this->tanggal_lahir?->age;
    }

    /** "Jember, 17 Agustus 2005" */
    public function getTtlAttribute(): string
    {
        return $this->tempat_lahir.', '.($this->tanggal_lahir?->locale('id')->translatedFormat('j F Y') ?? '-');
    }

    public function getNamaCaborAttribute(): string
    {
        return Cabor::nama($this->kode_cabor);
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return self::JENIS_KELAMIN[$this->jenis_kelamin] ?? $this->jenis_kelamin;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
