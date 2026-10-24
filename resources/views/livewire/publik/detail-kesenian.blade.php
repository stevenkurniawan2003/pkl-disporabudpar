<div>
<div class="page art">
    <a class="chip" href="{{ route('publik.kebudayaan') }}" wire:navigate>← Kebudayaan</a>

    <div class="kesenian-sampul" style="background:{{ $kesenian->gradien }}">
        <span class="kesenian-ikon" aria-hidden="true">{{ $kesenian->ikon }}</span>
    </div>

    <span class="bd">{{ $kesenian->kategori }}</span>
    <h1 style="margin-top:10px;line-height:1.2">{{ $kesenian->nama_kesenian }}</h1>
    @if ($kesenian->asal_daerah)
        <p class="meta"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $kesenian->asal_daerah }}</p>
    @endif

    <div class="kesenian-isi">
        @foreach ($kesenian->paragraf as $p)
            <p>{!! nl2br(e($p)) !!}</p>
        @endforeach
    </div>

    @if ($serupa->isNotEmpty())
        <h2 class="judul-tabel">Kesenian {{ \Illuminate\Support\Str::lower($kesenian->kategori) }} lainnya</h2>
        <div class="serupa">
            @foreach ($serupa as $s)
                <a class="bl" wire:key="serupa-{{ $s->id_kesenian }}" href="{{ route('publik.detail-kesenian', $s) }}" wire:navigate>
                    <span class="bl-ph" style="background:{{ $s->gradien }}" aria-hidden="true">{{ $s->ikon }}</span>
                    <span class="bl-teks"><b>{{ $s->nama_kesenian }}</b><small>{{ $s->asal_daerah ?? $s->kategori }}</small></span>
                </a>
            @endforeach
        </div>
    @endif
</div>
</div>
