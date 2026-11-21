<div class="login-shell">
    {{-- Panel kiri: identitas dinas (disembunyikan di layar kecil) --}}
    <div class="login-side" aria-hidden="true">
        <svg class="login-deko" viewBox="0 0 200 200"><circle cx="100" cy="100" r="90" fill="none" stroke="#fff" stroke-width="3"></circle><circle cx="100" cy="100" r="60" fill="none" stroke="#fff" stroke-width="3"></circle><circle cx="100" cy="100" r="30" fill="#fff"></circle></svg>
        <div class="brand">
            <img src="{{ asset('images/logo-jember-96x96.png') }}" alt="Logo Kabupaten Jember" class="logo logo-lg">
            <div><b>Disporabudpar</b><small>Kabupaten Jember</small></div>
        </div>
        <div>
            <h2>Panel Pengelola Situs</h2>
            <p>Kelola data wisata, hotel, kesenian, ekonomi kreatif, dan berita Dinas Kepemudaan, Olahraga, Kebudayaan, dan Pariwisata Kabupaten Jember.</p>
        </div>
        <small>© {{ now()->year }} Disporabudpar Jember</small>
    </div>

    {{-- Panel kanan: form masuk --}}
    <section class="login-main">
        <a class="login-back" href="{{ route('publik.beranda') }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Kembali ke situs publik
        </a>

        <div class="login-card">
            <div class="brand login-brand-hp">
                <img src="{{ asset('images/logo-jember.png') }}" alt="Logo Kabupaten Jember" class="logo">
                <div><b>Disporabudpar</b><small>Kabupaten Jember · Admin</small></div>
            </div>

            <h1>Selamat datang</h1>
            <p class="login-sub">Masuk untuk mengelola konten situs. Halaman ini khusus pengelola.</p>

            <form wire:submit="masuk" class="login-form">
                <div class="fd">
                    <label for="email">Email</label>
                    <div class="inp-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <input id="email" type="email" wire:model="email" autocomplete="username" placeholder="nama@contoh.com" autofocus>
                    </div>
                    @error('email') <small class="err-msg">{{ $message }}</small> @enderror
                </div>

                <div class="fd" x-data="{ lihat: false }">
                    <label for="password">Kata sandi</label>
                    <div class="inp-ikon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        <input id="password" :type="lihat ? 'text' : 'password'" type="password" wire:model="password" autocomplete="current-password" placeholder="Masukkan kata sandi">
                        <button type="button" class="mata" @click="lihat = !lihat" :aria-label="lihat ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" aria-label="Tampilkan kata sandi">
                            <svg x-show="!lihat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="lihat" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-2.2 3.2M6.6 6.6C3.9 8.4 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2M3 3l18 18"/></svg>
                        </button>
                    </div>
                    @error('password') <small class="err-msg">{{ $message }}</small> @enderror
                </div>

                <label class="cek"><input type="checkbox" wire:model="ingat"> Ingat saya di perangkat ini</label>

                <button type="submit" class="btn p login-btn" wire:loading.attr="disabled" wire:target="masuk">
                    <span wire:loading.remove wire:target="masuk">Masuk</span>
                    <span wire:loading wire:target="masuk">Memeriksa…</span>
                </button>
            </form>
        </div>
    </section>
</div>
