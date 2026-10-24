@php
    // Menu otomatis: item yang route-nya belum dibuat disembunyikan, jadi tidak pernah error.
    $menu = [
        ['Beranda', 'publik.beranda'],
        ['Destinasi', [
            ['Peta Wisata', 'publik.peta-wisata'],
            ['Data Kunjungan Wisata', 'publik.kunjungan-wisata'],
            ['Data Kunjungan Hotel', 'publik.kunjungan-hotel'],
        ]],
        ['Berita', 'publik.berita'],
        ['Kebudayaan', 'publik.kebudayaan'],
        ['Ekonomi Kreatif', 'publik.ekonomi-kreatif'],
        ['Profil', [
            ['Tentang Kami', 'publik.profil.tentang-kami'],
            ['Visi Misi', 'publik.profil.visi-misi'],
            ['Struktur Organisasi', 'publik.profil.struktur-organisasi'],
            ['Tugas dan Fungsi', 'publik.profil.tugas-dan-fungsi'],
        ]],
    ];
    // Route lain yang ikut menandai menu sebagai aktif (misalnya detail berita -> menu Berita).
    $aktifJuga = [
        'publik.berita' => ['publik.detail-berita'],
        'publik.peta-wisata' => ['publik.detail-wisata', 'publik.detail-hotel'],
        'publik.kebudayaan' => ['publik.detail-kesenian'],
        'publik.ekonomi-kreatif' => ['publik.detail-ekraf'],
    ];
    $ada = fn ($nama) => \Illuminate\Support\Facades\Route::has($nama);
    $aktif = fn ($nama) => request()->routeIs($nama, $nama.'.*', ...($aktifJuga[$nama] ?? []));
    $angka = fn ($n) => number_format((int) $n, 0, ',', '.');
    $sosmed = array_values(array_filter($situs['media_sosial'], fn ($s) => filled($s['url'])));
@endphp
<!doctype html>
<html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $title ?? 'Disporabudpar Jember' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/publik.css') }}">
<script>try{var t=localStorage.getItem('publik-tema');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
@livewireStyles
</head><body>

<header>
    <div class="w hb">
        <a class="logo" href="{{ route('publik.beranda') }}" wire:navigate>
            <span class="lg">J</span>
            <span>Disporabudpar<small>Kabupaten Jember</small></span>
        </a>
        <button type="button" class="bg" aria-label="Buka menu" aria-controls="menu-utama" aria-expanded="false">☰</button>
        <nav id="menu-utama" aria-label="Menu utama">
            @foreach ($menu as [$label, $tujuan])
                @if (is_string($tujuan))
                    @if ($ada($tujuan))
                        <div class="ni"><a class="nb {{ $aktif($tujuan) ? 'on' : '' }}" href="{{ route($tujuan) }}" wire:navigate>{{ $label }}</a></div>
                    @endif
                @else
                    @php $anak = array_values(array_filter($tujuan, fn ($a) => $ada($a[1]))); @endphp
                    @if ($anak)
                        <div class="ni">
                            <button type="button" class="nb {{ collect($anak)->contains(fn ($a) => $aktif($a[1])) ? 'on' : '' }}" aria-haspopup="true">{{ $label }} ▾</button>
                            <div class="dd">
                                @foreach ($anak as [$l, $r])
                                    <a class="nb {{ $aktif($r) ? 'on' : '' }}" href="{{ route($r) }}" wire:navigate>{{ $l }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @endforeach
            <button type="button" class="btn" id="th" aria-label="Ganti tema" style="padding:6px 12px">◐</button>
        </nav>
    </div>
</header>

<main class="w">{{ $slot }}</main>

<footer>
    <div class="w">
        <div class="fg">
            {{-- Kolom 1: dinas + media sosial --}}
            <div class="f-dinas">
                <a class="logo" href="{{ route('publik.beranda') }}" wire:navigate>
                    <span class="lg">J</span>
                    <span>Disporabudpar<small>Kabupaten Jember</small></span>
                </a>
                <p class="f-nama">{{ $situs['nama_dinas'] }}</p>
                <p>{{ $situs['deskripsi'] }}</p>
                @if ($sosmed)
                    <div class="sosmed">
                        @foreach ($sosmed as $s)
                            <a href="{{ $s['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Disporabudpar Jember di {{ $s['nama'] }}" title="{{ $s['nama'] }}"><i class="bi bi-{{ $s['ikon'] }}" aria-hidden="true"></i></a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Kolom 2: tautan cepat --}}
            <div>
                <h4>Tautan Cepat</h4>
                <ul class="f-link">
                    @foreach ($situs['tautan_cepat'] as $t)
                        <li><a href="{{ $t['url'] }}" target="_blank" rel="noopener noreferrer">{{ $t['nama'] }} <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span class="sr">(tab baru)</span></a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Kolom 3: pengunjung situs --}}
            <div>
                <h4>Pengunjung</h4>
                <dl class="f-statistik">
                    <div><dt>Hari ini</dt><dd>{{ $angka($statistikPengunjung['hari_ini']) }}</dd></div>
                    <div><dt>Bulan ini</dt><dd>{{ $angka($statistikPengunjung['bulan_ini']) }}</dd></div>
                    <div class="total"><dt>Total</dt><dd>{{ $angka($statistikPengunjung['total']) }}</dd></div>
                </dl>
            </div>

            {{-- Kolom 4: hubungi kami + peta kantor --}}
            <div>
                <h4>Hubungi Kami</h4>
                @persist('peta-kantor')
                    <div class="f-peta">
                        <iframe src="{{ $situs['peta_embed'] }}" title="Peta lokasi kantor Disporabudpar Kabupaten Jember" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endpersist
                <ul class="f-kontak">
                    <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ $situs['kontak']['alamat'] }}</span></li>
                    @if (filled($situs['kontak']['telepon']))
                        <li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $situs['kontak']['telepon']) }}">{{ $situs['kontak']['telepon'] }}</a></li>
                    @endif
                    @if (filled($situs['kontak']['email']))
                        <li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:{{ $situs['kontak']['email'] }}">{{ $situs['kontak']['email'] }}</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="cp">
            <span>© {{ date('Y') }} Disporabudpar Kabupaten Jember</span>
            <a href="{{ route('admin.login') }}">Masuk admin</a>
        </div>
    </div>
