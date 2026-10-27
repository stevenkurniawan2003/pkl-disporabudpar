{{-- Dipakai bersama oleh DetailWisata dan DetailHotel. --}}
@php
    // Narasi dari editor admin (TinyMCE), sudah dibersihkan HTML Purifier. Data lama berupa teks biasa jadi paragraf.
    $narasiHtml = \App\Support\TeksKaya::html($narasi);
    $adaPeta = \App\Livewire\Publik\PetaWisata::koordinatValid($lat, $lng);
    $kembali = route('publik.peta-wisata', $jenis === 'hotel' ? ['tampil' => 'hotel'] : []);
@endphp
<div>
<div class="page detail-dest">
    <a class="chip" href="{{ $kembali }}" wire:navigate>← Peta wisata</a>

    @if ($pratinjauDraf)
        <p class="pratinjau">Pratinjau admin: wisata ini masih draf dan belum tampil di publik.</p>
    @endif

    <div class="dd-kepala">
        <span class="bd">{{ $label }}</span>
        <h1>{{ $nama }}</h1>
        @if ($ringkas)<p class="meta">{{ $ringkas }}</p>@endif
    </div>

    <div class="dd-grid">
        <article>
            @if ($gambar)
                <img class="dd-utama" src="{{ $gambar }}" alt="{{ $nama }}">
            @else
                <div class="dd-utama ph" style="background:{{ $gradien }}" aria-hidden="true">{{ $ikon }}</div>
            @endif

            <h2>Tentang {{ $jenis === 'hotel' ? 'hotel' : 'wisata' }} ini</h2>
            @if (\App\Support\TeksKaya::kosong($narasiHtml))
                <p class="meta">Belum ada deskripsi.</p>
            @else
                <div class="isi-kaya">{!! $narasiHtml !!}</div>
            @endif

            @if ($galeri)
                <h2>Galeri foto <small>({{ count($galeri) }})</small></h2>
                <div class="dd-galeri">
                    @foreach ($galeri as $i => $foto)
                        <button type="button" class="dd-foto" data-i="{{ $i }}" aria-label="Lihat foto {{ $i + 1 }}{{ $foto['keterangan'] ? ': '.$foto['keterangan'] : '' }}">
                            <img src="{{ $foto['url'] }}" alt="{{ $foto['keterangan'] ?: 'Foto '.($i + 1).' '.$nama }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </article>

        <aside class="dd-info" aria-labelledby="judul-lokasi">
            <h2 id="judul-lokasi">Lokasi</h2>
            @if ($adaPeta)
                <div class="dd-peta" id="peta-kecil" wire:ignore role="region" aria-label="Peta lokasi {{ $nama }}"></div>
            @endif
            <ul class="dd-data">
                <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ $alamat }}</span></li>
                @if ($telepon)
                    <li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $telepon) }}">{{ $telepon }}</a></li>
                @endif
            </ul>
            <div class="dd-tombol">
                @if ($linkMaps)
                    <a class="btn p" href="{{ $linkMaps }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-map" aria-hidden="true"></i> Buka di Google Maps</a>
                @endif
                @if ($linkWebsite)
                    <a class="btn" href="{{ $linkWebsite }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-globe" aria-hidden="true"></i> Kunjungi Website</a>
                @endif
            </div>
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
@endif

@script
<script>
    // Peta kecil lokasi
    const elPeta = document.getElementById('peta-kecil');
    if (elPeta && window.L) {
        const posisi = [@js($lat), @js($lng)];
        const peta = L.map(elPeta, { scrollWheelZoom: false, attributionControl: true }).setView(posisi, 15);
        peta.attributionControl.setPrefix(false);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
        }).addTo(peta);
        L.marker(posisi, {
            title: @js($nama),
            icon: L.divIcon({
                className: 'pin-peta ' + @js($jenis),
                html: '<span><i class="bi ' + (@js($jenis) === 'hotel' ? 'bi-building' : 'bi-tree-fill') + '"></i></span>',
                iconSize: [34, 42], iconAnchor: [17, 40],
            }),
        }).addTo(peta);
        setTimeout(() => peta.invalidateSize(), 150);
    }
</script>
@endscript
</div>
