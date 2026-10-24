<div>
    <x-admin.topbar judul="Kesenian" sub="Data kesenian Kabupaten Jember untuk halaman Kebudayaan" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama atau asal daerah kesenian…" aria-label="Cari kesenian">
        <select wire:model.live="kategori" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach ($daftarKategori as $k)
                <option value="{{ $k }}">{{ $k }}</option>
            @endforeach
        </select>
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.kesenian-form', 'buka-form-kesenian')">+ Tambah kesenian</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>Nama kesenian</th>
                    <th>Kategori</th>
                    <th>Asal daerah</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $k)
                    <tr wire:key="kesenian-{{ $k->id_kesenian }}">
                        <td>{{ $k->nama_kesenian }}</td>
                        <td><span class="bd g">{{ $k->kategori }}</span></td>
                        <td>{{ $k->asal_daerah ?? '–' }}</td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $k->id_kesenian }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.kesenian-form', 'buka-form-kesenian', { id: {{ $k->id_kesenian }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $k->id_kesenian }})"
                                        wire:confirm="Hapus kesenian &quot;{{ $k->nama_kesenian }}&quot;? Data ini dihapus permanen.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">
                        @if ($cari !== '' || $kategori !== '') Tidak ada kesenian yang cocok dengan pencarian.
                        @else Belum ada data kesenian. Klik "+ Tambah kesenian" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-kesenian">
                <div class="mh">
                    <h2 id="judul-detail-kesenian">Detail kesenian</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="info full"><small>Nama kesenian</small><b>{{ $detail->nama_kesenian }}</b></div>
                    <div class="info"><small>Kategori</small><p>{{ $detail->kategori }}</p></div>
                    <div class="info"><small>Asal daerah</small><p>{{ $detail->asal_daerah ?? '–' }}</p></div>
                    <div class="info full"><small>Deskripsi</small><p>{!! nl2br(e($detail->deskripsi)) !!}</p></div>
                    <div class="info"><small>Ditambahkan</small><p>{{ $detail->created_at?->locale('id')->translatedFormat('j F Y, H:i') ?? '–' }}</p></div>
                    <div class="info"><small>Terakhir diubah</small><p>{{ $detail->updated_at?->locale('id')->translatedFormat('j F Y, H:i') ?? '–' }}</p></div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutupDetail">Tutup</button>
                    <button type="button" class="btn p" wire:click="ubahDariDetail">Ubah</button>
                </div>
            </div>
        </div>
    @endif

    <livewire:admin.kesenian-form />
</div>
