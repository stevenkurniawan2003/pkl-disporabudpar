{{-- Dipakai bersama oleh DetailWisata dan DetailHotel. --}}
@php
    $paragraf = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', (string) $narasi)), fn ($p) => $p !== ''));
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
            @forelse ($paragraf as $p)
                <p>{!! nl2br(e($p)) !!}</p>
            @empty
                <p class="meta">Belum ada deskripsi.</p>
            @endforelse

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
        <dialog class="lightbox" id="lightbox" aria-label="Galeri foto {{ $nama }}">
            <figure>
                <img id="lb-img" src="" alt="">
                <figcaption><span id="lb-ket"></span><span id="lb-no" class="lb-no"></span></figcaption>
            </figure>
            <button type="button" class="lb-btn lb-tutup" data-aksi="tutup" aria-label="Tutup galeri"><i class="bi bi-x-lg"></i></button>
            @if (count($galeri) > 1)
                <button type="button" class="lb-btn lb-kiri" data-aksi="kiri" aria-label="Foto sebelumnya"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="lb-btn lb-kanan" data-aksi="kanan" aria-label="Foto berikutnya"><i class="bi bi-chevron-right"></i></button>
            @endif
        </dialog>
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

    // Lightbox galeri: klik foto, panah kiri/kanan (tombol & keyboard), Esc atau klik latar untuk menutup.
    const kotak = document.getElementById('lightbox');
    if (kotak) {
        const foto = @js($galeri);
        const img = document.getElementById('lb-img');
        const ket = document.getElementById('lb-ket');
        const no = document.getElementById('lb-no');
        let posisi = 0;

        const tampil = (i) => {
            posisi = (i + foto.length) % foto.length;
            img.src = foto[posisi].url;
            img.alt = foto[posisi].keterangan || ('Foto ' + (posisi + 1) + ' ' + @js($nama));
            ket.textContent = foto[posisi].keterangan || '';
            no.textContent = (posisi + 1) + ' / ' + foto.length;
        };

        $wire.$el.addEventListener('click', (e) => {
            const kecil = e.target.closest('.dd-foto');
            if (kecil) { tampil(parseInt(kecil.dataset.i, 10)); kotak.showModal(); return; }

            const tombol = e.target.closest('.lb-btn');
            if (tombol) {
                const aksi = tombol.dataset.aksi;
                if (aksi === 'tutup') kotak.close();
                else tampil(posisi + (aksi === 'kanan' ? 1 : -1));
                return;
            }

            if (e.target === kotak) kotak.close(); // klik di luar foto
        });

        kotak.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') tampil(posisi + 1);
            if (e.key === 'ArrowLeft') tampil(posisi - 1);
        });
    }
</script>
@endscript
</div>
