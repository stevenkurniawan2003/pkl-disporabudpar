<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ekspor tabel ke PDF (dompdf) atau Excel .xlsx (OpenSpout), keduanya dengan kolom tanda tangan pejabat
 * di bawah tabel. Dipakai menu "Extract" Database Atlet/Pelatih/Cabor dan Data Atlet Berprestasi (PPO).
 */
class EksporTabel
{
    /**
     * @param  list<string>  $kolom  judul kolom (tanpa kolom No; nomor urut ditambahkan otomatis)
     * @param  list<list<string|int|float|null>>  $baris
     * @param  array{jabatan: string, nama: string, nip: string}  $pejabat
     */
    public function __construct(
        public string $judul,
        public string $keterangan,
        public array $kolom,
        public array $baris,
        public array $pejabat,
    ) {}

    /** $unduh = false -> PDF dibuka di tab browser (untuk dicetak). */
    public function pdf(string $namaFile, bool $unduh = true): Response
    {
        $pdf = Pdf::loadView('pdf.tabel', [
            'judul' => $this->judul,
            'keterangan' => $this->keterangan,
            'kolom' => $this->kolom,
            'baris' => $this->baris,
            'pejabat' => $this->pejabat,
            'kop' => config('site.kop'),
            'tanggal' => now()->locale('id')->translatedFormat('j F Y'),
        ])->setPaper('a4', 'landscape');

        return $unduh ? $pdf->download($namaFile.'.pdf') : $pdf->stream($namaFile.'.pdf');
    }

    public function xlsx(string $namaFile): Response
    {
        return response()->streamDownload(function () {
            $tebal = new Style(fontBold: true);
            $kolomTtd = count($this->kolom); // kolom terakhir (indeks 0 = No)

            $options = new Options;
            $options->setColumnWidth(6, 1);
            $options->setColumnWidthForRange(22, 2, $kolomTtd + 1);

            $writer = new Writer($options);
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValuesWithStyle([mb_strtoupper(config('site.kop.dinas').' Kabupaten Jember')], $tebal));
            $writer->addRow(Row::fromValuesWithStyle([$this->judul], $tebal));
            $writer->addRow(Row::fromValues([$this->keterangan]));
            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValuesWithStyle(['No', ...$this->kolom], $tebal));

            foreach ($this->baris as $i => $isi) {
                $writer->addRow(Row::fromValues([$i + 1, ...array_map(fn ($v) => $v ?? '', $isi)]));
            }

            // Kolom tanda tangan di kolom paling kanan.
            $ttd = fn (string $teks, bool $bold = false) => Row::fromValuesWithStyle([...array_fill(0, $kolomTtd, ''), $teks], $bold ? $tebal : new Style);
            $writer->addRow(Row::fromValues([]));
            $writer->addRow($ttd('Jember, '.now()->locale('id')->translatedFormat('j F Y')));
            $writer->addRow($ttd($this->pejabat['jabatan']));
            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues([]));
            $writer->addRow($ttd($this->pejabat['nama'] ?: '..............................', true));
            $writer->addRow($ttd('NIP. '.($this->pejabat['nip'] ?: '....................')));

            $writer->close();
        }, $namaFile.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
