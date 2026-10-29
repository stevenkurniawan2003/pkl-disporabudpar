<div>
    <x-admin.topbar judul="Data Master Kesenian" sub="Pusat data sanggar/komunitas/lembaga kebudayaan untuk Kartu Induk Kesenian" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <select wire:model.live="kolom" aria-label="Cari berdasarkan">
            @foreach ($kolomCari as $nilai => $label)
                <option value="{{ $nilai }}">{{ $label }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari data…" aria-label="Cari data kesenian">
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.kesenian-form', 'buka-form-kesenian')">+ Input Data Master</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>Nomor induk</th>
                    <th>Nama kesenian</th>
                    <th>Nama pemilik</th>
                    <th>Jenis kesenian</th>
                    <th>Alamat</th>
                    <th>Kecamatan</th>
                    <th>Desa</th>
                    <th class="n">Anggota</th>
                    <th>Telp</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $k)
                    <tr wire:key="kesenian-{{ $k->id_kesenian }}">
                        <td><b>{{ $k->nomor_induk }}</b></td>
                        <td>{{ $k->nama_kesenian }}</td>
                        <td>{{ $k->nama_pemilik }}</td>
                        <td>{{ $k->jenis_lengkap }}</td>
                        <td>{{ $k->alamat }}</td>
                        <td>{{ $k->kecamatan->nama ?? '–' }}</td>
                        <td>{{ $k->desa->nama ?? '–' }}</td>
                        <td class="n">{{ $k->jumlah_anggota }}</td>
                        <td>{{ $k->no_telepon ?? '–' }}</td>
                        <td><span class="bd {{ $k->status === 'aktif' ? 'g' : 'warn' }}">{{ $k->status_label }}</span></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $k->id_kesenian }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.kesenian-form', 'buka-form-kesenian', { id: {{ $k->id_kesenian }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $k->id_kesenian }})"
                                        wire:confirm="Hapus kesenian &quot;{{ $k->nama_kesenian }}&quot; ({{ $k->nomor_induk }})? Riwayat perpanjangan KIK dan foto-fotonya ikut terhapus permanen.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="empty">
                        @if ($cari !== '') Tidak ada data yang cocok dengan "{{ $cari }}".
                        @else Belum ada data kesenian. Klik "+ Input Data Master" untuk mulai.
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
                    <div class="info"><small>Nomor induk</small><b>{{ $detail->nomor_induk }}</b></div>
                    <div class="info"><small>Status</small><p>{{ $detail->status_label }}</p></div>
                    <div class="info"><small>Nama kesenian</small><p>{{ $detail->nama_kesenian }}</p></div>
                    <div class="info"><small>Nama pemilik</small><p>{{ $detail->nama_pemilik }}</p></div>
                    <div class="info full"><small>Jenis kesenian</small><p>{{ $detail->jenis_lengkap }}</p></div>
                    <div class="info full"><small>Alamat</small><p>{{ $detail->alamat_lengkap }}</p></div>
                    <div class="info"><small>Jumlah anggota</small><p>{{ $detail->jumlah_anggota }} orang</p></div>
                    <div class="info"><small>Nomor telepon</small><p>{{ $detail->no_telepon ?? '–' }}</p></div>
                    <div class="info full">
                        <small>Dokumentasi kegiatan</small>
                        @if ($detail->dokumentasi_url)
                            <a class="galeri-item" href="{{ $detail->dokumentasi_url }}" target="_blank" rel="noopener" style="max-width:240px"><img src="{{ $detail->dokumentasi_url }}" alt="Dokumentasi {{ $detail->nama_kesenian }}"></a>
                        @else
                            <p>–</p>
                        @endif
                    </div>
                    <div class="info full">
                        <small>Riwayat perpanjangan KIK</small>
                        @forelse ($detail->perpanjangan as $p)
                            <p wire:key="riwayat-{{ $p->id_perpanjangan }}">
                                {{ $p->tanggal_pengajuan->locale('id')->translatedFormat('j F Y') }} s.d. {{ $p->berlaku_sampai->locale('id')->translatedFormat('j F Y') }}
                                · <a href="{{ route('admin.kartu-kik', $p) }}" target="_blank" rel="noopener">Unduh kartu ↗</a>
                            </p>
                        @empty
                            <p>Belum pernah mengajukan perpanjangan.</p>
                        @endforelse
                    </div>
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
