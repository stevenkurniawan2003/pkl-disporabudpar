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

    document.addEventListener('livewire:navigated', function () {
        var side = document.getElementById('side');
        if (side) side.classList.remove('on');
    });
})();
</script>
</body></html>
