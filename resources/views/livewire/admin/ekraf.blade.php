@php $angka = fn ($n) => number_format((int) $n, 0, ',', '.'); @endphp
<div>
    <x-admin.topbar judul="Data Master EKRAF" sub="Sistem input data pelaku ekonomi kreatif Kabupaten Jember" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="stats" style="grid-template-columns:repeat(2,1fr)">
        <div class="card stat"><small>Jumlah nama usaha/pelaku ekonomi kreatif aktif</small><b style="color:var(--ok)">{{ $angka($jumlahAktif) }}</b><span>pelaku</span></div>
        <div class="card stat"><small>Jumlah nama usaha/pelaku ekonomi kreatif non aktif</small><b style="color:var(--pinkink)">{{ $angka($jumlahNonaktif) }}</b><span>pelaku</span></div>
    </div>

    <details class="card" style="margin-bottom:16px" open>
        <summary style="cursor:pointer;font-weight:600">Grafik rincian data per subsektor dan per kecamatan</summary>
        <div style="margin-top:14px">
            @include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])
        </div>
    </details>

    <div class="bar-tools">
        <select wire:model.live="kolom" aria-label="Cari berdasarkan">
            @foreach ($kolomCari as $nilai => $label)
                <option value="{{ $nilai }}">{{ $label }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari data…" aria-label="Cari data ekraf">
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.ekraf-form', 'buka-form-ekraf')">+ Input Data Master</button>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>ID usaha/pelaku</th>
                    <th>Nama usaha/pelaku</th>
                    <th>Subsektor</th>
                    <th>Alamat</th>
                    <th>Kecamatan</th>
                    <th>Desa</th>
                    <th class="n">Omzet</th>
                    <th>Telp</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $e)
                    <tr wire:key="ekraf-{{ $e->id_ekraf }}">
                        <td><b>{{ $e->nomor_ekraf }}</b></td>
                        <td>{{ $e->nama_usaha }}</td>
                        <td>{{ $e->subsektor_lengkap }}</td>
                        <td>{{ $e->alamat }}</td>
                        <td>{{ $e->kecamatan->nama ?? '–' }}</td>
                        <td>{{ $e->desa->nama ?? '–' }}</td>
                        <td class="n">{{ $angka($e->omzet) }}</td>
                        <td>{{ $e->no_telepon ?? '–' }}</td>
                        <td><span class="bd {{ $e->status === 'aktif' ? 'g' : 'warn' }}">{{ $e->status_label }}</span></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $e->id_ekraf }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.ekraf-form', 'buka-form-ekraf', { id: {{ $e->id_ekraf }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $e->id_ekraf }})"
                                        wire:confirm="Hapus data &quot;{{ $e->nama_usaha }}&quot; ({{ $e->nomor_ekraf }})? Data yang dihapus tidak bisa dikembalikan.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="empty">
                        @if ($cari !== '') Tidak ada data yang cocok dengan "{{ $cari }}".
                        @else Belum ada data pelaku ekraf. Klik "+ Input Data Master" untuk mulai.
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
                    <h2 id="judul-detail-ekraf">Detail usaha/pelaku EKRAF</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="info"><small>ID usaha/pelaku</small><b>{{ $detail->nomor_ekraf }}</b></div>
                    <div class="info"><small>Status</small><p>{{ $detail->status_label }}</p></div>
                    <div class="info full"><small>Nama usaha/pelaku</small><p>{{ $detail->nama_usaha }}</p></div>
                    <div class="info"><small>Subsektor</small><p>{{ $detail->subsektor_lengkap }}</p></div>
                    <div class="info"><small>Omzet</small><p>{{ $detail->omzet_rupiah }}</p></div>
                    <div class="info full"><small>Alamat</small><p>{{ $detail->alamat }}, Desa {{ $detail->desa->nama ?? '–' }}, Kec. {{ $detail->kecamatan->nama ?? '–' }}, Kab. {{ $detail->kabupaten }}</p></div>
                    <div class="info"><small>Telp</small><p>{{ $detail->no_telepon ?? '–' }}</p></div>
                    <div class="info"><small>Ditambahkan</small><p>{{ $detail->created_at?->locale('id')->translatedFormat('j F Y, H:i') ?? '–' }}</p></div>
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
