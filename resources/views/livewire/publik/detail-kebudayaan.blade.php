{{-- Dipakai bersama oleh DetailOpk dan DetailCagarBudaya. $data: daftar [ikon, label, nilai]; nilai kosong dilewati. --}}
@php
    $isiHtml = \App\Support\TeksKaya::html($isi);
    $adaPeta = $lat !== null && \App\Livewire\Publik\PetaWisata::koordinatValid($lat, $lng);
@endphp
<div>
<div class="page detail-dest">
    <a class="chip" href="{{ $kembali[0] }}" wire:navigate>← {{ $kembali[1] }}</a>

    <div class="dd-kepala">
        <span class="bd">{{ $label }}</span>
        <h1>{{ $nama }}</h1>
    </div>

    <div class="dd-grid">
        <article>
            @if ($gambar)
                <img class="dd-utama" src="{{ $gambar }}" alt="{{ $nama }}">
            @endif

            <h2>{{ $judulIsi }}</h2>
            @if (\App\Support\TeksKaya::kosong($isiHtml))
                <p class="meta">Belum ada deskripsi.</p>
            @else
                <div class="isi-kaya">{!! $isiHtml !!}</div>
            @endif

            @if ($galeri)
                <h2>Galeri foto <small>({{ count($galeri) }})</small></h2>
                <div class="dd-galeri">
                    @foreach ($galeri as $i => $foto)
                        <button type="button" class="dd-foto" data-i="{{ $i }}" aria-label="Lihat foto {{ $i + 1 }}">
                            <img src="{{ $foto['url'] }}" alt="Foto {{ $i + 1 }} {{ $nama }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </article>

        <aside class="dd-info" aria-labelledby="judul-info">
            <h2 id="judul-info">Informasi</h2>
            @if ($adaPeta)
                <div class="dd-peta" id="peta-kecil" wire:ignore role="region" aria-label="Peta lokasi {{ $nama }}"></div>
            @endif
            <ul class="dd-data">
                @foreach ($data as [$ikon, $judul, $nilai])
                    @if (filled($nilai))
                        <li><i class="bi bi-{{ $ikon }}" aria-hidden="true"></i><span><b>{{ $judul }}:</b> {{ $nilai }}</span></li>
                    @endif
                @endforeach
            </ul>
            @if ($adaPeta)
                <div class="dd-tombol">
                    <a class="btn p" href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-map" aria-hidden="true"></i> Buka di Google Maps</a>
                </div>
            @endif
        </aside>
    </div>

    @if ($galeri)
        @include('livewire.publik.partials.lightbox')
    @endif
</div>

@if ($adaPeta)
@assets
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
@endassets

@script
<script>
    const elPeta = document.getElementById('peta-kecil');
    if (elPeta && window.L) {
        const posisi = [@js($lat), @js($lng)];
        const peta = L.map(elPeta, { scrollWheelZoom: false }).setView(posisi, 15);
        peta.attributionControl.setPrefix(false);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
        }).addTo(peta);
        L.marker(posisi, {
            title: @js($nama),
            icon: L.divIcon({ className: 'pin-peta wisata', html: '<span><i class="bi bi-bank"></i></span>', iconSize: [34, 42], iconAnchor: [17, 40] }),
        }).addTo(peta);
        setTimeout(() => peta.invalidateSize(), 150);
    }
</script>
@endscript
@endif
</div>
