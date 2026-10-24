<div>
<div class="page art">
    <a class="chip" href="{{ route('publik.ekonomi-kreatif') }}" wire:navigate>← Ekonomi kreatif</a>

    <div class="kesenian-sampul" style="background:{{ $ekraf->gradien }}">
        <span class="kesenian-ikon" aria-hidden="true">{{ $ekraf->ikon }}</span>
    </div>

    <span class="bd">{{ $ekraf->subsektor }}</span>
    <h1 style="margin-top:10px;line-height:1.2">{{ $ekraf->nama_usaha }}</h1>

    <ul class="dd-data info-ekraf">
        <li><i class="bi bi-person" aria-hidden="true"></i><span>Pemilik: {{ $ekraf->nama_pemilik }}</span></li>
        <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ $ekraf->alamat }}</span></li>
        @if ($ekraf->no_telepon)
            <li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $ekraf->no_telepon) }}">{{ $ekraf->no_telepon }}</a></li>
        @endif
    </ul>

    @if ($ekraf->platform_sosmed)
        <a class="btn p" href="{{ $ekraf->media_sosial }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-{{ $ekraf->platform_sosmed['ikon'] }}" aria-hidden="true"></i> Kunjungi {{ $ekraf->platform_sosmed['nama'] }}</a>
    @endif

    <h2 class="judul-tabel">Tentang usaha ini</h2>
    <div class="kesenian-isi">
        @foreach ($ekraf->paragraf as $p)
            <p>{!! nl2br(e($p)) !!}</p>
        @endforeach
    </div>

    @if ($serupa->isNotEmpty())
        <h2 class="judul-tabel">Usaha {{ \Illuminate\Support\Str::lower($ekraf->subsektor) }} lainnya</h2>
        <div class="serupa">
            @foreach ($serupa as $s)
                <a class="bl" wire:key="serupa-{{ $s->id_ekraf }}" href="{{ route('publik.detail-ekraf', $s) }}" wire:navigate>
                    <span class="bl-ph" style="background:{{ $s->gradien }}" aria-hidden="true">{{ $s->ikon }}</span>
                    <span class="bl-teks"><b>{{ $s->nama_usaha }}</b><small>{{ $s->alamat }}</small></span>
                </a>
            @endforeach
        </div>
    @endif
</div>
</div>
