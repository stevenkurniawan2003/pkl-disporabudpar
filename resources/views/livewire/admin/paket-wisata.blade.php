<div>
    <x-admin.topbar judul="Paket Wisata" sub="Paket wisata Jember terbaru beserta flyer dan contact person" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama paket…" aria-label="Cari paket wisata">
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.paket-wisata-form', 'buka-form-paket')">+ Tambah paket</button>
    </div>

    <div class="tw">
        <table>
            <thead><tr><th style="width:84px">Flyer</th><th>Nama paket</th><th>Harga</th><th>Durasi</th><th>CP</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($daftar as $p)
                    <tr wire:key="paket-{{ $p->id_paket }}">
                        <td><a href="{{ $p->flyer_url }}" target="_blank" rel="noopener"><img class="thumb-sm" src="{{ $p->flyer_url }}" alt="Flyer {{ $p->nama_paket }}" loading="lazy"></a></td>
                        <td>{{ $p->nama_paket }}</td>
                        <td>{{ $p->harga ?? '–' }}</td>
                        <td>{{ $p->durasi ?? '–' }}</td>
                        <td>{{ $p->nama_cp }}<br><small style="color:var(--mut)">{{ $p->no_cp }}</small></td>
                        <td><span class="bd {{ $p->tayang ? 'g' : 'warn' }}">{{ $p->tayang ? 'Tayang' : 'Disembunyikan' }}</span></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.paket-wisata-form', 'buka-form-paket', { id: {{ $p->id_paket }} })">Ubah</button>
                                <button type="button" class="btn s d" wire:click="hapus({{ $p->id_paket }})" wire:confirm="Hapus paket &quot;{{ $p->nama_paket }}&quot;? Flyernya ikut terhapus.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">Belum ada paket wisata. Klik "+ Tambah paket" untuk mulai.</td></tr>
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

    <livewire:admin.paket-wisata-form />
</div>
