<div>
<div class="page uptd">
    <div class="uptd-kepala">
        <small>{{ config('site.nama_dinas') }}</small>
        <h1>{{ $d['judul'] }}</h1>
        <p>{{ $d['ringkas'] }}</p>
    </div>

    <section aria-labelledby="judul-sarana">
        <h2 class="judul-tabel" id="judul-sarana">Sarana dan prasarana olahraga</h2>
        <div class="uptd-sarana">
            @foreach ($d['sarana'] as $i => [$nama, $foto])
                <figure wire:key="sarana-{{ $i }}">
                    @if ($foto && is_file(public_path('images/uptd/'.$foto)))
                        <img src="{{ asset('images/uptd/'.$foto) }}" alt="{{ $nama }}" loading="lazy">
                    @else
                        <span class="uptd-ph" aria-hidden="true"><i class="bi bi-trophy"></i></span>
                    @endif
                    <figcaption><span class="uptd-no">{{ sprintf('%02d', $i + 1) }}</span>{{ $nama }}</figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="judul-sop">
        <h2 class="judul-tabel" id="judul-sop">Informasi SOP peminjaman dan pemanfaatan sarana dan prasarana olahraga</h2>
        <div class="tw">
            <table class="tabel-sop">
                <thead>
                    <tr><th class="no">No</th><th>Uraian prosedur</th><th>Pelaksana</th><th>Kelengkapan</th><th>Waktu</th><th>Output</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    @foreach ($d['sop'] as $i => [$uraian, $pelaksana, $kelengkapan, $waktu, $output, $keterangan])
                        <tr wire:key="sop-{{ $i }}">
                            <td class="no">{{ $i + 1 }}</td>
                            <td>{{ $uraian }}</td>
                            <td>{{ implode(', ', $pelaksana) }}</td>
                            <td>
                                @if (count($kelengkapan) > 1)
                                    <ol>@foreach ($kelengkapan as $k)<li>{{ $k }}</li>@endforeach</ol>
                                @else
                                    {{ $kelengkapan[0] }}
                                @endif
                            </td>
                            <td style="white-space:nowrap">{{ $waktu }}</td>
                            <td>{{ $output }}</td>
                            <td>{{ $keterangan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section aria-labelledby="judul-fasilitas">
        <h2 class="judul-tabel" id="judul-fasilitas">Fasilitas yang tersedia di tempat sarana dan prasarana olahraga</h2>
        <div class="uptd-fasilitas">
            @foreach ($d['fasilitas'] as [$ikon, $nama, $ket])
                <div><i class="bi bi-{{ $ikon }}" aria-hidden="true"></i><b>{{ $nama }}</b><small>{{ $ket }}</small></div>
            @endforeach
        </div>
    </section>

    <section class="uptd-kontak" aria-labelledby="judul-kontak">
        <h2 class="judul-tabel" id="judul-kontak">Kontak &amp; lokasi</h2>
        <ul class="dd-data" style="margin-top:12px">
            <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span><b>Alamat:</b> {{ $d['kontak']['alamat'] }}</span></li>
            <li><i class="bi bi-telephone" aria-hidden="true"></i><span><b>Kantor UPTD:</b> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $d['kontak']['telepon']) }}">{{ $d['kontak']['telepon'] }}</a> ({{ $d['kontak']['jam'] }})</span></li>
        </ul>
    </section>
</div>
</div>
