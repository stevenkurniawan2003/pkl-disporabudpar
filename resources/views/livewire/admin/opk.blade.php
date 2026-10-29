<div>
    <x-admin.topbar judul="Objek Pemajuan Kebudayaan" sub="Input data digital OPK Kabupaten Jember beserta galeri fotonya" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <select wire:model.live="jenis" aria-label="Filter jenis OPK">
            <option value="">-- Filter Jenis OPK --</option>
            @foreach ($daftarJenis as $j)
                <option value="{{ $j }}">{{ $j }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama, lokasi, atau pelaku budaya…" aria-label="Cari OPK">
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.opk-form', 'buka-form-opk')">+ Tambah Data</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th class="n" style="width:48px">No.</th>
                    <th style="width:84px">Gambar utama</th>
                    <th>Nama</th>
                    <th>Jenis OPK</th>
                    <th>Tahun</th>
                    <th>Kondisi</th>
                    <th>Diajukan pada</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $i => $o)
                    <tr wire:key="opk-{{ $o->id_opk }}">
                        <td class="n">{{ $daftar->firstItem() + $i }}.</td>
                        <td><img class="thumb-sm" src="{{ $o->gambar_url }}" alt="Gambar {{ $o->nama }}" loading="lazy"></td>
                        <td>{{ $o->nama }}<br><small style="color:var(--mut)">{{ $o->lokasi_persebaran }}</small></td>
                        <td>{{ $o->jenis }}</td>
                        <td>{{ $o->tahun ?? '–' }}</td>
                        <td><span class="bd {{ in_array($o->kondisi, ['Terancam Punah', 'Punah'], true) ? 'warn' : 'g' }}">{{ $o->kondisi }}</span></td>
                        <td>{{ $o->created_at?->locale('id')->translatedFormat('j M Y, H:i') }}<br><small style="color:var(--mut)">Oleh: {{ $o->pencatat->name ?? '–' }}</small></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $o->id_opk }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.opk-form', 'buka-form-opk', { id: {{ $o->id_opk }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $o->id_opk }})"
                                        wire:confirm="Hapus OPK &quot;{{ $o->nama }}&quot;? Gambar utama dan semua gambar tambahan ikut terhapus permanen.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">
                        @if ($cari !== '' || $jenis !== '') Tidak ada OPK yang cocok dengan filter.
                        @else Belum ada data OPK. Klik "+ Tambah Data" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-opk">
                <div class="mh">
                    <h2 id="judul-detail-opk">Detail OPK</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="fd full"><img class="detail-utama" src="{{ $detail->gambar_url }}" alt="Gambar utama {{ $detail->nama }}"></div>
                    <div class="info full"><small>Nama OPK</small><b>{{ $detail->nama }}</b></div>
                    <div class="info"><small>Jenis OPK</small><p>{{ $detail->jenis }}</p></div>
                    <div class="info"><small>Kondisi</small><p>{{ $detail->kondisi }}</p></div>
                    <div class="info"><small>Tahun</small><p>{{ $detail->tahun ?? '–' }}</p></div>
                    <div class="info"><small>Pelaku budaya</small><p>{{ $detail->pelaku_budaya ?? '–' }}</p></div>
                    <div class="info full"><small>Lokasi persebaran</small><p>{{ $detail->lokasi_persebaran }}</p></div>
                    <div class="info full"><small>Persebaran</small><p>{{ $detail->persebaran ?? '–' }}</p></div>
                    <div class="info full"><small>Deskripsi OPK</small><div class="isi-kaya">{!! \App\Support\TeksKaya::html($detail->deskripsi) !!}</div></div>
                    <div class="info"><small>Diajukan pada</small><p>{{ $detail->created_at?->locale('id')->translatedFormat('j F Y, H:i') ?? '–' }}</p></div>
                    <div class="info"><small>Oleh</small><p>{{ $detail->pencatat->name ?? '–' }}</p></div>

                    <div class="info full">
                        <small>Gambar OPK tambahan ({{ $detail->galeri->count() }} foto)</small>
                        @if ($detail->galeri->isNotEmpty())
                            <div class="galeri">
                                @foreach ($detail->galeri as $g)
                                    <a class="galeri-item" href="{{ $g->gambar_url }}" target="_blank" rel="noopener" wire:key="detail-galeri-opk-{{ $g->id_galeri_opk }}">
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

    <livewire:admin.opk-form />
</div>
