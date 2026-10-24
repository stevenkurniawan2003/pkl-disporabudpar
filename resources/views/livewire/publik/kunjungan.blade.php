{{-- Dipakai bersama oleh halaman publik Data Kunjungan Wisata dan Data Kunjungan Hotel (lihat HalamanKunjungan). --}}
@php
    $angka = fn ($n) => number_format((int) $n, 0, ',', '.');
    $namaPilihan = $pilihan ? $daftarPilihan->firstWhere('id', $pilihan)?->nama : null;
    $cakupan = ($namaPilihan ?? $teks['semua']).', '.$tahun;
@endphp
<div>
<div class="page"><h1>{{ $teks['judul'] }}</h1><p>{{ $teks['deskripsi'] }}</p>

<div class="tools">
    <select wire:model.live="tahun" aria-label="Pilih tahun">
        @forelse ($daftarTahun as $t)
            <option value="{{ $t }}">{{ $t }}</option>
        @empty
            <option value="{{ $tahun }}">{{ $tahun }}</option>
        @endforelse
    </select>
    <select wire:model.live="pilihan" aria-label="Pilih {{ \Illuminate\Support\Str::lower($teks['kolom']) }}" class="pilih-lebar">
        <option value="0">{{ $teks['semua'] }}</option>
        @foreach ($daftarPilihan as $p)
            <option value="{{ $p->id }}">{{ $p->nama }}</option>
        @endforeach
    </select>
</div>

<div class="ringkas" aria-label="Ringkasan kunjungan {{ $tahun }}">
    <div class="ringkas-kartu"><small>{{ $teks['orang'] }} domestik</small><b>{{ $angka($totalDomestik) }}</b><span>{{ $cakupan }}</span></div>
    <div class="ringkas-kartu"><small>{{ $teks['orang'] }} mancanegara</small><b>{{ $angka($totalMancanegara) }}</b><span>{{ $cakupan }}</span></div>
    <div class="ringkas-kartu utama"><small>Total kunjungan</small><b>{{ $angka($totalDomestik + $totalMancanegara) }}</b><span>domestik + mancanegara</span></div>
</div>

@if ($totalDomestik + $totalMancanegara === 0)
    <p class="info-kosong">Belum ada data kunjungan{{ $namaPilihan ? ' untuk '.$namaPilihan : '' }} pada tahun {{ $tahun }}.</p>
@endif

@include('livewire.publik.partials.grafik-kunjungan', ['judulGrafik' => 'Kunjungan per bulan, '.$tahun])

<h2 class="judul-tabel">Rincian per bulan</h2>
<div class="tw">
    <table>
        <thead><tr><th>Bulan</th><th class="n">Domestik</th><th class="n">Mancanegara</th><th class="n">Total</th></tr></thead>
        <tbody>
            @foreach ($grafik['nama_bulan'] as $i => $nama)
                <tr wire:key="bulan-{{ $tahun }}-{{ $pilihan }}-{{ $i }}">
                    <td>{{ $nama }}</td>
                    <td class="n">{{ $angka($grafik['domestik'][$i]) }}</td>
                    <td class="n">{{ $angka($grafik['mancanegara'][$i]) }}</td>
                    <td class="n">{{ $angka($grafik['domestik'][$i] + $grafik['mancanegara'][$i]) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot><tr><td>Total</td><td class="n">{{ $angka($totalDomestik) }}</td><td class="n">{{ $angka($totalMancanegara) }}</td><td class="n">{{ $angka($totalDomestik + $totalMancanegara) }}</td></tr></tfoot>
    </table>
</div>

<h2 class="judul-tabel">Rekap per {{ \Illuminate\Support\Str::lower($teks['kolom']) }}, {{ $tahun }}</h2>
<div class="tw">
    <table>
        <thead><tr><th class="no">No</th><th>{{ $teks['kolom'] }}</th><th class="n">Domestik</th><th class="n">Mancanegara</th><th class="n">Total</th></tr></thead>
        <tbody>
            @forelse ($rekap as $i => $r)
                <tr wire:key="rekap-{{ $tahun }}-{{ md5($r->url) }}">
                    <td class="no">{{ $i + 1 }}</td>
                    <td><a href="{{ $r->url }}" wire:navigate>{{ $r->nama }}</a></td>
                    <td class="n">{{ $angka($r->domestik) }}</td>
                    <td class="n">{{ $angka($r->mancanegara) }}</td>
                    <td class="n"><b>{{ $angka($r->total) }}</b></td>
                </tr>
            @empty
                <tr><td colspan="5" class="kosong-sel">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
</div>
