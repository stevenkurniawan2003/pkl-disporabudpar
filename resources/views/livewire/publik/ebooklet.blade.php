<div>
<div class="page"><h1>E-Booklet Wisata</h1><p>Panduan wisata Jember terbaru untuk memudahkan wisatawan saat berkunjung ke Jember.</p>

<div class="news">
@forelse ($daftar as $e)
    <article class="c kartu-berita" wire:key="ebooklet-{{ $e->id_ebooklet }}">
        <div class="ph s" style="background:{{ $e->sampul_url ? 'url('.$e->sampul_url.') center/cover no-repeat' : 'linear-gradient(135deg,#C93A74,#F28BB0)' }}">@unless ($e->sampul_url)<i class="bi bi-book" aria-hidden="true"></i>@endunless</div>
        <div class="cb">
            <span class="bd">{{ $e->tahun }}</span>
            <b>{{ $e->judul }}</b>
            @if ($e->deskripsi)<p class="cuplikan">{{ $e->deskripsi }}</p>@endif
            <a class="btn p" style="margin-top:12px;align-self:flex-start" href="{{ $e->pdf_url }}" target="_blank" rel="noopener" download><i class="bi bi-download" aria-hidden="true"></i> Unduh PDF</a>
        </div>
    </article>
@empty
    <p class="kosong-berita">Belum ada e-booklet.</p>
@endforelse
</div>
</div>
</div>
