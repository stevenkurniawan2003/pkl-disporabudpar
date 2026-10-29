<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{{ $judul }}</title>
<style>
    @page { margin: 28pt 28pt 36pt; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 7.5pt; color: #111; }
    .judul { text-align: center; margin-bottom: 12pt; }
    .judul .dinas { font-size: 11pt; font-weight: bold; text-transform: uppercase; }
    .judul .sub { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
    .judul .ket { font-size: 7.5pt; font-weight: bold; margin-top: 2pt; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #eee; font-weight: bold; }
    table.data th, table.data td { border: 0.5pt solid #999; padding: 3pt 4pt; vertical-align: top; }
    table.data .n { text-align: center; }
    .ttd { width: 260pt; margin-left: auto; margin-top: 24pt; text-align: center; page-break-inside: avoid; }
    .ttd .nama { margin-top: 48pt; font-weight: bold; text-decoration: underline; }
    .kosong { text-align: center; padding: 16pt; color: #666; }
    .kaki { position: fixed; bottom: -22pt; left: 0; right: 0; font-size: 6.5pt; color: #666; }
</style>
</head>
<body>
    <div class="kaki">Diunduh {{ now()->locale('id')->translatedFormat('j F Y, H:i') }} WIB · {{ count($baris) }} data</div>

    <div class="judul">
        <div class="dinas">{{ $kop['dinas'] }} Kabupaten Jember</div>
        <div class="sub">{{ $judul }}</div>
        <div class="ket">{{ $keterangan }}</div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th class="n">No</th>
                @foreach ($kolom as $k)
                    <th>{{ $k }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($baris as $i => $isi)
                <tr>
                    <td class="n">{{ $i + 1 }}</td>
                    @foreach ($isi as $nilai)
                        <td>{{ $nilai ?? '-' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($kolom) + 1 }}" class="kosong">Tidak ada data yang cocok dengan filter.</td></tr>
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
