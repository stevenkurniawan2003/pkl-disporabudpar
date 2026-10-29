<div>
<div class="page uptd">
    <div class="uptd-kepala">
        <small>{{ config('site.nama_dinas') }}</small>
        <h1>{{ $d['judul'] }}</h1>
        <p>{{ $d['ringkas'] }}</p>
    </div>

    <section aria-labelledby="judul-tentang-uptd">
        <h2 class="judul-tabel" id="judul-tentang-uptd">Tentang UPTD</h2>
        <p>{{ $d['tentang'] }}</p>
        <div class="ringkas empat">
            @foreach ($d['angka'] as [$angka, $ket])
                <div class="ringkas-kartu"><b>{{ $angka }}</b><span>{{ $ket }}</span></div>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="judul-destinasi">
        <h2 class="judul-tabel" id="judul-destinasi">Empat destinasi wisata unggulan</h2>
        @foreach ($d['destinasi'] as $i => $dest)
            <article class="uptd-destinasi" wire:key="destinasi-{{ $i }}" aria-labelledby="dest-{{ $i }}">
                <div class="uptd-dest-kiri">
                    <span class="uptd-no">{{ sprintf('%02d', $i + 1) }}</span>
                    <i class="bi bi-{{ $dest['ikon'] }}" aria-hidden="true"></i>
                    <h3 id="dest-{{ $i }}">{{ $dest['nama'] }}</h3>
                    <small>{{ $dest['jenis'] }}</small>
                    <small><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $dest['lokasi'] }}</small>
                </div>
                <div class="uptd-dest-isi">
                    <p>{{ $dest['tentang'] }}</p>
                    <div class="uptd-fasilitas dua">
                        @foreach ($dest['fasilitas'] as [$nama, $ket])
                            <div><b>{{ $nama }}</b><small>{{ $ket }}</small></div>
                        @endforeach
                    </div>
                    <p class="uptd-jam"><span><i class="bi bi-clock" aria-hidden="true"></i> Buka: {{ $dest['buka'] }}</span><span><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Tiket: {{ $dest['tiket'] }}</span></p>
                </div>
            </article>
        @endforeach
    </section>

    <section aria-labelledby="judul-fasilitas-umum">
        <h2 class="judul-tabel" id="judul-fasilitas-umum">Fasilitas umum</h2>
        <p class="meta">Tersedia di destinasi wisata UPTD.</p>
        <div class="uptd-fasilitas">
            @foreach ($d['fasilitas_umum'] as [$ikon, $nama, $ket])
                <div><i class="bi bi-{{ $ikon }}" aria-hidden="true"></i><b>{{ $nama }}</b><small>{{ $ket }}</small></div>
            @endforeach
        </div>
    </section>

    <div class="dua-kolom-peta">
        <section aria-labelledby="judul-info-kunjungan">
            <h2 class="judul-tabel" id="judul-info-kunjungan">Jam operasional &amp; tiket</h2>
            <div class="tw">
                <table>
                    <tbody>
                        @foreach ($d['kunjungan'] as [$label, $isi])
                            <tr><th scope="row">{{ $label }}</th><td>{{ $isi }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        <section class="uptd-kontak" aria-labelledby="judul-kontak-wisata">
            <h2 class="judul-tabel" id="judul-kontak-wisata">Kontak &amp; lokasi</h2>
            <ul class="dd-data" style="margin-top:12px">
                <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span><b>Alamat:</b> {{ $d['kontak']['alamat'] }}</span></li>
                @if ($d['kontak']['telepon'])
                    <li><i class="bi bi-telephone" aria-hidden="true"></i><span><b>Kantor UPTD:</b> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $d['kontak']['telepon']) }}">{{ $d['kontak']['telepon'] }}</a> ({{ $d['kontak']['jam'] }})</span></li>
                @endif
                <li><i class="bi bi-envelope" aria-hidden="true"></i><span><b>Email:</b> <a href="mailto:{{ $d['kontak']['email'] }}">{{ $d['kontak']['email'] }}</a></span></li>
                <li><i class="bi bi-globe" aria-hidden="true"></i><span><b>Website:</b> <a href="{{ $d['kontak']['website'] }}" target="_blank" rel="noopener noreferrer">{{ preg_replace('#^https?://#', '', $d['kontak']['website']) }}</a></span></li>
                <li><i class="bi bi-instagram" aria-hidden="true"></i><span><b>Media sosial:</b> {{ $d['kontak']['medsos'] }}</span></li>
            </ul>
        </section>
    </div>

    <p class="uptd-penutup">Jelajahi pesona Jember bersama kami.</p>
</div>
</div>
