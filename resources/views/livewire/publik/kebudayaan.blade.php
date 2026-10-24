<div>
<div class="page"><h1>Kebudayaan</h1><p>Ragam kesenian yang tumbuh dan dilestarikan di Kabupaten Jember.</p>

<div class="tools">
    <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari nama kesenian atau asal daerah…" aria-label="Cari kesenian">
    <button type="button" class="chip {{ $kategori === 'Semua' ? 'on' : '' }}" wire:click="pilihKategori('Semua')" aria-pressed="{{ $kategori === 'Semua' ? 'true' : 'false' }}">Semua</button>
    @foreach ($daftarKategori as $k)
        <button type="button" class="chip {{ $kategori === $k ? 'on' : '' }}" wire:key="kat-{{ $k }}" wire:click="pilihKategori(@js($k))" aria-pressed="{{ $kategori === $k ? 'true' : 'false' }}">{{ $k }}</button>
    @endforeach
</div>

<div class="news">
@forelse ($daftar as $k)
    <a class="c" wire:key="kesenian-{{ $k->id_kesenian }}" href="{{ route('publik.detail-kesenian', $k) }}" wire:navigate>
        <div class="ph s" style="background:{{ $k->gradien }}" aria-hidden="true">{{ $k->ikon }}</div>
        <div class="cb">
            <span class="bd">{{ $k->kategori }}</span>
            <b>{{ $k->nama_kesenian }}</b>
            @if ($k->asal_daerah)<small><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $k->asal_daerah }}</small>@endif
            <p class="cuplikan">{{ $k->cuplikan }}</p>
        </div>
    </a>
@empty
    <p class="kosong-berita">
        @if (trim($cari) !== '')
            Tidak ada kesenian yang cocok dengan "{{ $cari }}"{{ $kategori !== 'Semua' ? ' pada kategori '.$kategori : '' }}.
        @else
            Belum ada data kesenian{{ $kategori !== 'Semua' ? ' pada kategori '.$kategori : '' }}.
        @endif
    </p>
@endforelse
</div>

@if ($daftar->hasPages())
<div class="tools" style="justify-content:center;margin-top:20px">
    <button type="button" class="chip" wire:click="previousPage" @disabled($daftar->onFirstPage())>‹ Sebelumnya</button>
    <span style="color:var(--mut)">Halaman {{ $daftar->currentPage() }} dari {{ $daftar->lastPage() }}</span>
    <button type="button" class="chip" wire:click="nextPage" @disabled(! $daftar->hasMorePages())>Berikutnya ›</button>
</div>
@endif
</div>
</div>
