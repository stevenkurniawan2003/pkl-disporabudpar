<div>
<div class="page"><h1>Peta Wisata</h1><p>Desa wisata, ekonomi kreatif, hotel dan penginapan, obyek wisata, restoran, seni budaya, situs budaya, dan lainnya di Kabupaten Jember.</p>

{{-- 1. Peta Wisata berlapis: centang kategori & pencarian disaring di JavaScript (tanpa request). --}}
<section class="card peta-lapis" aria-labelledby="judul-peta-lapis">
    <h2 id="judul-peta-lapis" class="sr">Peta wisata per kategori</h2>
    <div class="tools" style="margin-bottom:10px">
        <input type="search" id="cari-peta" placeholder="Cari nama obyek wisata…" aria-label="Cari nama obyek wisata" autocomplete="off">
    </div>
    <fieldset class="filter-lapis" wire:ignore>
        <legend>Filter kategori:</legend>
        @foreach ($lapisan as $l)
            <label class="cek-lapis">
                <input type="checkbox" value="{{ $l['kunci'] }}" checked>
                <i class="pin-mini {{ $l['kunci'] }}" aria-hidden="true"></i>{{ $l['label'] }} ({{ $l['jumlah'] }})
            </label>
        @endforeach
    </fieldset>
    <div class="map peta-besar">
        <div id="peta-lapis" wire:ignore style="position:absolute;inset:0" role="region" aria-label="Peta wisata per kategori"></div>
    </div>
    <p class="meta" id="info-peta-lapis" aria-live="polite"></p>
</section>

{{-- 2. Peta Obyek Wisata: semua obyek wisata, daftar + peta. --}}
<h2 class="judul-tabel" id="peta-obyek">Peta Obyek Wisata</h2>
<p class="meta">Semua obyek wisata di Jember. Klik nama di daftar untuk melihat lokasinya.</p>

<div class="tools" style="margin-top:10px">
    <button type="button" class="chip {{ $kategori === 'Semua' ? 'on' : '' }}" wire:click="pilihKategori('Semua')">Semua kategori</button>
    @foreach ($daftarKategori as $k)
        <button type="button" class="chip {{ $kategori === $k->nama ? 'on' : '' }}" wire:key="kat-{{ $k->id }}" wire:click="pilihKategori(@js($k->nama))">{{ $k->nama }}</button>
    @endforeach
</div>

<div class="mapw">
<div class="lst" id="daftar-peta">
    @forelse ($wisata as $w)
        <button type="button" class="it it-peta" wire:key="it-w-{{ $w->id }}" data-kunci="w-{{ $w->id }}">
            <span class="it-ph" style="background:{{ $w->gambar_url ? 'url('.$w->gambar_url.') center/cover no-repeat' : $w->gradien }}">@unless ($w->gambar_url){{ $w->kategori?->ikon ?? '📍' }}@endunless</span>
            <span><b>{{ $w->nama }}</b><small>{{ $w->kategori?->nama }} · Kec. {{ $w->kecamatan?->nama }}</small></span>
        </button>
    @empty
        <p class="kosong-peta">
            @if ($kategori !== 'Semua') Belum ada wisata pada kategori {{ $kategori }}.
            @else Belum ada data wisata dengan titik lokasi.
            @endif
        </p>
    @endforelse
</div>

<div class="map" style="min-height:520px">
    <div id="peta" wire:ignore style="position:absolute;inset:0" role="region" aria-label="Peta obyek wisata"></div>
</div>
</div>

