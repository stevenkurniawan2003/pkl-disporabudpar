<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kesenian;
use App\Models\PerpanjanganKik;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/** Dokumen PDF bidang Kebudayaan: daftar data kesenian (menu Cetak) dan Kartu Induk Kesenian. */
class CetakKebudayaanController extends Controller
{
    /** Lebar x tinggi kartu: 8,5 cm x 5,5 cm, dalam point (1 cm = 28,3465 pt). */
    private const UKURAN_KARTU = [0, 0, 240.94, 155.91];

    /** Daftar kesenian sesuai filter menu Cetak. ?unduh=1 -> unduh file, selain itu dibuka di tab (untuk dicetak). */
    public function dataKesenian(Request $request): Response
    {
        $filter = $request->validate([
            'kecamatan' => ['nullable', 'integer'],
            'desa' => ['nullable', 'integer'],
            'jenis' => ['nullable', 'string', 'in:'.implode(',', array_keys(Kesenian::JENIS))],
            'status' => ['nullable', 'in:'.implode(',', array_keys(Kesenian::STATUS))],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $daftar = Kesenian::with(['kecamatan', 'desa'])->filterCetak($filter)->orderBy('kode_jenis')->orderBy('nomor_urut')->get();

        // Baris keterangan di bawah judul, contoh: "2.1 Seni Musik - ... - Status Aktif - Jember Kidul - Kaliwates - Kabupaten Jember".
        $keterangan = collect([
            ! empty($filter['jenis']) ? $filter['jenis'].' '.Kesenian::JENIS[$filter['jenis']] : null,
            ! empty($filter['status']) ? 'Status '.Kesenian::STATUS[$filter['status']] : null,
            ! empty($filter['desa']) ? Desa::find($filter['desa'])?->nama : null,
            ! empty($filter['kecamatan']) ? Kecamatan::find($filter['kecamatan'])?->nama : null,
            'Kabupaten Jember',
        ])->filter()->implode(' - ');

        $pdf = Pdf::loadView('pdf.data-kesenian', [
            'daftar' => $daftar,
            'tahun' => $filter['tahun'] ?? now()->year,
            'keterangan' => Str::upper($keterangan),
            'pejabat' => config('site.pejabat.kabid_kebudayaan'),
            'kop' => config('site.kop'),
            'tanggal' => now()->locale('id')->translatedFormat('j F Y'),
        ])->setPaper('a4', 'landscape');

        $nama = 'data-kesenian-'.now()->format('Ymd-His').'.pdf';

        return $request->boolean('unduh') ? $pdf->download($nama) : $pdf->stream($nama);
    }

    /** Kartu Induk Kesenian 8,5 x 5,5 cm: halaman 1 sisi depan (data), halaman 2 sisi belakang (foto, masa berlaku, TTD). */
    public function kartuKik(PerpanjanganKik $perpanjangan): Response
    {
        $perpanjangan->load('kesenian.kecamatan', 'kesenian.desa');

        $pdf = Pdf::loadView('pdf.kartu-kik', [
            'p' => $perpanjangan,
            'k' => $perpanjangan->kesenian,
            'pejabat' => config('site.pejabat.kepala_dinas'),
            'kop' => config('site.kop'),
            'gambar' => [
                'ilustrasi' => $this->dataUri(public_path('images/kik/ilustrasi.jpg')),
                'lambang' => $this->dataUri(public_path('images/kik/lambang.jpg')),
                'banner' => $this->dataUri(public_path('images/kik/banner.jpg')),
                'foto' => $this->dataUri(storage_path('app/public/'.$perpanjangan->foto_ketua)),
            ],
        ])->setPaper(self::UKURAN_KARTU);

        return $pdf->download('kik-'.Str::slug($perpanjangan->kesenian->nomor_induk.' '.$perpanjangan->kesenian->nama_kesenian).'.pdf');
    }

    /** Gambar disisipkan sebagai data URI supaya dompdf tidak perlu mengakses file/URL dari luar (chroot). */
    private function dataUri(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        return 'data:'.(mime_content_type($path) ?: 'image/jpeg').';base64,'.base64_encode((string) file_get_contents($path));
    }
}
