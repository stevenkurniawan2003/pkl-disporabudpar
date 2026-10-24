@php
    use Illuminate\Support\Facades\Route;

    // Menu admin. Item yang route-nya belum dibuat otomatis disembunyikan.
    $menu = [
        'Utama' => [
            ['📊', 'Dashboard', 'admin.dashboard', 'admin.dashboard'],
            ['📰', 'Berita', 'admin.berita', 'admin.berita*'],
        ],
        'Data Master' => [
            ['📍', 'Wisata', 'admin.wisata', 'admin.wisata*'],
            ['🏨', 'Hotel', 'admin.hotel', 'admin.hotel'],
            ['🎭', 'Kesenian', 'admin.kesenian', 'admin.kesenian*'],
            ['🎨', 'Ekraf', 'admin.ekonomi-kreatif', 'admin.ekonomi-kreatif*'],
        ],
        'Kelola Data Kunjungan' => [
            ['📈', 'Data Kunjungan Wisata', 'admin.kunjungan-wisata', 'admin.kunjungan-wisata*'],
            ['🛏', 'Data Kunjungan Hotel', 'admin.kunjungan-hotel', 'admin.kunjungan-hotel*'],
        ],
        'Akun' => [
            ['👤', 'Profil', 'admin.profil', 'admin.profil*'],
        ],
    ];
@endphp
<!doctype html>
<html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $title ?? 'Admin · Disporabudpar Jember' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<script>try{var t=localStorage.getItem('admin-tema');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
@livewireStyles
</head><body>
<div class="app">
    <aside id="side">
        <div class="brand">
            <span class="logo">J</span>
            <div><b>Disporabudpar</b><small>Panel Admin</small></div>
        </div>
        <nav>
            @foreach ($menu as $judul => $items)
                @php $tampil = array_values(array_filter($items, fn ($i) => Route::has($i[2]))); @endphp
                @if ($tampil)
                    <div class="nav-h">{{ $judul }}</div>
                    @foreach ($tampil as [$ikon, $label, $rute, $pola])
                        <a class="{{ request()->routeIs($pola) ? 'on' : '' }}" href="{{ route($rute) }}" wire:navigate><i>{{ $ikon }}</i>{{ $label }}</a>
                    @endforeach
                @endif
            @endforeach

            <div class="nav-h">Situs</div>
            <a href="{{ route('publik.beranda') }}" target="_blank" rel="noopener"><i>🌐</i>Lihat situs publik</a>
        </nav>
    </aside>

    <main>{{ $slot }}</main>
</div>

@livewireScripts
<script data-navigate-once>
(function () {
    document.addEventListener('click', function (e) {
        var t = e.target, side = document.getElementById('side');

        if (t.closest('#bg')) { if (side) side.classList.toggle('on'); return; }

        if (t.closest('#th')) {
            var el = document.documentElement;
            var baru = el.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            el.setAttribute('data-theme', baru);
            try { localStorage.setItem('admin-tema', baru); } catch (x) {}
            return;
        }

        // Klik di luar sidebar (mobile) menutupnya
        if (side && side.classList.contains('on') && !t.closest('#side')) side.classList.remove('on');
    });

    document.addEventListener('livewire:navigated', function () {
        var side = document.getElementById('side');
        if (side) side.classList.remove('on');
    });
})();
</script>
</body></html>
