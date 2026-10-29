{{-- Dipakai bersama oleh DaftarOpk dan DaftarCagarBudaya: pencarian, chip filter, kartu bergambar, pagination. --}}
<div>
<div class="page"><h1>{{ $judul }}</h1><p>{{ $deskripsi }}</p>

<div class="tools">
    <input type="search" wire:model.live.debounce.400ms="cari" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}" class="cari-berita">
</div>
<div class="tools">
    <button type="button" class="chip {{ $filter === 'Semua' ? 'on' : '' }}" wire:click="{{ $aksiFilter }}('Semua')" aria-pressed="{{ $filter === 'Semua' ? 'true' : 'false' }}">Semua</button>
    @foreach ($daftarFilter as $f)
        <button type="button" class="chip {{ $filter === $f ? 'on' : '' }}" wire:key="filter-{{ $f }}" wire:click="{{ $aksiFilter }}(@js($f))" aria-pressed="{{ $filter === $f ? 'true' : 'false' }}">{{ $f }}</button>
    @endforeach
</div>

<div class="news">
@forelse ($kartu as $k)
    <a class="c kartu-berita" wire:key="{{ $k['kunci'] }}" href="{{ $k['url'] }}" wire:navigate>
        <div class="ph s" style="background:{{ $k['gambar'] ? 'url('.$k['gambar'].') center/cover no-repeat' : 'linear-gradient(135deg,#C93A74,#F28BB0)' }}">@unless ($k['gambar']){{ $k['ikon'] }}@endunless</div>
        <div class="cb">
            <span class="bd">{{ $k['label'] }}</span>
            <b>{{ $k['nama'] }}</b>
            <small><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $k['sub'] }}</small>
            <p class="cuplikan">{{ $k['cuplikan'] }}</p>
            <span class="selengkapnya">Selengkapnya <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </div>
    </a>
@empty
    <p class="kosong-berita">
        @if (trim($cari) !== '' || $filter !== 'Semua') Tidak ada data yang cocok dengan pencarian.
        @else Belum ada data.
        @endif
    </p>
@endforelse
</div>

{{ $daftar->links('livewire.publik.partials.pagination', ['scrollTo' => '.page']) }}
</div>
</div>
