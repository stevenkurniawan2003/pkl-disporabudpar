<div>
<div class="page">
    @include('livewire.publik.profil._banner')

    @if ($adaBagan)
        <figure class="bagan">
            <button type="button" class="bagan-buka" aria-label="Perbesar bagan struktur organisasi">
                <img src="{{ asset('images/struktur-organisasi.png') }}" alt="Bagan struktur organisasi {{ config('site.nama_dinas') }}">
                <span class="bagan-zoom"><i class="bi bi-zoom-in" aria-hidden="true"></i> Klik untuk memperbesar</span>
            </button>
            <figcaption class="meta">Bagan struktur organisasi {{ config('site.nama_dinas') }}.</figcaption>
        </figure>

        <dialog class="lightbox" id="lightbox-bagan" aria-label="Bagan struktur organisasi">
            <figure><img src="{{ asset('images/struktur-organisasi.png') }}" alt="Bagan struktur organisasi {{ config('site.nama_dinas') }}"></figure>
            <button type="button" class="lb-btn lb-tutup" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
        </dialog>
    @else
        <div class="segera">
            <span class="segera-ikon" aria-hidden="true">🗂</span>
            <h2>Bagan struktur organisasi belum tersedia</h2>
            <p class="ph-teks">[SIMPAN GAMBAR BAGAN DI public/images/struktur-organisasi.png]</p>
        </div>
    @endif
</div>

@script
<script>
    const kotak = document.getElementById('lightbox-bagan');
    if (kotak) {
        $wire.$el.querySelector('.bagan-buka').addEventListener('click', () => kotak.showModal());
        kotak.querySelector('.lb-tutup').addEventListener('click', () => kotak.close());
        kotak.addEventListener('click', (e) => { if (e.target === kotak) kotak.close(); }); // klik di luar gambar
    }
</script>
@endscript
</div>
