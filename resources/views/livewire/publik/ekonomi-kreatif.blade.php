<div>
<div class="page"><h1>Ekonomi kreatif</h1><p>Pelaku dan usaha ekonomi kreatif di Kabupaten Jember.</p>

<div class="tools">
    <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari nama usaha atau alamat…" aria-label="Cari usaha ekonomi kreatif">
    <button type="button" class="chip {{ $subsektor === 'Semua' ? 'on' : '' }}" wire:click="pilihSubsektor('Semua')" aria-pressed="{{ $subsektor === 'Semua' ? 'true' : 'false' }}">Semua</button>
    @foreach ($daftarSubsektor as $s)
        <button type="button" class="chip {{ $subsektor === $s ? 'on' : '' }}" wire:key="sub-{{ $s }}" wire:click="pilihSubsektor(@js($s))" aria-pressed="{{ $subsektor === $s ? 'true' : 'false' }}">{{ $s }}</button>
    @endforeach
</div>

<div class="news">
@forelse ($daftar as $e)
    {{-- Seluruh kartu bisa diklik lewat tautan judul (::after), tanpa menyarangkan tautan telepon/medsos di dalam <a>. --}}
    <article class="c kartu-ekraf" wire:key="ekraf-{{ $e->id_ekraf }}">
        <div class="ph s" style="background:{{ $e->gradien }}" aria-hidden="true">{{ $e->ikon }}</div>
        <div class="cb">
            <span class="bd">{{ $e->subsektor }}</span>
            <b><a class="tautan-kartu" href="{{ route('publik.detail-ekraf', $e) }}" wire:navigate>{{ $e->nama_usaha }}</a></b>
            <small><i class="bi bi-person" aria-hidden="true"></i> {{ $e->nama_pemilik }}</small>
            <small><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $e->alamat }}</small>
            @if ($e->no_telepon || $e->platform_sosmed)
                <div class="aksi-ekraf">
                    @if ($e->no_telepon)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $e->no_telepon) }}" aria-label="Telepon {{ $e->nama_usaha }}: {{ $e->no_telepon }}"><i class="bi bi-telephone" aria-hidden="true"></i> {{ $e->no_telepon }}</a>
                    @endif
                    @if ($e->platform_sosmed)
                        <a href="{{ $e->media_sosial }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $e->platform_sosmed['nama'] }} {{ $e->nama_usaha }} (tab baru)"><i class="bi bi-{{ $e->platform_sosmed['ikon'] }}" aria-hidden="true"></i> {{ $e->platform_sosmed['nama'] }}</a>
                    @endif
                </div>
            @endif
        </div>
    </article>
@empty
    <p class="kosong-berita">
        @if (trim($cari) !== '')
            Tidak ada usaha yang cocok dengan "{{ $cari }}"{{ $subsektor !== 'Semua' ? ' pada subsektor '.$subsektor : '' }}.
        @else
            Belum ada data ekonomi kreatif{{ $subsektor !== 'Semua' ? ' pada subsektor '.$subsektor : '' }}.
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
