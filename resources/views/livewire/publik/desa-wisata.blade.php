<div>
<div class="page"><h1>Desa Wisata</h1><p>Desa wisata Kabupaten Jember yang ditetapkan dengan Surat Keputusan (SK) Bupati.</p>

<div class="news">
@forelse ($daftar as $w)
    <a class="c kartu-berita" wire:key="desa-{{ $w->id }}" href="{{ route('publik.detail-wisata', $w) }}" wire:navigate>
        <div class="ph s" style="background:{{ $w->gambar_url ? 'url('.$w->gambar_url.') center/cover no-repeat' : $w->gradien }}">@unless ($w->gambar_url)🏡@endunless</div>
        <div class="cb">
            <span class="bd">{{ $w->nomor_sk_desa_wisata ? 'SK Bupati '.$w->nomor_sk_desa_wisata : 'Desa wisata' }}</span>
            <b>{{ $w->nama }}</b>
            <small><i class="bi bi-geo-alt" aria-hidden="true"></i> Kec. {{ $w->kecamatan?->nama }}</small>
            <p class="cuplikan">{{ $w->deskripsi_singkat }}</p>
            <span class="selengkapnya">Selengkapnya <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </div>
    </a>
@empty
    <p class="kosong-berita">Belum ada desa wisata. Admin dapat menambahkannya lewat menu Wisata dengan kategori "Desa wisata".</p>
@endforelse
</div>
</div>
</div>
