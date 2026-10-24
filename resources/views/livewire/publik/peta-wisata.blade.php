<div>
<div class="page"><h1>Peta wisata</h1><p>Semua destinasi wisata dan hotel di Kabupaten Jember.</p>

<div class="tools">
    @foreach (['semua' => 'Wisata & hotel', 'wisata' => 'Wisata saja', 'hotel' => 'Hotel saja'] as $nilai => $label)
        <button type="button" class="chip {{ $jenis === $nilai ? 'on' : '' }}" wire:key="jenis-{{ $nilai }}" wire:click="pilihJenis('{{ $nilai }}')" aria-pressed="{{ $jenis === $nilai ? 'true' : 'false' }}">{{ $label }}</button>
    @endforeach
    <span class="legenda" aria-hidden="true">
        <span><i class="pin-mini wisata"></i>Wisata</span>
        <span><i class="pin-mini hotel"></i>Hotel</span>
    </span>
</div>

@if ($jenis !== 'hotel')
<div class="tools">
    <button type="button" class="chip {{ $kategori === 'Semua' ? 'on' : '' }}" wire:click="pilihKategori('Semua')">Semua kategori</button>
    @foreach ($daftarKategori as $k)
        <button type="button" class="chip {{ $kategori === $k->nama ? 'on' : '' }}" wire:key="kat-{{ $k->id }}" wire:click="pilihKategori('{{ $k->nama }}')">{{ $k->nama }}</button>
    @endforeach
</div>
@endif

<div class="mapw">
<div class="lst" id="daftar-peta">
    @foreach ($wisata as $w)
        <button type="button" class="it it-peta" wire:key="it-w-{{ $w->id }}" data-kunci="w-{{ $w->id }}">
            <span class="it-ph" style="background:{{ $w->gambar_url ? 'url('.$w->gambar_url.') center/cover no-repeat' : $w->gradien }}">@unless ($w->gambar_url){{ $w->kategori?->ikon ?? '📍' }}@endunless</span>
            <span><b>{{ $w->nama }}</b><small>{{ $w->kategori?->nama }} · Kec. {{ $w->kecamatan?->nama }}</small></span>
        </button>
    @endforeach
    @foreach ($hotel as $h)
        <button type="button" class="it it-peta" wire:key="it-h-{{ $h->id_hotel }}" data-kunci="h-{{ $h->id_hotel }}">
            <span class="it-ph" style="background:{{ $h->gambar_url ? 'url('.$h->gambar_url.') center/cover no-repeat' : 'linear-gradient(135deg,#C93A74,#F28BB0)' }}">@unless ($h->gambar_url)🏨@endunless</span>
            <span><b>{{ $h->nama_hotel }}</b><small>Hotel · {{ \Illuminate\Support\Str::limit($h->alamat, 40) }}</small></span>
        </button>
    @endforeach
    @if ($wisata->isEmpty() && $hotel->isEmpty())
        <p class="kosong-peta">
            @if ($jenis === 'hotel') Belum ada hotel dengan titik lokasi.
            @elseif ($kategori !== 'Semua') Belum ada wisata pada kategori {{ $kategori }}.
            @else Belum ada data wisata atau hotel dengan titik lokasi.
            @endif
        </p>
    @endif
</div>

<div class="map" style="min-height:520px">
    {{-- Peta Leaflet digambar oleh JavaScript, jadi Livewire jangan menyentuh isinya --}}
    <div id="peta" wire:ignore style="position:absolute;inset:0" role="region" aria-label="Peta lokasi wisata dan hotel"></div>
</div>
</div>
</div>

@assets
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<style>
    /* .map svg di publik.css bisa mengganggu ikon svg milik Leaflet */
    .map .leaflet-container svg { position: static; width: auto; height: auto; inset: auto; }
    .map .leaflet-pane svg, .map .leaflet-overlay-pane svg { position: absolute; }
    .map .leaflet-container { font: inherit; background: var(--bg2); }
    .leaflet-control-attribution a { color: var(--pinkink); }
</style>
@endassets

