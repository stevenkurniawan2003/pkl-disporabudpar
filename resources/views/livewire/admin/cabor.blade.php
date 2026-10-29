<div>
    <x-admin.topbar judul="Database Cabor" sub="Cabang olahraga Kabupaten Jember beserta jumlah atlet dan pelatih aktif" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <details class="card" style="margin-bottom:16px" open>
        <summary style="cursor:pointer;font-weight:600">Grafik Database Cabor</summary>
        <div style="margin-top:14px">@include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])</div>
    </details>

    @include('livewire.admin.partials.alat-ppo', ['jenis' => 'cabor', 'placeholder' => 'Cari nama cabor atau induk organisasi…', 'tambah' => ['admin.cabor-form', 'buka-form-cabor', '+ Tambah Cabor']])

    <div class="tw">
        <table>
            <thead>
                <tr><th>Cabang olahraga</th><th>Induk organisasi</th><th>Ketua Pengkab</th><th>Kecamatan</th><th class="n">Atlet aktif</th><th class="n">Pelatih aktif</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($daftar as $c)
                    <tr wire:key="cabor-{{ $c->id_cabor }}">
                        <td><b>{{ $c->nama_cabor }}</b></td>
                        <td>{{ $c->induk_organisasi }}</td>
                        <td>{{ $c->ketua_pengkab }}</td>
                        <td>{{ $c->kecamatan->nama ?? '–' }}</td>
                        <td class="n">{{ (int) $c->jumlah_atlet_aktif }}</td>
                        <td class="n">{{ (int) $c->jumlah_pelatih_aktif }}</td>
                        <td><span class="bd {{ $c->status === 'aktif' ? 'g' : 'warn' }}">{{ $c->status_label }}</span></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $c->id_cabor }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.cabor-form', 'buka-form-cabor', { id: {{ $c->id_cabor }} })">Edit</button>
                                <button type="button" class="btn s d" wire:click="hapus({{ $c->id_cabor }})" wire:confirm="Hapus data cabor &quot;{{ $c->nama_cabor }}&quot;? Data atlet dan pelatih cabor ini tidak ikut terhapus.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">
                        @if ($cari !== '' || $cabor !== '') Tidak ada cabor yang cocok dengan filter.
                        @else Belum ada data cabor. Klik "+ Tambah Cabor" untuk mulai.
                        @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pg"><span>{{ $daftar->count() }} cabor terdaftar dari 50 cabor di Jember</span></div>

    @if ($detail)
        <div class="ov on" wire:click.self="tutupDetail" x-on:keydown.escape.window="$wire.tutupDetail()">
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-cabor">
                <div class="mh">
                    <h2 id="judul-detail-cabor">Detail cabor</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>
                <div class="mb">
                    <div class="info"><small>Cabang olahraga</small><b>{{ $detail->nama_cabor }}</b></div>
                    <div class="info"><small>Induk organisasi</small><p>{{ $detail->induk_organisasi }} ({{ $detail->kepanjangan_induk }})</p></div>
                    <div class="info"><small>Ketua Pengkab</small><p>{{ $detail->ketua_pengkab }}</p></div>
                    <div class="info"><small>Status keaktifan</small><p>{{ $detail->status_label }}</p></div>
                    <div class="info full"><small>Alamat sekretariat</small><p>{{ $detail->alamat_sekretariat }}, Kec. {{ $detail->kecamatan->nama ?? '–' }}</p></div>
                    <div class="info">
                        <small>Titik sekretariat</small>
                        <p>@if ($detail->latitude !== null)<a href="https://www.google.com/maps?q={{ $detail->latitude }},{{ $detail->longitude }}" target="_blank" rel="noopener">{{ $detail->latitude }}, {{ $detail->longitude }} ↗</a>@else – @endif</p>
                    </div>
                    <div class="info"><small>Info kontak</small><p>{{ $detail->kontak ?? '–' }}</p></div>
                    <div class="info"><small>Jumlah atlet aktif</small><p>{{ (int) $detail->jumlah_atlet_aktif }}</p></div>
                    <div class="info"><small>Jumlah pelatih aktif</small><p>{{ (int) $detail->jumlah_pelatih_aktif }}</p></div>
                    <div class="info full"><small>Prestasi terakhir</small><p>{{ $detail->prestasi_terakhir ?? '–' }}</p></div>
                </div>
                <div class="mf">
                    <button type="button" class="btn" wire:click="tutupDetail">Tutup</button>
                    <button type="button" class="btn p" wire:click="ubahDariDetail">Edit</button>
                </div>
            </div>
        </div>
    @endif

    <livewire:admin.cabor-form />
</div>