{{-- 3. Saran dokumen: obyek wisata baru & paket wisata terbaru. --}}
<div class="dua-kolom-peta">
    <section aria-labelledby="judul-wisata-baru">
        <h2 class="judul-tabel" id="judul-wisata-baru">Obyek wisata baru di Jember</h2>
        <div class="daftar-mini">
            @forelse ($wisataTerbaru as $w)
                <a class="it" href="{{ route('publik.detail-wisata', $w) }}" wire:navigate wire:key="baru-{{ $w->id }}">
                    <span class="it-ph" style="background:{{ $w->gambar_url ? 'url('.$w->gambar_url.') center/cover no-repeat' : $w->gradien }}">@unless ($w->gambar_url){{ $w->kategori?->ikon ?? '📍' }}@endunless</span>
                    <span><b>{{ $w->nama }}</b><small>{{ $w->kategori?->nama }} · Kec. {{ $w->kecamatan?->nama }}</small></span>
                </a>
            @empty
                <p class="meta">Belum ada obyek wisata.</p>
            @endforelse
        </div>
    </section>
    <section aria-labelledby="judul-paket-baru">
        <h2 class="judul-tabel" id="judul-paket-baru">Paket wisata terbaru</h2>
        <div class="daftar-mini">
            @forelse ($paketTerbaru as $p)
                <a class="it" href="{{ route('publik.paket-wisata') }}" wire:navigate wire:key="paket-{{ $p->id_paket }}">
                    <span class="it-ph" style="background:url({{ $p->flyer_url }}) center/cover no-repeat"></span>
                    <span><b>{{ $p->nama_paket }}</b><small>{{ collect([$p->durasi, $p->harga])->filter()->implode(' · ') ?: 'Lihat detail paket' }}</small></span>
                </a>
            @empty
                <p class="meta">Belum ada paket wisata.</p>
            @endforelse
        </div>
    </section>
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
    if (window.L) {
        const PUSAT_JEMBER = [-8.17, 113.70];

        const buatPeta = (el) => {
            const peta = L.map(el, { attributionControl: false, scrollWheelZoom: true }).setView(PUSAT_JEMBER, 10);
            L.control.attribution({ position: 'bottomright', prefix: '<a href="https://leafletjs.com" target="_blank" rel="noopener">Leaflet</a>' }).addTo(peta);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
            }).addTo(peta);
            setTimeout(() => peta.invalidateSize(), 150);
            return peta;
        };

        // Pin berwarna per lapisan (warna di publik.css .pin-peta.<jenis>); ikon emoji dipakai Peta Obyek Wisata.
        const ikon = (jenis, ikonBi) => L.divIcon({
            className: 'pin-peta ' + jenis,
            html: '<span><i class="bi ' + (ikonBi && ikonBi.startsWith('bi-') ? ikonBi : 'bi-tree-fill') + '"></i></span>',
            iconSize: [34, 42], iconAnchor: [17, 40], popupAnchor: [0, -38],
        });

        // Isi popup dibangun dengan textContent (bukan innerHTML) supaya teks dari admin aman.
        const buatPopup = (t) => {
            const kotak = document.createElement('div');
            kotak.className = 'pp';
            const foto = document.createElement('div');
            foto.className = 'pp-ph pin-latar ' + t.jenis;
            if (t.gambar) {
                const img = document.createElement('img');
                img.src = t.gambar; img.alt = t.nama; img.loading = 'lazy';
                foto.appendChild(img);
            } else if (t.ikon.startsWith('bi-')) {
                const i = document.createElement('i');
                i.className = 'bi ' + t.ikon;
                foto.appendChild(i);
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
            if (t.url) {
                const tombol = document.createElement('a');
                tombol.className = 'btn p';
                tombol.href = t.url;
                tombol.textContent = 'Selengkapnya';
                tombol.setAttribute('wire:navigate', '');
                isi.appendChild(tombol);
            }
            kotak.appendChild(isi);
            return kotak;
        };

        const sesuaikan = (peta, titik) => {
            if (titik.length > 1) peta.fitBounds(L.latLngBounds(titik.map((t) => [t.lat, t.lng])), { padding: [50, 50], maxZoom: 13 });
            else if (titik.length === 1) peta.setView([titik[0].lat, titik[0].lng], 13);
        };

        // ---- 1. Peta Wisata berlapis ----
        const elLapis = document.getElementById('peta-lapis');
        const semuaTitik = @js($titikBerlapis);
        const petaLapis = buatPeta(elLapis);
        const grupLapis = L.layerGroup().addTo(petaLapis);
        const kotakCari = document.getElementById('cari-peta');
        const info = document.getElementById('info-peta-lapis');

        const gambarLapis = (sesuaikanZoom) => {
            const aktif = new Set([...document.querySelectorAll('.filter-lapis input:checked')].map((c) => c.value));
            const kata = kotakCari.value.trim().toLowerCase();
            const tampil = semuaTitik.filter((t) => aktif.has(t.jenis) && (kata === '' || t.nama.toLowerCase().includes(kata)));

            grupLapis.clearLayers();
            tampil.forEach((t) => {
                L.marker([t.lat, t.lng], { icon: ikon(t.jenis, t.ikon), title: t.nama, keyboard: true })
                    .bindPopup(() => buatPopup(t), { maxWidth: 260, minWidth: 220 })
                    .addTo(grupLapis);
            });

            info.textContent = tampil.length + ' titik ditampilkan' + (kata ? ' untuk "' + kotakCari.value.trim() + '"' : '') + '.';
            if (sesuaikanZoom) sesuaikan(petaLapis, tampil);
            if (kata && tampil.length === 1) grupLapis.getLayers()[0].openPopup();
        };

        gambarLapis(true);
        document.querySelectorAll('.filter-lapis input').forEach((c) => c.addEventListener('change', () => gambarLapis(false)));
        let jedaCari;
        kotakCari.addEventListener('input', () => { clearTimeout(jedaCari); jedaCari = setTimeout(() => gambarLapis(true), 300); });

        // ---- 2. Peta Obyek Wisata ----
        const el = document.getElementById('peta');
        const peta = buatPeta(el);
        const lapisPenanda = L.layerGroup().addTo(peta);
        let penandaPerKunci = {};

        const tandaiKartu = (kunci) => {
            document.querySelectorAll('#daftar-peta .it-peta').forEach((k) => k.classList.toggle('on', k.dataset.kunci === kunci));
        };

        function gambar(titik, sesuaikanZoom) {
            lapisPenanda.clearLayers();
            penandaPerKunci = {};
            titik.forEach((t) => {
                const penanda = L.marker([t.lat, t.lng], { icon: ikon('objek-wisata'), title: t.nama, keyboard: true })
                    .bindPopup(() => buatPopup(t), { maxWidth: 260, minWidth: 220 })
                    .addTo(lapisPenanda);
                penanda.on('popupopen', () => tandaiKartu(t.kunci));
                penandaPerKunci[t.kunci] = penanda;
            });
            if (sesuaikanZoom) sesuaikan(peta, titik);
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
        if (dipilih) setTimeout(() => { el.scrollIntoView({ block: 'center' }); buka(dipilih); }, 200);

        $wire.$el.addEventListener('click', (e) => {
            const kartu = e.target.closest('.it-peta');
            if (kartu) {
                buka(kartu.dataset.kunci);
                if (window.matchMedia('(max-width: 860px)').matches) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        // Dipanggil setiap kali kategori Peta Obyek Wisata berubah (lihat PetaWisata::pilihKategori).
        $wire.on('peta-obyek-diperbarui', ({ titik }) => gambar(titik || [], true));
    }
</script>
@endscript
</div>
