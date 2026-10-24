<div>
    <x-admin.topbar judul="Ekonomi Kreatif" sub="Data pelaku usaha ekonomi kreatif dan UMKM" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama usaha, pemilik, atau alamat…" aria-label="Cari ekonomi kreatif">
        <select wire:model.live="subsektor" aria-label="Filter subsektor">
            <option value="">Semua subsektor</option>
            @foreach ($daftarSubsektor as $s)
                <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
        </select>
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.ekraf-form', 'buka-form-ekraf')">+ Tambah pelaku</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>Nama usaha</th>
                    <th>Pemilik</th>
                    <th>Subsektor</th>
                    <th>Alamat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $e)
                    <tr wire:key="ekraf-{{ $e->id_ekraf }}">
                        <td>{{ $e->nama_usaha }}</td>
                        <td>{{ $e->nama_pemilik }}</td>
                        <td><span class="bd g">{{ $e->subsektor }}</span></td>
                        <td>{{ $e->alamat }}</td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $e->id_ekraf }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.ekraf-form', 'buka-form-ekraf', { id: {{ $e->id_ekraf }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $e->id_ekraf }})"
                                        wire:confirm="Hapus data &quot;{{ $e->nama_usaha }}&quot;? Data ini dihapus permanen.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">
                        @if ($cari !== '' || $subsektor !== '') Tidak ada data yang cocok dengan pencarian.
                        @else Belum ada data ekonomi kreatif. Klik "+ Tambah pelaku" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-ekraf">
                <div class="mh">
                    <h2 id="judul-detail-ekraf">Detail pelaku ekonomi kreatif</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="info full"><small>Nama usaha</small><b>{{ $detail->nama_usaha }}</b></div>
                    <div class="info"><small>Nama pemilik</small><p>{{ $detail->nama_pemilik }}</p></div>
                    <div class="info"><small>Subsektor</small><p>{{ $detail->subsektor }}</p></div>
                    <div class="info full"><small>Alamat</small><p>{{ $detail->alamat }}</p></div>
                    <div class="info"><small>Nomor telepon</small><p>{{ $detail->no_telepon ?? '–' }}</p></div>
                    <div class="info">
                        <small>Media sosial</small>
                        <p>@if ($detail->media_sosial)<a href="{{ $detail->media_sosial }}" target="_blank" rel="noopener">{{ $detail->media_sosial }} ↗</a>@else – @endif</p>
                    </div>
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

    <livewire:admin.ekraf-form />
</div>
