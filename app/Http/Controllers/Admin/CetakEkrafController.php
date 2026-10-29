<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Ekraf;
use App\Models\Kecamatan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/** PDF Data Master EKRAF sesuai filter menu CETAK, bertanda tangan Kepala Dinas (dokumen konsep Ekraf poin 12). */
class CetakEkrafController extends Controller
{
    /** ?unduh=1 -> unduh file, selain itu dibuka di tab (untuk langsung dicetak). */
    public function __invoke(Request $request): Response
    {
        $filter = $request->validate([
            'kecamatan' => ['nullable', 'integer'],
            'desa' => ['nullable', 'integer'],
            'subsektor' => ['nullable', 'string', 'in:'.implode(',', array_keys(Ekraf::SUBSEKTOR))],
            'status' => ['nullable', 'in:'.implode(',', array_keys(Ekraf::STATUS))],
        ]);

        $daftar = Ekraf::with(['kecamatan', 'desa'])->filter($filter)->orderBy('kode_subsektor')->orderBy('nomor_urut')->get();

        // Baris keterangan di bawah judul, contoh: "1.8 Kriya - Status Aktif - Tegalwaru - Mayang - Kabupaten Jember".
        $keterangan = collect([
            ! empty($filter['subsektor']) ? $filter['subsektor'].' '.Ekraf::SUBSEKTOR[$filter['subsektor']] : null,
            ! empty($filter['status']) ? 'Status '.Ekraf::STATUS[$filter['status']] : null,
            ! empty($filter['desa']) ? Desa::find($filter['desa'])?->nama : null,
            ! empty($filter['kecamatan']) ? Kecamatan::find($filter['kecamatan'])?->nama : null,
            'Kabupaten Jember',
        ])->filter()->implode(' - ');

        $pdf = Pdf::loadView('pdf.data-ekraf', [
            'daftar' => $daftar,
            'keterangan' => Str::upper($keterangan),
            'pejabat' => config('site.pejabat.kepala_dinas'),
            'kop' => config('site.kop'),
            'tanggal' => now()->locale('id')->translatedFormat('j F Y'),
        ])->setPaper('a4', 'landscape');

        $nama = 'data-ekraf-'.now()->format('Ymd-His').'.pdf';

        return $request->boolean('unduh') ? $pdf->download($nama) : $pdf->stream($nama);
    }
}
