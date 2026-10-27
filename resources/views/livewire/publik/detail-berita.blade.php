<div>
<div class="page detail-berita">
    <article class="art">
        <a class="chip" href="{{ route('publik.berita') }}" wire:navigate>← Semua berita</a>

        @if ($berita->status === 'draf' || $berita->terjadwal)
            <p style="margin-top:14px;padding:10px 14px;border:1px solid var(--line);border-radius:10px;background:var(--pink2);color:var(--pinkink);font-weight:600">Pratinjau admin: berita ini {{ $berita->status === 'draf' ? 'masih draf' : 'terjadwal' }} dan belum tampil di publik.</p>
        @endif

        <h1 style="margin-top:16px;line-height:1.2">{{ $berita->judul }}</h1>
        <p class="meta">
            {{ $berita->tanggal_teks }}{{ $berita->tanggal_terbit ? ' · ' : '' }}{{ $berita->kategori?->nama }}
            @if ($berita->penulis) · oleh {{ $berita->penulis->name }} @endif
            · dibaca {{ number_format($berita->jumlah_dibaca, 0, ',', '.') }} kali
        </p>

        <div class="ph" style="background:{{ $berita->gambar_url ? 'url('.$berita->gambar_url.') center/cover no-repeat' : $berita->gradien }}" @if ($berita->gambar_url) role="img" aria-label="Gambar sampul: {{ $berita->judul }}" @endif>@unless ($berita->gambar_url)📰@endunless</div>

        {{-- Isi dari editor admin (TinyMCE), sudah dibersihkan HTML Purifier (lihat App\Support\TeksKaya). --}}
        <div class="isi-kaya">{!! $berita->isi_html !!}</div>

        @if ($galeri)
            <section class="mini-galeri" aria-labelledby="judul-mini-galeri">
                <h2 id="judul-mini-galeri">Galeri foto <small>({{ count($galeri) }})</small></h2>
                <div class="dd-galeri">
                    @foreach ($galeri as $i => $foto)
                        <button type="button" class="dd-foto" data-i="{{ $i }}" aria-label="Lihat foto {{ $i + 1 }}">
                            <img src="{{ $foto['url'] }}" alt="Foto {{ $i + 1 }} {{ $berita->judul }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
            </section>
            @include('livewire.publik.partials.lightbox', ['nama' => $berita->judul])
        @endif
    </article>

    @if ($lainnya->isNotEmpty())
        <aside class="berita-lainnya" aria-labelledby="judul-berita-lainnya">
            <h2 id="judul-berita-lainnya">Berita Lainnya</h2>
            @foreach ($lainnya as $b)
                <a class="bl" wire:key="lainnya-{{ $b->id }}" href="{{ route('publik.detail-berita', $b) }}" wire:navigate>
                    <span class="bl-ph" style="background:{{ $b->gambar_url ? 'url('.$b->gambar_url.') center/cover no-repeat' : $b->gradien }}">@unless ($b->gambar_url)📰@endunless</span>
                    <span class="bl-teks">
                        <b>{{ $b->judul }}</b>
                        <small>{{ $b->tanggal_teks }}{{ $b->kategori ? ' · '.$b->kategori->nama : '' }}</small>
                    </span>
                </a>
            @endforeach
        </aside>
    @endif
</div>
</div>