@script
<script>
    const el = document.getElementById('peta');
    if (el && window.L) {
        const PUSAT_JEMBER = [-8.17, 113.70];

        const peta = L.map(el, { attributionControl: false, scrollWheelZoom: true }).setView(PUSAT_JEMBER, 10);

        L.control.attribution({
            position: 'bottomright',
            prefix: '<a href="https://leafletjs.com" target="_blank" rel="noopener">Leaflet</a>',
        }).addTo(peta);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
        }).addTo(peta);

        const lapisPenanda = L.layerGroup().addTo(peta);
        let penandaPerKunci = {};

        // Ikon berbeda: wisata = pin pink, hotel = pin biru dengan ikon gedung.
        const ikon = (jenis) => L.divIcon({
            className: 'pin-peta ' + jenis,
            html: '<span><i class="bi ' + (jenis === 'hotel' ? 'bi-building' : 'bi-tree-fill') + '"></i></span>',
            iconSize: [34, 42],
            iconAnchor: [17, 40],
            popupAnchor: [0, -38],
        });

        // Isi popup dibangun dengan textContent (bukan innerHTML) supaya teks dari admin aman.
        const buatPopup = (t) => {
            const kotak = document.createElement('div');
            kotak.className = 'pp';

            const foto = document.createElement('div');
            foto.className = 'pp-ph';
            if (t.gambar) {
                const img = document.createElement('img');
                img.src = t.gambar;
                img.alt = t.nama;
                img.loading = 'lazy';
                foto.appendChild(img);
            } else {
                foto.textContent = t.ikon;
            }
            kotak.appendChild(foto);

            const isi = document.createElement('div');
            isi.className = 'pp-isi';
            const label = document.createElement('span');
            label.className = 'bd';
            label.textContent = t.label;
            const nama = document.createElement('b');
            nama.textContent = t.nama;
            isi.append(label, nama);
            if (t.teks) {
                const p = document.createElement('p');
                p.textContent = t.teks;
                isi.appendChild(p);
            }
            const tombol = document.createElement('a');
            tombol.className = 'btn p';
            tombol.href = t.url;
            tombol.textContent = 'Selengkapnya';
            tombol.setAttribute('wire:navigate', '');
            isi.appendChild(tombol);
            kotak.appendChild(isi);

            return kotak;
        };

        const tandaiKartu = (kunci) => {
            document.querySelectorAll('#daftar-peta .it-peta').forEach((k) => k.classList.toggle('on', k.dataset.kunci === kunci));
        };

        function gambar(titik, sesuaikanZoom) {
            lapisPenanda.clearLayers();
            penandaPerKunci = {};

            titik.forEach((t) => {
                const penanda = L.marker([t.lat, t.lng], { icon: ikon(t.jenis), title: t.nama, keyboard: true })
                    .bindPopup(() => buatPopup(t), { maxWidth: 260, minWidth: 220 })
                    .addTo(lapisPenanda);
                penanda.on('popupopen', () => tandaiKartu(t.kunci));
                penandaPerKunci[t.kunci] = penanda;
            });

            if (sesuaikanZoom && titik.length > 1) {
                peta.fitBounds(L.latLngBounds(titik.map((t) => [t.lat, t.lng])), { padding: [50, 50], maxZoom: 13 });
            } else if (sesuaikanZoom && titik.length === 1) {
                peta.setView([titik[0].lat, titik[0].lng], 13);
            }
        }

        function buka(kunci) {
            const penanda = penandaPerKunci[kunci];
            if (!penanda) return;
            peta.setView(penanda.getLatLng(), Math.max(peta.getZoom(), 14), { animate: true });
            penanda.openPopup();
            tandaiKartu(kunci);
        }

        gambar(@js($titik), true);

        const dipilih = @js($dipilih ? 'w-'.$dipilih : null);
        if (dipilih) setTimeout(() => buka(dipilih), 200);

        // Klik kartu di daftar: pusatkan peta ke penandanya.
        $wire.$el.addEventListener('click', (e) => {
            const kartu = e.target.closest('.it-peta');
            if (kartu) {
                buka(kartu.dataset.kunci);
                if (window.matchMedia('(max-width: 860px)').matches) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        // Dipanggil setiap kali jenis / kategori berubah (lihat PetaWisata.php).
        $wire.on('peta-diperbarui', ({ titik }) => gambar(titik || [], true));

        setTimeout(() => peta.invalidateSize(), 150);
    }
</script>
@endscript
</div>
