@php
    $angka = fn ($n) => number_format((int) $n, 0, ',', '.');

    // Isi slider hero (statis, sesuai desain).
    $slide = [
        ['Jember, Miniatur Indonesia', 'Pantai, pegunungan, desa wisata, dan budaya dalam satu kabupaten.', 'Jelajahi destinasi', route('publik.peta-wisata'), 'linear-gradient(135deg,#E8487F,#F7A1C0)'],
        ['Festival Hyang Argopuro', 'Rangkaian tradisi dan ekonomi lokal di Arjasa.', 'Baca beritanya', route('publik.berita'), 'linear-gradient(135deg,#C93A74,#F28BB0)'],
        ['Kartu Induk Kesenian', 'Data kelompok kesenian Jember yang terdaftar resmi.', 'Lihat data kesenian', route('publik.kebudayaan'), 'linear-gradient(135deg,#F06A9A,#FBC4D8)'],
    ];

    // Bidang dan UPTD (statis, sesuai desain). Isian: [ikon, nama, jenis, uptd?]
    $bidang = [
        ['🧭', 'Pariwisata', 'Bidang', false],
        ['🎨', 'Ekonomi Kreatif', 'Bidang', false],
        ['🎭', 'Kebudayaan', 'Bidang', false],
        ['🏅', 'Peningkatan Prestasi Olahraga', 'Bidang', false],
        ['🌱', 'Kepemudaan', 'Bidang', false],
        ['🏞', 'UPTD Wisata Terpadu', 'UPTD', true],
        ['🏟', 'UPTD Olahraga', 'UPTD', true],
    ];

    // "Januari–September 2026" sesuai bulan terakhir yang sudah ada datanya.
    $periode = fn (?array $p) => $p
        ? ($p['bulan_akhir'] > 1 ? 'Januari–'.$namaBulan[$p['bulan_akhir']] : 'Januari').' '.$p['tahun']
        : now()->year;
@endphp
<div>
<div class="slider" id="sl">
    @foreach ($slide as $i => [$judul, $teks, $tombol, $tautan, $warna])
        <div class="sl {{ $i === 0 ? 'on' : '' }}" style="background:{{ $warna }}">
            <svg viewBox="0 0 200 200" aria-hidden="true"><circle cx="100" cy="100" r="90" fill="none" stroke="#fff" stroke-width="3"></circle><circle cx="100" cy="100" r="60" fill="none" stroke="#fff" stroke-width="3"></circle><circle cx="100" cy="100" r="30" fill="#fff"></circle></svg>
            @if ($i === 0)<h1>{{ $judul }}</h1>@else<h2 class="sl-judul">{{ $judul }}</h2>@endif
            <p>{{ $teks }}</p>
            <a class="btn" href="{{ $tautan }}" wire:navigate>{{ $tombol }}</a>
        </div>
    @endforeach
    <div class="dots">
        @foreach ($slide as $i => $s)
            <button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-s="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
    </div>
</div>

<section class="sec" aria-labelledby="judul-unggulan">
    <div class="sh">
        <div>
            <h2 id="judul-unggulan">Destinasi unggulan</h2>
            <p>{{ $unggulanCadangan ? 'Destinasi wisata terbaru di Kabupaten Jember' : 'Pilihan tempat yang paling sering dikunjungi' }}</p>
        </div>
        <a class="sh-link" href="{{ route('publik.peta-wisata') }}" wire:navigate>Lihat peta wisata</a>
    </div>
    <div class="g3">
        @forelse ($unggulan as $d)
            <a class="c" wire:key="unggulan-{{ $d->id }}" href="{{ route('publik.detail-wisata', $d) }}" wire:navigate>
                <div class="ph" style="background:{{ $d->gambar_url ? 'url('.$d->gambar_url.') center/cover no-repeat' : $d->gradien }}">@unless ($d->gambar_url){{ $d->kategori?->ikon }}@endunless</div>
                <div class="cb">
                    <span class="bd">{{ $d->kategori?->nama }}</span>
                    <b>{{ $d->nama }}</b>
                    <small>Kec. {{ $d->kecamatan?->nama }}</small>
                </div>
            </a>
        @empty
            <p style="color:var(--mut)">Belum ada destinasi wisata.</p>
        @endforelse
    </div>
</section>

<section class="sec" aria-labelledby="judul-bidang">
    <div class="sh">
        <div>
            <h2 id="judul-bidang">Bidang dan UPTD</h2>
            <p>Unit kerja yang melayani kepemudaan, olahraga, kebudayaan, dan pariwisata</p>
        </div>
    </div>
    <div class="bidang">
        @foreach ($bidang as [$ikon, $nama, $jenis, $uptd])
            <div class="bidang-item {{ $uptd ? 'u' : '' }}"><i aria-hidden="true">{{ $ikon }}</i><div><b>{{ $nama }}</b><small>{{ $jenis }}</small></div></div>
        @endforeach
    </div>
</section>

<section class="sec" aria-labelledby="judul-berita">
    <div class="sh">
        <div><h2 id="judul-berita">Berita terbaru</h2></div>
        <a class="sh-link" href="{{ route('publik.berita') }}" wire:navigate>Semua berita</a>
    </div>
    <div class="news">
        @forelse ($berita as $b)
            @include('livewire.publik.partials.kartu-berita', ['b' => $b])
        @empty
            <p style="color:var(--mut)">Belum ada berita.</p>
        @endforelse
    </div>
</section>

<section class="sec" aria-label="Ringkasan data">
    <div class="band">
        <div>
            <small>Kunjungan wisatawan, {{ $periode($kunjungan['wisata']) }}</small>
            <b>{{ $angka($kunjungan['wisata']['total'] ?? 0) }}</b>
            <a href="{{ route('publik.kunjungan-wisata', $kunjungan['wisata'] ? ['tahun' => $kunjungan['wisata']['tahun']] : []) }}" wire:navigate>Lihat rincian</a>
        </div>
        <div>
            <small>Kunjungan hotel, {{ $periode($kunjungan['hotel']) }}</small>
            <b>{{ $angka($kunjungan['hotel']['total'] ?? 0) }}</b>
            <a href="{{ route('publik.kunjungan-hotel', $kunjungan['hotel'] ? ['tahun' => $kunjungan['hotel']['tahun']] : []) }}" wire:navigate>Lihat rincian</a>
        </div>
        <div>
            <small>Layanan</small>
            <p style="margin:4px 0 0"><a class="chip" href="{{ route('publik.kebudayaan') }}" wire:navigate>Kartu induk kesenian</a> <a class="chip" href="{{ route('publik.ekonomi-kreatif') }}" wire:navigate>Data ekraf</a></p>
        </div>
    </div>
</section>
</div>
