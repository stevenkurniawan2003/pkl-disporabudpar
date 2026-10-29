<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Data Pelaku Ekonomi Kreatif Jember</title>
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
    table.data td.r { text-align: right; white-space: nowrap; }
    .ttd { width: 260pt; margin-left: auto; margin-top: 24pt; text-align: center; page-break-inside: avoid; }
    .ttd .nama { margin-top: 48pt; font-weight: bold; text-decoration: underline; }
    .kosong { text-align: center; padding: 16pt; color: #666; }
    .kaki { position: fixed; bottom: -22pt; left: 0; right: 0; font-size: 6.5pt; color: #666; }
</style>
</head>
<body>
    <div class="kaki">Diunduh {{ now()->locale('id')->translatedFormat('j F Y, H:i') }} WIB · {{ count($daftar) }} data</div>

    <div class="judul">
        <div class="dinas">{{ $kop['dinas'] }} Kabupaten Jember</div>
        <div class="sub">DATA PELAKU EKONOMI KREATIF JEMBER</div>
        <div class="ket">{{ $keterangan }}</div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th class="n">No</th>
                <th>ID Usaha/Pelaku</th>
                <th>Nama Usaha/Pelaku</th>
                <th>Subsektor</th>
                <th>Alamat</th>
                <th>Kabupaten</th>
                <th>Kecamatan</th>
                <th>Desa</th>
                <th class="n">Omzet (Rp)</th>
                <th>Telp</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftar as $i => $e)
                <tr>
                    <td class="n">{{ $i + 1 }}</td>
                    <td>{{ $e->nomor_ekraf }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($e->nama_usaha) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($e->subsektor_lengkap) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($e->alamat) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($e->kabupaten) }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($e->kecamatan->nama ?? '-') }}</td>
                    <td>{{ \Illuminate\Support\Str::upper($e->desa->nama ?? '-') }}</td>
                    <td class="r">{{ number_format($e->omzet, 0, ',', '.') }}</td>
                    <td>{{ $e->no_telepon ?? '-' }}</td>
                    <td>{{ $e->status_label }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="kosong">Tidak ada data pelaku ekraf yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd">
        <div>Jember, {{ $tanggal }}</div>
        <div>{{ $pejabat['jabatan'] }}</div>
        <div class="nama">{{ $pejabat['nama'] }}</div>
        <div>NIP. {{ $pejabat['nip'] }}</div>
    </div>
</body>
</html>
