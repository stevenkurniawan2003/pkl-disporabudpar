<div>
<div class="page">
    @include('livewire.publik.profil._banner')

    <div class="profil-isi">
        <article class="art">
            <h2>Sekilas tentang dinas</h2>
            <p class="ph-teks">[ISI SEJARAH SINGKAT DINAS DI SINI]</p>
            <p class="ph-teks">[ISI GAMBARAN UMUM DINAS DI SINI: dasar hukum pembentukan, kedudukan, dan peran dinas di Kabupaten Jember]</p>

            <h2>Bidang yang dinaungi</h2>
            <p class="ph-teks">[ISI PENJELASAN SINGKAT BIDANG KEPEMUDAAN, OLAHRAGA, KEBUDAYAAN, DAN PARIWISATA DI SINI]</p>
        </article>

        <aside class="profil-kartu" aria-labelledby="judul-kantor">
            <h2 id="judul-kantor">Kantor dinas</h2>
            <ul class="dd-data">
                <li><i class="bi bi-building" aria-hidden="true"></i><span>{{ config('site.nama_dinas') }}</span></li>
                <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ config('site.kontak.alamat') }}</span></li>
                @if (filled(config('site.kontak.telepon')))
                    <li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', config('site.kontak.telepon')) }}">{{ config('site.kontak.telepon') }}</a></li>
                @endif
                @if (filled(config('site.kontak.email')))
                    <li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:{{ config('site.kontak.email') }}">{{ config('site.kontak.email') }}</a></li>
                @endif
                <li><i class="bi bi-clock" aria-hidden="true"></i><span class="ph-teks">[ISI JAM LAYANAN DI SINI]</span></li>
            </ul>
        </aside>
    </div>
</div>
</div>
