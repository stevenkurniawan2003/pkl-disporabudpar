<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kartu Induk Kesenian {{ $k->nomor_induk }}</title>
<style>
    @page { margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #111; }
    .sisi { position: relative; width: 240.9pt; height: 155.9pt; overflow: hidden; page-break-after: always; }
    .sisi:last-child { page-break-after: auto; }

    /* Depan */
    .ilustrasi { position: absolute; left: 0; top: 0; width: 64pt; height: 155.9pt; }
    .lambang { position: absolute; left: 70pt; top: 7pt; width: 22pt; }
    .kop { position: absolute; left: 96pt; top: 6pt; right: 6pt; line-height: 1.15; }
    .kop .pemda { color: #D35400; font-size: 6pt; font-weight: bold; text-transform: uppercase; }
    .kop .dinas { color: #1F2A7A; font-size: 5pt; font-weight: bold; text-transform: uppercase; }
    .kop .alamat { font-size: 3.6pt; }
    .garis { position: absolute; left: 96pt; right: 6pt; top: 44pt; border-top: 1.2pt solid #111; }
    .judul { position: absolute; left: 70pt; right: 4pt; top: 50pt; color: #D35400; font-size: 6.4pt; font-weight: bold; text-transform: uppercase; }
    .data { position: absolute; left: 70pt; top: 63pt; width: 164pt; border-collapse: collapse; }
    .data td { font-size: 4.8pt; font-weight: bold; padding: 1.2pt 0; vertical-align: top; }
    .data td.l { width: 50pt; white-space: nowrap; }
    .data td.t { width: 5pt; }

    /* Belakang */
    .banner { position: absolute; left: 0; top: 0; width: 240.9pt; height: 24pt; }
    .foto { position: absolute; left: 12pt; top: 32pt; width: 36pt; height: 48pt; border: 0.6pt solid #111; border-radius: 3pt; }
    .foto-kosong { position: absolute; left: 12pt; top: 32pt; width: 36pt; height: 28pt; border: 0.6pt solid #111; border-radius: 3pt; font-size: 5pt; text-align: center; padding-top: 20pt; }
    .barcode { position: absolute; left: 9pt; top: 88pt; width: 42pt; height: 22pt; border: 0.6pt solid #111; border-radius: 3pt; font-size: 5.5pt; font-weight: bold; text-align: center; padding-top: 8pt; line-height: 1.1; }
    .berlaku { position: absolute; left: 60pt; top: 32pt; border-collapse: collapse; }
    .berlaku td { font-size: 5.4pt; font-weight: bold; padding: 0.8pt 0; white-space: nowrap; }
    .tempat { position: absolute; right: 12pt; top: 64pt; font-size: 5.4pt; font-weight: bold; }
    .ttd { position: absolute; left: 60pt; right: 8pt; top: 76pt; font-size: 4.4pt; line-height: 1.3; }
    .ttd .oleh { font-size: 4.8pt; margin-bottom: 1pt; }
    .ttd .jabatan { font-weight: bold; text-transform: uppercase; }
    .ttd .nama { font-weight: bold; text-decoration: underline; margin-top: 6pt; font-size: 5pt; }
    .ttd .nip { font-weight: bold; font-size: 5pt; }
</style>
</head>
<body>
    @php $tgl = fn ($d) => $d->format('d-m-Y'); @endphp

    <div class="sisi">
        @if ($gambar['ilustrasi'])<img class="ilustrasi" src="{{ $gambar['ilustrasi'] }}" alt="">@endif
        @if ($gambar['lambang'])<img class="lambang" src="{{ $gambar['lambang'] }}" alt="">@endif
        <div class="kop">
            <div class="pemda">{{ $kop['pemerintah'] }}</div>
            <div class="dinas">{{ $kop['dinas'] }}</div>
            <div class="alamat">{{ $kop['alamat'] }}</div>
            <div class="alamat">{{ $kop['alamat_2'] }}</div>
        </div>
        <div class="garis"></div>
        <div class="judul">Kartu Nomor Induk Organisasi Kesenian</div>
        <table class="data">
            <tr><td class="l">No. Induk</td><td class="t">:</td><td>{{ $k->nomor_induk }}</td></tr>
            <tr><td class="l">Nama Organisasi</td><td class="t">:</td><td>{{ \Illuminate\Support\Str::upper($k->nama_kesenian) }}</td></tr>
            <tr><td class="l">Jenis Kesenian</td><td class="t">:</td><td>{{ \Illuminate\Support\Str::upper($k->jenis_lengkap) }}</td></tr>
            <tr><td class="l">Nama Pimpinan</td><td class="t">:</td><td>{{ \Illuminate\Support\Str::upper($k->nama_pemilik) }}</td></tr>
            <tr><td class="l">Alamat</td><td class="t">:</td><td>{{ \Illuminate\Support\Str::upper($k->alamat_lengkap) }}</td></tr>
            <tr><td class="l">Jumlah Anggota</td><td class="t">:</td><td>{{ $k->jumlah_anggota }} Orang</td></tr>
        </table>
    </div>

    <div class="sisi">
        @if ($gambar['banner'])<img class="banner" src="{{ $gambar['banner'] }}" alt="">@endif
        @if ($gambar['foto'])
            <img class="foto" src="{{ $gambar['foto'] }}" alt="Foto ketua">
        @else
            <div class="foto-kosong">Foto Diri 3x4</div>
        @endif
        {{-- Tempat barcode TTE dari sistem tanda tangan elektronik resmi, ditempel saat penandatanganan. --}}
        <div class="barcode">Barcode<br>TTD</div>

        <table class="berlaku">
            <tr><td style="width:40pt;padding-right:3pt">Berlaku Dari</td><td>: {{ $tgl($p->tanggal_pengajuan) }}</td></tr>
            <tr><td>Sampai</td><td>: {{ $tgl($p->berlaku_sampai) }}</td></tr>
        </table>

        <div class="tempat">Jember, {{ $tgl($p->tanggal_pengajuan) }}</div>

        <div class="ttd">
            <div class="oleh">Di tandatangani secara elektronik oleh :</div>
            <div class="jabatan">{{ $pejabat['jabatan'] }}</div>
            <div class="nama">{{ $pejabat['nama'] }}</div>
            <div class="nip">NIP. {{ $pejabat['nip'] }}</div>
        </div>
    </div>
</body>
</html>
