{{--
    Pagination bernomor untuk halaman publik (dipakai daftar Berita).
    Pindah halaman lewat Livewire (gotoPage/previousPage/nextPage), jadi tanpa reload, lalu gulir ke $scrollTo.
--}}
@php
    $scrollTo = $scrollTo ?? 'body';
    $gulir = "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth' })";
    $nama = $paginator->getPageName();
@endphp
<div>
    @if ($paginator->hasPages())
        <nav class="pgn-publik" aria-label="Navigasi halaman">
            @if ($paginator->onFirstPage())
                <span class="pg-btn off" aria-disabled="true" aria-label="Halaman sebelumnya"><i class="bi bi-chevron-left" aria-hidden="true"></i></span>
            @else
                <button type="button" class="pg-btn" wire:click="previousPage('{{ $nama }}')" x-on:click="{{ $gulir }}" wire:loading.attr="disabled" aria-label="Halaman sebelumnya"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pg-btn titik" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pg-btn on" aria-current="page" wire:key="pg-{{ $nama }}-{{ $page }}">{{ $page }}</span>
                        @else
                            <button type="button" class="pg-btn" wire:key="pg-{{ $nama }}-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $nama }}')" x-on:click="{{ $gulir }}" aria-label="Halaman {{ $page }}">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" class="pg-btn" wire:click="nextPage('{{ $nama }}')" x-on:click="{{ $gulir }}" wire:loading.attr="disabled" aria-label="Halaman berikutnya"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
            @else
                <span class="pg-btn off" aria-disabled="true" aria-label="Halaman berikutnya"><i class="bi bi-chevron-right" aria-hidden="true"></i></span>
            @endif
        </nav>
        <p class="pg-info">Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} berita</p>
    @endif
</div>
