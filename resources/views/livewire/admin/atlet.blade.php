<div>
    <x-admin.topbar judul="Database Atlet" sub="Bidang Peningkatan Prestasi Olahraga (PPO) Kabupaten Jember" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <details class="card" style="margin-bottom:16px" open>
        <summary style="cursor:pointer;font-weight:600">Grafik jumlah atlet</summary>
        <div style="margin-top:14px">@include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])</div>
    </details>

    @include('livewire.admin.partials.alat-ppo', ['jenis' => 'atlet', 'placeholder' => 'Cari nama atlet atau cabor…', 'tambah' => ['admin.atlet-form', 'buka-form-atlet', '+ Tambah Atlet']])

    <div class="tw">
        <table>
            <thead>
                <tr><th>Nama lengkap</th><th>L/P</th><th class="n">Umur</th><th>Cabor</th><th>Klub</th><th>Kecamatan</th><th>Status binaan</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($daftar as $a)
                    <tr wire:key="atlet-{{ $a->id_atlet }}">
                        <td>{{ $a->nama_lengkap }}</td>
                        <td>{{ $a->jenis_kelamin }}</td>
                        <td class="n">{{ $a->umur }}</td>
                        <td>{{ $a->nama_cabor }}</td>
                        <td>{{ $a->klub ?? '–' }}</td>
                        <td>{{ $a->kecamatan->nama ?? '–' }}</td>
                        <td>{{ $a->status_binaan }}</td>
                        <td><span class="bd {{ $a->status === 'aktif' ? 'g' : 'warn' }}">{{ $a->status_label }}</span></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $a->id_atlet }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.atlet-form', 'buka-form-atlet', { id: {{ $a->id_atlet }} })">Edit</button>
                                <button type="button" class="btn s d" wire:click="hapus({{ $a->id_atlet }})" wire:confirm="Hapus atlet &quot;{{ $a->nama_lengkap }}&quot;?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty">
                        @if ($cari !== '' || $cabor !== '') Tidak ada atlet yang cocok dengan filter.
                        @else Belum ada data atlet. Klik "+ Tambah Atlet" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-atlet">
                <div class="mh">
                    <h2 id="judul-detail-atlet">Detail atlet</h2>
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
                    <div class="info"><small>Klub</small><p>{{ $detail->klub ?? '–' }}</p></div>
                    <div class="info"><small>Status binaan</small><p>{{ $detail->status_binaan }}</p></div>
                    <div class="info"><small>Status keaktifan</small><p>{{ $detail->status_label }}</p></div>
                    <div class="info full"><small>Prestasi terakhir</small><p>{{ $detail->prestasi_terakhir ?? '–' }}</p></div>
                </div>
                <div class="mf">
                    <button type="button" class="btn" wire:click="tutupDetail">Tutup</button>
                    <button type="button" class="btn p" wire:click="ubahDariDetail">Edit</button>
                </div>
            </div>
        </div>
    @endif

    <livewire:admin.atlet-form />
</div>
