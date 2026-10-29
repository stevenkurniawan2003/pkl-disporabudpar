<div>
<div class="page"><h1>Paket Wisata</h1><p>Paket wisata Jember terbaru. Klik flyer untuk memperbesar, hubungi contact person untuk pemesanan.</p>

<div class="news paket">
@forelse ($daftar as $i => $p)
    <article class="c kartu-berita" wire:key="paket-{{ $p->id_paket }}">
        <button type="button" class="dd-foto flyer" data-i="{{ $i }}" aria-label="Perbesar flyer {{ $p->nama_paket }}">
            <img src="{{ $p->flyer_url }}" alt="Flyer {{ $p->nama_paket }}" loading="lazy">
        </button>
        <div class="cb">
            <b>{{ $p->nama_paket }}</b>
            @if ($p->durasi || $p->harga)
                <small>{{ collect([$p->durasi, $p->harga])->filter()->implode(' · ') }}</small>
            @endif
            <p class="paket-deskripsi">{{ $p->deskripsi }}</p>
            <div class="paket-cp">
                <span><i class="bi bi-person" aria-hidden="true"></i> CP: {{ $p->nama_cp }} ({{ $p->no_cp }})</span>
                <a class="btn p" href="{{ $p->tautan_wa }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp" aria-hidden="true"></i> Hubungi via WhatsApp</a>
            </div>
        </div>
    </article>
@empty
    <p class="kosong-berita">Belum ada paket wisata.</p>
@endforelse
</div>

@if ($flyer)
    @include('livewire.publik.partials.lightbox', ['galeri' => $flyer, 'nama' => 'Paket Wisata'])
@endif

{{ $daftar->links('livewire.publik.partials.pagination', ['scrollTo' => '.page']) }}
</div>
</div>
