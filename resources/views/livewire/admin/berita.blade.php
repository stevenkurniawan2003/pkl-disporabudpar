<div>
    <x-admin.topbar judul="Berita" sub="Tulis dan terbitkan berita" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari judul berita…" aria-label="Cari berita">
        <select wire:model.live="status" aria-label="Filter status">
            <option value="">Semua status</option>
            <option value="terbit">Terbit</option>
            <option value="draf">Draf</option>
        </select>
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.berita-form', 'buka-form-berita')">+ Tulis berita</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr><th style="width:84px">Gambar</th><th>Judul</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($daftar as $b)
                    <tr wire:key="berita-{{ $b->id }}">
                        <td>
                            @if ($b->gambar_url)
                                <img class="thumb-sm" src="{{ $b->gambar_url }}" alt="Sampul {{ $b->judul }}" loading="lazy">
                            @else
                                <div class="thumb-sm kosong" title="Belum ada gambar sampul">📰</div>
                            @endif
                        </td>
                        <td>{{ $b->judul }}</td>
                        <td><span class="bd g">{{ $b->kategori?->nama }}</span></td>
                        <td>{{ $b->tanggal_terbit?->locale('id')->translatedFormat('j M Y') ?? '—' }}</td>
                        <td>
                            @if ($b->status === 'draf')
                                <span class="bd g">Draf</span>
                            @elseif ($b->terjadwal)
                                <span class="bd warn" style="margin-left:0">Terjadwal</span>
                            @else
                                <span class="bd">Terbit</span>
                            @endif
                        </td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $b->id }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.berita-form', 'buka-form-berita', { id: {{ $b->id }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $b->id }})"
                                        wire:confirm="Hapus berita &quot;{{ $b->judul }}&quot;? Berita ini juga hilang dari halaman publik.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">
                        @if ($cari !== '' || $status !== '') Tidak ada berita yang cocok dengan pencarian.
                        @else Belum ada berita. Klik "+ Tulis berita" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-berita">
                <div class="mh">
                    <h2 id="judul-detail-berita">Detail berita</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="fd full">
                        @if ($detail->gambar_url)
                            <img class="detail-utama" src="{{ $detail->gambar_url }}" alt="Sampul {{ $detail->judul }}">
                        @else
                            <p class="info-kosong">Belum ada gambar sampul.</p>
                        @endif
                    </div>

                    <div class="info full"><small>Judul</small><b>{{ $detail->judul }}</b></div>
                    <div class="info"><small>Kategori</small><p>{{ $detail->kategori?->nama ?? '–' }}</p></div>
                    <div class="info">
                        <small>Status</small>
                        <p>
                            @if ($detail->status === 'draf') Draf (disembunyikan)
                            @elseif ($detail->terjadwal) Terjadwal, tampil mulai {{ $detail->tanggal_teks }}
                            @else Terbit (tampil di publik)
                            @endif
                        </p>
                    </div>
                    <div class="info"><small>Tanggal terbit</small><p>{{ $detail->tanggal_teks ?: '–' }}</p></div>
                    <div class="info"><small>Dibaca</small><p>{{ number_format($detail->jumlah_dibaca ?? 0, 0, ',', '.') }} kali</p></div>
                    <div class="info"><small>Penulis</small><p>{{ $detail->penulis?->name ?? '–' }}</p></div>
                    <div class="info">
                        <small>Halaman publik</small>
                        <p>
                            @if ($detail->sedangTayang())
                                <a href="{{ route('publik.detail-berita', $detail) }}" target="_blank" rel="noopener">Lihat di situs ↗</a>
                            @else
                                Belum tampil di publik
                            @endif
                        </p>
                    </div>
                    <div class="info full"><small>Ringkasan</small><p>{{ $detail->ringkasan_tampil ?: '–' }}</p></div>
                    <div class="info full">
                        <small>Isi berita</small>
                        <div class="isi-berita">
                            @foreach ($detail->paragraf as $p)
                                <p>{!! nl2br(e($p)) !!}</p>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutupDetail">Tutup</button>
                    <button type="button" class="btn p" wire:click="ubahDariDetail">Ubah</button>
                </div>
            </div>
        </div>
    @endif

    <livewire:admin.berita-form />
</div>
