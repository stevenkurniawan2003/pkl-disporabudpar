@php $angka = fn ($n) => number_format((int) $n, 0, ',', '.'); @endphp
<div>
<div class="page"><h1>Data Ekonomi Kreatif</h1><p>Infografis jumlah pelaku ekonomi kreatif Kabupaten Jember per subsektor dan per kecamatan.</p>

<div class="tools">
    <a class="btn p" href="{{ route('publik.pencarian-ekraf') }}" wire:navigate>Lihat Rincian</a>
</div>

<div class="ringkas dua" aria-label="Jumlah pelaku ekonomi kreatif">
    <div class="ringkas-kartu aktif"><small>Jumlah nama usaha/pelaku ekonomi kreatif aktif</small><b>{{ $angka($jumlahAktif) }}</b><span>pelaku</span></div>
    <div class="ringkas-kartu nonaktif"><small>Jumlah nama usaha/pelaku ekonomi kreatif non aktif</small><b>{{ $angka($jumlahNonaktif) }}</b><span>pelaku</span></div>
</div>

<div class="grafik-daftar">
    @include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])
</div>
</div>
</div>
