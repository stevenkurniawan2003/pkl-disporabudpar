<div>
    <x-admin.topbar judul="Hotel" sub="Kelola data hotel, titik peta, dan galeri foto" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama atau alamat hotel…" aria-label="Cari hotel">
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.hotel-form', 'buka-form-hotel')">+ Tambah hotel</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th style="width:84px">Gambar</th>
                    <th>Nama hotel</th>
                    <th>Alamat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $h)
                    <tr wire:key="hotel-{{ $h->id_hotel }}">
                        <td><img class="thumb-sm" src="{{ $h->gambar_url }}" alt="Gambar {{ $h->nama_hotel }}" loading="lazy"></td>
                        <td>{{ $h->nama_hotel }}</td>
                        <td>{{ $h->alamat }}</td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $h->id_hotel }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.hotel-form', 'buka-form-hotel', { id: {{ $h->id_hotel }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $h->id_hotel }})"
                                        wire:confirm="Hapus hotel &quot;{{ $h->nama_hotel }}&quot;? Gambar utama, semua foto galeri, dan data kunjungan hotel ini ikut terhapus permanen.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">
                        @if ($cari !== '') Tidak ada hotel yang cocok dengan "{{ $cari }}".
                        @else Belum ada hotel. Klik "+ Tambah hotel" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-hotel">
                <div class="mh">
                    <h2 id="judul-detail-hotel">Detail hotel</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="fd full">
                        <img class="detail-utama" src="{{ $detail->gambar_url }}" alt="Gambar utama {{ $detail->nama_hotel }}">
                    </div>

                    <div class="info full"><small>Nama hotel</small><b>{{ $detail->nama_hotel }}</b></div>
                    <div class="info full"><small>Deskripsi singkat</small><p>{{ $detail->deskripsi_singkat }}</p></div>
                    <div class="info full"><small>Narasi</small><div class="isi-kaya">{!! \App\Support\TeksKaya::html($detail->narasi) !!}</div></div>
                    <div class="info full"><small>Alamat</small><p>{{ $detail->alamat }}</p></div>
                    <div class="info"><small>Titik koordinat</small><p>{{ $detail->latitude }}, {{ $detail->longitude }}</p></div>
                    <div class="info"><small>Nomor telepon</small><p>{{ $detail->no_telepon ?? '–' }}</p></div>
                    <div class="info">
                        <small>Link Google Maps</small>
                        <p>@if ($detail->link_google_maps)<a href="{{ $detail->link_google_maps }}" target="_blank" rel="noopener">Buka Google Maps ↗</a>@else – @endif</p>
                    </div>
                    <div class="info">
                        <small>Link website</small>
                        <p>@if ($detail->link_website)<a href="{{ $detail->link_website }}" target="_blank" rel="noopener">{{ $detail->link_website }} ↗</a>@else – @endif</p>
                    </div>

                    <div class="info full">
                        <small>Galeri ({{ $detail->galeri->count() }} foto)</small>
                        @if ($detail->galeri->isNotEmpty())
                            <div class="galeri">
                                @foreach ($detail->galeri as $g)
                                    <a class="galeri-item" href="{{ $g->gambar_url }}" target="_blank" rel="noopener" wire:key="detail-galeri-{{ $g->id_galeri_hotel }}">
                                        <img src="{{ $g->gambar_url }}" alt="Foto galeri {{ $detail->nama_hotel }}" loading="lazy">
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

    <livewire:admin.hotel-form />
</div>
