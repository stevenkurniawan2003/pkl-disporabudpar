@php $angka = fn ($n) => number_format((int) $n, 0, ',', '.'); @endphp
<div>
<div class="page"><h1>Ringkasan Data Kebudayaan</h1><p>Rincian data kesenian, Objek Pemajuan Kebudayaan, dan cagar budaya Kabupaten Jember.</p>

<h2 class="judul-tabel" style="margin-top:0">Rincian data kesenian</h2>
<div class="ringkas empat" aria-label="Rincian data kesenian">
    @foreach ($perKelompok as $k)
        <a class="ringkas-kartu" href="{{ route('publik.data-kesenian') }}" wire:navigate wire:key="kelompok-{{ $loop->index }}"><small>{{ $k['nama'] }}</small><b>{{ $angka($k['jumlah']) }}</b><span>kelompok kesenian</span></a>
    @endforeach
    <a class="ringkas-kartu utama" href="{{ route('publik.data-kesenian') }}" wire:navigate><small>Total data master</small><b>{{ $angka($totalKesenian) }}</b><span>{{ $angka($totalAktif) }} berstatus aktif</span></a>
</div>
<div class="ringkas" aria-label="Objek pemajuan kebudayaan dan cagar budaya">
    <a class="ringkas-kartu" href="{{ route('publik.opk') }}" wire:navigate><small>Objek Pemajuan Kebudayaan</small><b>{{ $angka($totalOpk) }}</b><span>10 jenis objek</span></a>
    <a class="ringkas-kartu" href="{{ route('publik.cagar-budaya') }}" wire:navigate><small>ODCB &amp; Cagar Budaya</small><b>{{ $angka($totalCagar) }}</b><span>benda, bangunan, struktur, kawasan, situs</span></a>
    <a class="ringkas-kartu" href="{{ route('publik.data-kesenian') }}" wire:navigate><small>Kartu Induk Kesenian</small><b>{{ $angka($totalAktif) }}</b><span>sanggar/komunitas aktif</span></a>
</div>

<div class="grafik-daftar">
    @include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])
</div>

<div class="tools" style="margin-top:20px">
    <a class="btn p" href="{{ route('publik.data-kesenian') }}" wire:navigate>Lihat rincian data kesenian</a>
    <a class="btn" href="{{ route('publik.opk') }}" wire:navigate>Lihat Objek Pemajuan Kebudayaan</a>
    <a class="btn" href="{{ route('publik.cagar-budaya') }}" wire:navigate>Lihat cagar budaya</a>
</div>
</div>
</div>