</footer>

@livewireScripts
<script data-navigate-once>
(function () {
    var q = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

    // Klik: menu mobile, dropdown, ganti tema, titik slider (pakai delegasi supaya tetap jalan setelah wire:navigate)
    document.addEventListener('click', function (e) {
        var t = e.target;

        var burger = t.closest('.bg');
        if (burger) {
            var buka = !document.getElementById('menu-utama').classList.contains('on');
            q('header nav').forEach(function (n) { n.classList.toggle('on', buka); });
            burger.setAttribute('aria-expanded', buka ? 'true' : 'false');
            return;
        }

        if (t.closest('#th')) {
            var el = document.documentElement;
            var baru = el.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            el.setAttribute('data-theme', baru);
            try { localStorage.setItem('publik-tema', baru); } catch (x) {}
            return;
        }

        var dot = t.closest('.dots button');
        if (dot) { pindah(parseInt(dot.getAttribute('data-s'), 10) || 0); return; }

        var tombol = t.closest('.ni > button.nb');
        q('.ni.open').forEach(function (n) { if (!tombol || n !== tombol.parentNode) n.classList.remove('open'); });
        if (tombol) tombol.parentNode.classList.toggle('open');
    });

    // Tutup menu setelah pindah halaman
    document.addEventListener('livewire:navigated', function () {
        q('header nav').forEach(function (n) { n.classList.remove('on'); });
        q('.bg').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
        q('.ni.open').forEach(function (n) { n.classList.remove('open'); });
    });

    // Slider beranda
    function pindah(i) {
        var s = q('#sl .sl'), d = q('#sl .dots button');
        if (!s.length) return;
        i = ((i % s.length) + s.length) % s.length;
        s.forEach(function (x, n) { x.classList.toggle('on', n === i); });
        d.forEach(function (x, n) { x.classList.toggle('on', n === i); });
    }
    setInterval(function () {
        var s = q('#sl .sl');
        if (s.length < 2 || document.hidden) return;
        var sekarang = s.findIndex(function (x) { return x.classList.contains('on'); });
        pindah(sekarang + 1);
    }, 5500);
})();
</script>
</body></html>
