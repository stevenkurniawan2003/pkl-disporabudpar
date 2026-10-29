<div>
    <x-admin.topbar judul="Cagar Budaya" sub="Input data Objek Diduga Cagar Budaya (ODCB) dan Cagar Budaya (CB)" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <select wire:model.live="kategori" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach ($daftarKategori as $k)
                <option value="{{ $k }}">{{ $k }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" aria-label="Filter status">
            <option value="">ODCB &amp; CB</option>
            @foreach ($daftarStatus as $kode => $label)
                <option value="{{ $kode }}">{{ $kode }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama atau alamat…" aria-label="Cari ODCB/CB">
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.cagar-budaya-form', 'buka-form-cagar-budaya')">+ Tambah Data</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th class="n" style="width:48px">No.</th>
                    <th style="width:84px">Gambar utama</th>
                    <th>Nama ODCB/CB</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Tahun</th>
                    <th>Kondisi</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $i => $c)
                    <tr wire:key="cb-{{ $c->id_cagar_budaya }}">
                        <td class="n">{{ $daftar->firstItem() + $i }}.</td>
                        <td><img class="thumb-sm" src="{{ $c->gambar_url }}" alt="Gambar {{ $c->nama }}" loading="lazy"></td>
                        <td>{{ $c->nama }}<br><small style="color:var(--mut)">{{ $c->alamat }}</small></td>
                        <td>{{ $c->kategori }}</td>
                        <td><span class="bd {{ $c->status === 'CB' ? 'g' : '' }}">{{ $c->status }}</span></td>
                        <td>{{ $c->tahun ?? '–' }}</td>
                        <td><span class="bd {{ $c->kondisi === 'Terpelihara' ? 'g' : 'warn' }}">{{ $c->kondisi }}</span></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $c->id_cagar_budaya }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.cagar-budaya-form', 'buka-form-cagar-budaya', { id: {{ $c->id_cagar_budaya }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $c->id_cagar_budaya }})"
                                        wire:confirm="Hapus &quot;{{ $c->nama }}&quot;? Gambar utama dan semua gambar tambahan ikut terhapus permanen.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">
                        @if ($cari !== '' || $kategori !== '' || $status !== '') Tidak ada data yang cocok dengan filter.
                        @else Belum ada data ODCB/CB. Klik "+ Tambah Data" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-cb">
                <div class="mh">
                    <h2 id="judul-detail-cb">Detail ODCB/CB</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="fd full"><img class="detail-utama" src="{{ $detail->gambar_url }}" alt="Gambar utama {{ $detail->nama }}"></div>
                    <div class="info full"><small>Nama ODCB/CB</small><b>{{ $detail->nama }}</b></div>
                    <div class="info"><small>Kategori</small><p>{{ $detail->kategori }}</p></div>
                    <div class="info"><small>Status</small><p>{{ $detail->status }} ({{ $detail->status_label }})</p></div>
                    <div class="info"><small>Tahun</small><p>{{ $detail->tahun ?? '–' }}</p></div>
                    <div class="info"><small>Kondisi</small><p>{{ $detail->kondisi }}</p></div>
                    <div class="info full"><small>Alamat</small><p>{{ $detail->alamat }}</p></div>
                    <div class="info">
                        <small>Titik koordinat</small>
                        <p><a href="https://www.google.com/maps?q={{ $detail->latitude }},{{ $detail->longitude }}" target="_blank" rel="noopener">{{ $detail->latitude }}, {{ $detail->longitude }} ↗</a></p>
                    </div>
                    <div class="info"><small>Dicatat oleh</small><p>{{ $detail->pencatat->name ?? '–' }}, {{ $detail->created_at?->locale('id')->translatedFormat('j F Y') }}</p></div>
                    <div class="info full"><small>Deskripsi sejarah</small><div class="isi-kaya">{!! \App\Support\TeksKaya::html($detail->deskripsi_sejarah) !!}</div></div>

                    <div class="info full">
                        <small>Gambar tambahan ({{ $detail->galeri->count() }} foto)</small>
                        @if ($detail->galeri->isNotEmpty())
                            <div class="galeri">
                                @foreach ($detail->galeri as $g)
                                    <a class="galeri-item" href="{{ $g->gambar_url }}" target="_blank" rel="noopener" wire:key="detail-galeri-cb-{{ $g->id_galeri_cagar_budaya }}">
                                        <img src="{{ $g->gambar_url }}" alt="Foto {{ $detail->nama }}" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p>Belum ada gambar tambahan.</p>
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

    <livewire:admin.cagar-budaya-form />
</div>
