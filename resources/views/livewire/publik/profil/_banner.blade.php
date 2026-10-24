{{-- Banner, breadcrumb, dan tab yang sama untuk keempat halaman Profil. --}}
<section class="profil-banner">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol>
            <li><a href="{{ route('publik.beranda') }}" wire:navigate>Beranda</a></li>
            <li><span>Profil</span></li>
            <li><span aria-current="page">{{ $judul }}</span></li>
        </ol>
    </nav>
    <h1>{{ $judul }}</h1>
    <p>{{ $keterangan }}</p>
    <svg viewBox="0 0 200 200" aria-hidden="true"><circle cx="100" cy="100" r="90" fill="none" stroke="#fff" stroke-width="3"></circle><circle cx="100" cy="100" r="60" fill="none" stroke="#fff" stroke-width="3"></circle><circle cx="100" cy="100" r="30" fill="#fff"></circle></svg>
</section>

<nav class="profil-tab" aria-label="Halaman profil">
    @foreach ($semuaHalaman as $slug => [$label])
        <a class="chip {{ $halaman === $slug ? 'on' : '' }}" href="{{ route('publik.profil.'.$slug) }}" wire:navigate @if ($halaman === $slug) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</nav>
