<div>
<div class="page"><h1>E-Booklet Wisata</h1><p>Panduan wisata Jember terbaru untuk memudahkan wisatawan saat berkunjung ke Jember.</p>

@if (filled($linkEbooklet))
    <div style="max-width:800px;margin:40px auto;text-align:center">
        @if (filled($gambarEbooklet))
            <div style="margin-bottom:24px">
                <img src="{{ Str::startsWith($gambarEbooklet, ['http://', 'https://']) ? $gambarEbooklet : asset('images/' . $gambarEbooklet) }}" 
                     alt="Cover E-Booklet Wisata Jember" 
                     style="max-width:100%;height:auto;max-height:500px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15)">
            </div>
        @else
            <div style="margin-bottom:24px">
                <i class="bi bi-book" aria-hidden="true" style="font-size:80px;color:var(--primary)"></i>
            </div>
        @endif
        
        <h2 style="margin-bottom:12px">E-Booklet Wisata Jember</h2>
        <p style="margin-bottom:24px;color:var(--mut);font-size:15px;line-height:1.6">
            Panduan lengkap destinasi wisata, kuliner, akomodasi, dan informasi praktis untuk wisatawan yang berkunjung ke Kabupaten Jember.
        </p>
        <a class="btn p" href="{{ $linkEbooklet }}" target="_blank" rel="noopener" style="font-size:16px;padding:14px 32px">
            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Baca Selengkapnya
        </a>
    </div>
@else
    <p class="kosong-berita">E-booklet sedang dalam proses pembuatan.</p>
@endif
</div>
</div>
