<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Data Kesenian Tahun {{ $tahun }}</title>
<style>
    @page { margin: 28pt 28pt 36pt; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 7.5pt; color: #111; }
    .judul { text-align: center; margin-bottom: 12pt; }
    .judul .dinas { font-size: 11pt; font-weight: bold; text-transform: uppercase; }
    .judul .sub { font-size: 9pt; font-weight: bold; }
    .judul .ket { font-size: 7.5pt; font-weight: bold; margin-top: 2pt; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #eee; font-weight: bold; }
    table.data th, table.data td { border: 0.5pt solid #999; padding: 3pt 4pt; vertical-align: top; }
    table.data td.n, table.data th.n { text-align: center; }
    .ttd { width: 220pt; margin-left: auto; margin-top: 24pt; text-align: center; page-break-inside: avoid; }
    .ttd .nama { margin-top: 48pt; font-weight: bold; text-decoration: underline; }
    .kosong { text-align: center; padding: 16pt; color: #666; }
    .kaki { position: fixed; bottom: -22pt; left: 0; right: 0; font-size: 6.5pt; color: #666; }
</style>
</head>
<body>
    <div class="kaki">Diunduh {{ now()->locale('id')->translatedFormat('j F Y, H:i') }} WIB · {{ count($daftar) }} data</div>

    <div class="judul">
        <div class="dinas">{{ $kop['dinas'] }} Kabupaten Jember</div>
        <div class="sub">DATA KESENIAN TAHUN {{ $tahun }}</div>
        <div class="ket">{{ $keterangan }}</div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th class="n">No</th>
                <th>Nomor Induk</th>
                <th>Nama Kesenian</th>
                <th>Nama Pemilik</th>
                <th>Jenis Kesenian</th>
                <th>Alamat</th>
                <th>Kabupaten</th>
                <th>Kecamatan</th>
                <th>Desa</th>
                <th class="n">Anggota</th>
                <th>Telp</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftar as $i => $k)
                <tr>
                    <td class="n">{{ $i + 1 }}</td>
                    <td>{{ $k->nomor_induk }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($k->nama_kesenian) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($k->nama_pemilik) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($k->jenis_lengkap) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($k->alamat) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($k->kabupaten) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($k->kecamatan->nama ?? '-') }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($k->desa->nama ?? '-') }}</td>
                    <td class="n">{{ $k->jumlah_anggota }}</td>
                    <td>{{ $k->no_telepon ?? '-' }}</td>
                    <td>{{ $k->status_label }}</td>
                </tr>
            @empty
                <tr><td colspan="12" class="kosong">Tidak ada data kesenian yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd">
        <div>Jember, {{ $tanggal }}</div>
        <div>{{ $pejabat['jabatan'] }}</div>
        <div class="nama">{{ $pejabat['nama'] ?: '..............................................' }}</div>
        <div>NIP. {{ $pejabat['nip'] ?: '..............................' }}</div>
    </div>
</body>
</html>
