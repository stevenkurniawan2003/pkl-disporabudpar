{{-- Kartu berita, dipakai di halaman Berita dan Beranda. Parameter: $b (Berita), $cuplikan (bool, opsional). --}}
<a class="c" wire:key="berita-{{ $b->id }}" href="{{ route('publik.detail-berita', $b) }}" wire:navigate>
    <div class="ph s" style="background:{{ $b->gambar_url ? 'url('.$b->gambar_url.') center/cover no-repeat' : $b->gradien }}">@unless ($b->gambar_url)📰@endunless</div>
    <div class="cb">
        <span class="bd">{{ $b->kategori?->nama }}</span>
        <b>{{ $b->judul }}</b>
        <small>{{ $b->tanggal_teks }} · dibaca {{ number_format($b->jumlah_dibaca, 0, ',', '.') }} kali</small>
        @if ($cuplikan ?? false)
            <p class="cuplikan">{{ $b->ringkasan_tampil }}</p>
        @endif
    </div>
</a>
