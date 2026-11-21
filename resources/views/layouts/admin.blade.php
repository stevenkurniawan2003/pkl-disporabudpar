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
        ],
        'Ekonomi Kreatif' => [
            ['🎨', 'Data Master EKRAF', 'admin.ekonomi-kreatif', 'admin.ekonomi-kreatif'],
            ['🖨', 'Cetak Data EKRAF', 'admin.cetak-ekraf', 'admin.cetak-ekraf'],
        ],
        'PPO' => [
            ['🏃', 'Database Atlet', 'admin.ppo-atlet', 'admin.ppo-atlet'],
            ['📋', 'Database Pelatih', 'admin.ppo-pelatih', 'admin.ppo-pelatih'],
            ['🏅', 'Database Cabor', 'admin.ppo-cabor', 'admin.ppo-cabor'],
            ['🥇', 'Data Atlet Berprestasi', 'admin.ppo-prestasi', 'admin.ppo-prestasi'],
            ['📸', 'Galeri Prestasi', 'admin.ppo-galeri', 'admin.ppo-galeri'],
        ],
        'Kebudayaan' => [
            ['🏺', 'Objek Pemajuan Kebudayaan', 'admin.opk', 'admin.opk'],
            ['🏛', 'Cagar Budaya', 'admin.cagar-budaya', 'admin.cagar-budaya'],
            ['🎭', 'Data Master Kesenian', 'admin.kesenian', 'admin.kesenian'],
            ['🪪', 'Perpanjangan KIK', 'admin.perpanjangan-kik', 'admin.perpanjangan-kik'],
            ['🖨', 'Cetak Data Kesenian', 'admin.cetak-kesenian', 'admin.cetak-kesenian'],
        ],
        'Pariwisata' => [
            ['🗺', 'Lokasi Peta', 'admin.lokasi-peta', 'admin.lokasi-peta'],
            ['🎒', 'Paket Wisata', 'admin.paket-wisata', 'admin.paket-wisata'],
        ],
        'Kelola Data Kunjungan' => [
            ['📈', 'Data Kunjungan Wisata', 'admin.kunjungan-wisata', 'admin.kunjungan-wisata*'],
            ['🛏', 'Data Kunjungan Hotel', 'admin.kunjungan-hotel', 'admin.kunjungan-hotel*'],
        ],
        'Pengaturan' => [
            ['⚙️', 'Data Pejabat', 'admin.pengaturan.data-pejabat', 'admin.pengaturan.data-pejabat'],
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
{{-- Leaflet untuk pemilih titik lokasi di form (wisata, hotel, cagar budaya, dll.). --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
<script>try{var t=localStorage.getItem('admin-tema');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
@livewireStyles
</head><body>
<div class="app">
    <aside id="side">
        <div class="brand">
            <img src="{{ asset('images/logo-jember.png') }}" alt="Logo Kabupaten Jember" class="logo">
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

<script data-navigate-once>
// Editor teks kaya (TinyMCE) untuk komponen admin.editor. Isi editor dikirim ke properti Livewire tanpa request
// (live = false), lalu ikut terkirim saat tombol Simpan ditekan.
document.addEventListener('alpine:init', function () {
    Alpine.data('editorKaya', function (properti) {
        return {
            editor: null,
            init: function () {
                var self = this;
                var gelap = document.documentElement.getAttribute('data-theme') === 'dark';
                if (!window.tinymce) return;
                tinymce.init({
                    target: this.$refs.area,
                    license_key: 'gpl',
                    menubar: false,
                    branding: false,
                    promotion: false,
                    elementpath: false,
                    height: 320,
                    plugins: 'lists link autolink wordcount',
                    toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist blockquote | link | removeformat',
                    block_formats: 'Paragraf=p; Judul 2=h2; Judul 3=h3; Judul 4=h4',
                    link_default_target: '_blank',
                    link_target_list: false,
                    skin: gelap ? 'oxide-dark' : 'oxide',
                    content_css: gelap ? 'dark' : 'default',
                    content_style: 'body{font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-size:14px;line-height:1.6}',
                    setup: function (ed) {
                        self.editor = ed;
                        ed.on('init', function () { ed.setContent(self.$wire.get(properti) || ''); });
                        ed.on('change input undo redo', function () { self.$wire.set(properti, ed.getContent(), false); });
                    },
                });
            },
            destroy: function () {
                if (this.editor) this.editor.remove();
            },
        };
    });
});
</script>
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

    // Sinkronkan tema antar tab
    window.addEventListener('storage', function (e) {
        if (e.key === 'admin-tema' && e.newValue) {
            document.documentElement.setAttribute('data-theme', e.newValue);
        }
    });

    document.addEventListener('livewire:navigated', function () {
        var side = document.getElementById('side');
        if (side) side.classList.remove('on');
        
        // Restore tema setelah navigasi
        try {
            var tema = localStorage.getItem('admin-tema');
            if (tema) document.documentElement.setAttribute('data-theme', tema);
        } catch (e) {}
    });
})();
</script>
</body></html>
