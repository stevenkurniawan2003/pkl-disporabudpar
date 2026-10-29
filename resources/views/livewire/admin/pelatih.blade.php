<div>
    <x-admin.topbar judul="Database Pelatih" sub="Bidang Peningkatan Prestasi Olahraga (PPO) Kabupaten Jember" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <details class="card" style="margin-bottom:16px" open>
        <summary style="cursor:pointer;font-weight:600">Grafik jumlah pelatih</summary>
        <div style="margin-top:14px">@include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])</div>
    </details>

    @include('livewire.admin.partials.alat-ppo', ['jenis' => 'pelatih', 'placeholder' => 'Cari nama pelatih atau cabor…', 'tambah' => ['admin.pelatih-form', 'buka-form-pelatih', '+ Tambah Pelatih']])

    <div class="tw">
        <table>
            <thead>
                <tr><th>Nama lengkap</th><th>L/P</th><th class="n">Umur</th><th>Cabor</th><th>Kecamatan</th><th>Sertifikat/lisensi</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($daftar as $p)
                    <tr wire:key="pelatih-{{ $p->id_pelatih }}">
                        <td>{{ $p->nama_lengkap }}</td>
                        <td>{{ $p->jenis_kelamin }}</td>
                        <td class="n">{{ $p->umur }}</td>
                        <td>{{ $p->nama_cabor }}</td>
                        <td>{{ $p->kecamatan->nama ?? '–' }}</td>
                        <td>{{ $p->lisensi_label }}</td>
                        <td><span class="bd {{ $p->status === 'aktif' ? 'g' : 'warn' }}">{{ $p->status_label }}</span></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $p->id_pelatih }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.pelatih-form', 'buka-form-pelatih', { id: {{ $p->id_pelatih }} })">Edit</button>
                                <button type="button" class="btn s d" wire:click="hapus({{ $p->id_pelatih }})" wire:confirm="Hapus pelatih &quot;{{ $p->nama_lengkap }}&quot;? Berkas sertifikat/lisensinya ikut terhapus.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">
                        @if ($cari !== '' || $cabor !== '') Tidak ada pelatih yang cocok dengan filter.
                        @else Belum ada data pelatih. Klik "+ Tambah Pelatih" untuk mulai.
                        @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pg">
        <span>Menampilkan {{ $daftar->count() }} dari {{ $daftar->total() }} data</span>
        <span class="pgn">
            <button type="button" class="btn s" wire:click="previousPage" @disabled($daftar->onFirstPage())>‹ Sebelumnya</button>
            Halaman {{ $daftar->currentPage() }} dari {{ max($daftar->lastPage(), 1) }}
            <button type="button" class="btn s" wire:click="nextPage" @disabled(! $daftar->hasMorePages())>Berikutnya ›</button>
        </span>
    </div>

    @if ($detail)
        <div class="ov on" wire:click.self="tutupDetail" x-on:keydown.escape.window="$wire.tutupDetail()">
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-pelatih">
                <div class="mh">
                    <h2 id="judul-detail-pelatih">Detail pelatih</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>
                <div class="mb">
                    <div class="info full"><small>Nama lengkap</small><b>{{ $detail->nama_lengkap }}</b></div>
                    <div class="info"><small>Tempat, tanggal lahir</small><p>{{ $detail->ttl }}</p></div>
                    <div class="info"><small>Umur</small><p>{{ $detail->umur }} tahun</p></div>
                    <div class="info"><small>Jenis kelamin</small><p>{{ $detail->jenis_kelamin_label }}</p></div>
                    <div class="info"><small>No. HP</small><p>{{ $detail->no_hp ?? '–' }}</p></div>
                    <div class="info full"><small>Alamat</small><p>{{ $detail->alamat }}, Kec. {{ $detail->kecamatan->nama ?? '–' }}</p></div>
                    <div class="info"><small>Cabang olahraga</small><p>{{ $detail->nama_cabor }}</p></div>
                    <div class="info"><small>Status keaktifan</small><p>{{ $detail->status_label }}</p></div>
                    <div class="info"><small>Sertifikat/lisensi</small><p>{{ $detail->lisensi_label }}</p></div>
                    <div class="info">
                        <small>Berkas sertifikat/lisensi</small>
                        <p>@if ($detail->file_lisensi_url)<a href="{{ $detail->file_lisensi_url }}" target="_blank" rel="noopener">Lihat berkas ↗</a>@else – @endif</p>
                    </div>
                </div>
                <div class="mf">
                    <button type="button" class="btn" wire:click="tutupDetail">Tutup</button>
                    <button type="button" class="btn p" wire:click="ubahDariDetail">Edit</button>
                </div>
            </div>
        </div>
    @endif

    <livewire:admin.pelatih-form />
</div>
