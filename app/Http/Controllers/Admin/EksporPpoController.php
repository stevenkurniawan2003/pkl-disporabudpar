<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Pelatih;
use App\Models\PrestasiAtlet;
use App\Support\EksporTabel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fitur "Extract" PPO: Database Atlet, Pelatih, Cabor, dan Data Atlet Berprestasi ke PDF atau Excel,
 * dengan kolom tanda tangan Kepala Dinas. Filter sama dengan halaman daftarnya (?cabor=&q=).
 */
class EksporPpoController extends Controller
{
    public const JENIS = ['atlet', 'pelatih', 'cabor', 'prestasi'];

    public function __invoke(Request $request, string $jenis): Response
    {
        abort_unless(in_array($jenis, self::JENIS, true), 404);

        $filter = $request->validate([
            'format' => ['nullable', 'in:pdf,xlsx'],
            'cabor' => ['nullable', 'in:'.implode(',', array_keys(Cabor::DAFTAR))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $filterData = ['cabor' => $filter['cabor'] ?? null, 'cari' => $filter['q'] ?? null];

        [$judul, $kolom, $baris] = match ($jenis) {
            'atlet' => $this->atlet($filterData),
            'pelatih' => $this->pelatih($filterData),
            'cabor' => $this->cabor($filterData),
            'prestasi' => $this->prestasi($filterData),
        };

        $keterangan = collect([
            ! empty($filter['cabor']) ? 'Cabor '.Cabor::nama($filter['cabor']) : 'Semua cabor',
            ! empty($filter['q']) ? 'Pencarian "'.$filter['q'].'"' : null,
            'Kabupaten Jember',
        ])->filter()->implode(' - ');

        $ekspor = new EksporTabel($judul, $keterangan, $kolom, $baris, config('site.pejabat.kepala_dinas'));
        $nama = 'ppo-'.$jenis.'-'.now()->format('Ymd-His');

        return ($filter['format'] ?? 'pdf') === 'xlsx' ? $ekspor->xlsx($nama) : $ekspor->pdf($nama, $request->boolean('unduh', true));
    }

    private function atlet(array $filter): array
    {
        $daftar = Atlet::with('kecamatan')->filter($filter)->orderBy('kode_cabor')->orderBy('nama_lengkap')->get();

        return ['Database Atlet', ['Nama Lengkap', 'Tempat, Tanggal Lahir', 'Umur', 'L/P', 'Alamat', 'Kecamatan', 'No. HP', 'Cabor', 'Klub', 'Prestasi Terakhir', 'Status Binaan', 'Status'],
            $daftar->map(fn (Atlet $a) => [$a->nama_lengkap, $a->ttl, $a->umur, $a->jenis_kelamin, $a->alamat, $a->kecamatan?->nama, $a->no_hp, $a->nama_cabor, $a->klub, $a->prestasi_terakhir, $a->status_binaan, $a->status_label])->all()];
    }

    private function pelatih(array $filter): array
    {
        $daftar = Pelatih::with('kecamatan')->filter($filter)->orderBy('kode_cabor')->orderBy('nama_lengkap')->get();

        return ['Database Pelatih', ['Nama Lengkap', 'Tempat, Tanggal Lahir', 'Umur', 'L/P', 'Alamat', 'Kecamatan', 'No. HP', 'Cabor', 'Sertifikat/Lisensi', 'Status'],
            $daftar->map(fn (Pelatih $p) => [$p->nama_lengkap, $p->ttl, $p->umur, $p->jenis_kelamin, $p->alamat, $p->kecamatan?->nama, $p->no_hp, $p->nama_cabor, $p->lisensi_label, $p->status_label])->all()];
    }

    private function cabor(array $filter): array
    {
        $daftar = Cabor::with('kecamatan')->denganJumlah()
            ->when(! empty($filter['cabor']), fn ($q) => $q->where('kode_cabor', $filter['cabor']))
            ->get()
            ->filter(fn (Cabor $c) => empty($filter['cari']) || str_contains(mb_strtolower($c->nama_cabor), mb_strtolower($filter['cari'])))
            ->sortBy('nama_cabor')->values();

        return ['Database Cabor', ['Cabang Olahraga', 'Induk Organisasi', 'Ketua Pengkab', 'Alamat Sekretariat', 'Kecamatan', 'Atlet Aktif', 'Pelatih Aktif', 'Prestasi Terakhir', 'Kontak', 'Status'],
            $daftar->map(fn (Cabor $c) => [$c->nama_cabor, $c->induk_organisasi, $c->ketua_pengkab, $c->alamat_sekretariat, $c->kecamatan?->nama, (int) $c->jumlah_atlet_aktif, (int) $c->jumlah_pelatih_aktif, $c->prestasi_terakhir, $c->kontak, $c->status_label])->all()];
    }

    private function prestasi(array $filter): array
    {
        $kata = trim((string) $filter['cari']);
        $daftar = PrestasiAtlet::query()
            ->when(! empty($filter['cabor']), fn ($q) => $q->where('kode_cabor', $filter['cabor']))
            ->when($kata !== '', fn ($q) => $q->where('nama_atlet', 'like', '%'.addcslashes($kata, '%_\\').'%'))
            ->orderByDesc('tahun')->orderBy('nama_atlet')->get();

        return ['Data Atlet Berprestasi', ['Nama', 'Jenis Medali', 'Cabor', 'Nomor Pertandingan', 'Kejuaraan', 'Tahun'],
            $daftar->map(fn (PrestasiAtlet $p) => [$p->nama_atlet, $p->jenis_medali, $p->nama_cabor, $p->nomor_pertandingan, $p->nama_kejuaraan, $p->tahun])->all()];
    }
}
