<div>
<div class="page"><h1>Berita</h1><p>Kabar terbaru dari Disporabudpar Jember.</p>

<div class="tools">
    <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari judul berita…" aria-label="Cari judul berita" class="cari-berita">
</div>

<div class="tools">
    <button type="button" class="chip {{ $kategori === 'Semua' ? 'on' : '' }}" wire:click="pilihKategori('Semua')">Semua</button>
    @foreach ($daftarKategori as $k)
        <button type="button" class="chip {{ $kategori === $k->nama ? 'on' : '' }}" wire:key="kat-{{ $k->id }}" wire:click="pilihKategori('{{ $k->nama }}')">{{ $k->nama }}</button>
    @endforeach
</div>

<div class="news">
@forelse ($daftar as $b)
    @include('livewire.publik.partials.kartu-berita', ['b' => $b, 'cuplikan' => true])
@empty
    <p class="kosong-berita">
        @if (trim($cari) !== '')
            Tidak ada berita dengan judul "{{ $cari }}"{{ $kategori !== 'Semua' ? ' pada kategori '.$kategori : '' }}.
        @else
            Belum ada berita{{ $kategori !== 'Semua' ? ' pada kategori '.$kategori : '' }}.
        @endif
    </p>
@endforelse
</div>

{{-- Pagination Livewire: pindah halaman tanpa memuat ulang, lalu gulir ke atas daftar. --}}
{{ $daftar->links('livewire.publik.partials.pagination', ['scrollTo' => '.page']) }}
</div>
</div>
