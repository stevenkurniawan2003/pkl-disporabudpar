<div>
    <x-admin.topbar judul="Wisata" sub="Kelola tempat wisata, titik peta, dan galeri foto" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama atau alamat wisata…" aria-label="Cari wisata">
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.wisata-form', 'buka-form-wisata')">+ Tambah wisata</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th style="width:84px">Gambar</th>
                    <th>Wisata</th>
                    <th>Kategori</th>
                    <th>Alamat</th>
                    <th>Galeri</th>
                    <th>Unggulan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $w)
                    <tr wire:key="wisata-{{ $w->id }}">
                        <td>
                            @if ($w->gambar_url)
                                <img class="thumb-sm" src="{{ $w->gambar_url }}" alt="Gambar {{ $w->nama }}" loading="lazy">
                            @else
                                <div class="thumb-sm kosong" title="Belum ada gambar">{{ $w->kategori?->ikon ?? '📍' }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $w->nama }}
                            @if ($w->status === 'draf') <span class="bd warn">Draf</span> @endif
                        </td>
                        <td><span class="bd g">{{ $w->kategori?->nama }}</span></td>
                        <td>{{ $w->alamat }}, Kec. {{ $w->kecamatan?->nama }}</td>
                        <td>{{ $w->galeri_count }} foto</td>
                        <td>
                            <button type="button" class="tg {{ $w->unggulan ? 'on' : '' }}"
                                    wire:click="toggleUnggulan({{ $w->id }})"
                                    aria-label="Unggulan" aria-pressed="{{ $w->unggulan ? 'true' : 'false' }}"></button>
                        </td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $w->id }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.wisata-form', 'buka-form-wisata', { id: {{ $w->id }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $w->id }})"
                                        wire:confirm="Hapus wisata &quot;{{ $w->nama }}&quot;? Wisata ini juga hilang dari halaman publik.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">
                        @if ($cari !== '') Tidak ada wisata yang cocok dengan "{{ $cari }}".
                        @else Belum ada wisata. Klik "+ Tambah wisata" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-wisata">
                <div class="mh">
                    <h2 id="judul-detail-wisata">Detail wisata</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="fd full">
                        @if ($detail->gambar_url)
                            <img class="detail-utama" src="{{ $detail->gambar_url }}" alt="Gambar utama {{ $detail->nama }}">
                        @else
                            <p class="info-kosong">Belum ada gambar utama.</p>
                        @endif
                    </div>

                    <div class="info full"><small>Nama wisata</small><b>{{ $detail->nama }}</b></div>
                    <div class="info"><small>Kategori</small><p>{{ $detail->kategori?->nama ?? '–' }}</p></div>
                    <div class="info"><small>Kecamatan</small><p>{{ $detail->kecamatan?->nama ?? '–' }}</p></div>
                    <div class="info"><small>Status</small><p>{{ $detail->status === 'tayang' ? 'Tayang (tampil di publik)' : 'Draf (disembunyikan)' }}</p></div>
                    <div class="info"><small>Unggulan</small><p>{{ $detail->unggulan ? 'Ya, tampil di beranda' : 'Tidak' }}</p></div>
                    <div class="info full"><small>Deskripsi singkat</small><p>{{ $detail->deskripsi_singkat ?: '–' }}</p></div>
                    <div class="info full"><small>Narasi</small>@if (\App\Support\TeksKaya::kosong($detail->narasi))<p>–</p>@else<div class="isi-kaya">{!! \App\Support\TeksKaya::html($detail->narasi) !!}</div>@endif</div>
                    <div class="info full"><small>Alamat</small><p>{{ $detail->alamat }}, Kec. {{ $detail->kecamatan?->nama }}</p></div>
                    <div class="info"><small>Titik koordinat</small><p>{{ $detail->latitude }}, {{ $detail->longitude }}</p></div>
                    <div class="info">
                        <small>Link Google Maps</small>
                        <p>@if ($detail->link_google_maps)<a href="{{ $detail->link_google_maps }}" target="_blank" rel="noopener">Buka Google Maps ↗</a>@else – @endif</p>
                    </div>

                    <div class="info full">
                        <small>Galeri ({{ $detail->galeri->count() }} foto)</small>
                        @if ($detail->galeri->isNotEmpty())
                            <div class="galeri">
                                @foreach ($detail->galeri as $g)
                                    <a class="galeri-item" href="{{ $g->gambar_url }}" target="_blank" rel="noopener" wire:key="detail-galeri-{{ $g->id }}">
                                        <img src="{{ $g->gambar_url }}" alt="{{ $g->keterangan ?: 'Foto galeri '.$detail->nama }}" loading="lazy">
                                        @if ($g->keterangan) <span class="galeri-ket">{{ $g->keterangan }}</span> @endif
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p>Belum ada foto galeri.</p>
                        @endif
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutupDetail">Tutup</button>
                    <button type="button" class="btn p" wire:click="ubahDariDetail">Ubah</button>
                </div>
            </div>
        </div>
    @endif

    <livewire:admin.wisata-form />
</div>
