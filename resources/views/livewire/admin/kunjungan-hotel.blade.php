@php
    $angka = fn ($n) => number_format((int) $n, 0, ',', '.');
    $adaFilter = $tahun !== '' || $bulan !== '' || $hotel !== '';
@endphp
<div>
    <x-admin.topbar judul="Data Kunjungan Hotel" sub="Jumlah tamu tiap hotel per bulan, domestik dan mancanegara" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="bar-tools">
        <select wire:model.live="tahun" aria-label="Filter tahun">
            <option value="">Semua tahun</option>
            @foreach ($daftarTahun as $t)
                <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
        </select>
        <select wire:model.live="bulan" aria-label="Filter bulan">
            <option value="">Semua bulan</option>
            @foreach ($daftarBulan as $no => $nama)
                <option value="{{ $no }}">{{ $nama }}</option>
            @endforeach
        </select>
        <select wire:model.live="hotel" aria-label="Filter hotel" class="grow">
            <option value="">Semua hotel</option>
            @foreach ($daftarHotel as $h)
                <option value="{{ $h->id_hotel }}">{{ $h->nama_hotel }}</option>
            @endforeach
        </select>
        @if ($adaFilter)
            <button type="button" class="btn" wire:click="resetFilter">Reset filter</button>
        @endif
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.kunjungan-hotel-form', 'buka-form-kunjungan-hotel')">+ Tambah data</button>
    </div>

    <div class="stats" aria-label="Ringkasan sesuai filter">
        <div class="card stat"><small>Tamu domestik</small><b>{{ $angka($totalDomestik) }}</b><span>{{ $adaFilter ? 'sesuai filter' : 'semua data' }}</span></div>
        <div class="card stat"><small>Tamu mancanegara</small><b>{{ $angka($totalMancanegara) }}</b><span>{{ $adaFilter ? 'sesuai filter' : 'semua data' }}</span></div>
        <div class="card stat"><small>Total kunjungan</small><b>{{ $angka($totalDomestik + $totalMancanegara) }}</b><span>domestik + mancanegara</span></div>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>Hotel</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th class="n">Domestik</th>
                    <th class="n">Mancanegara</th>
                    <th class="n">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $k)
                    <tr wire:key="kunjungan-hotel-{{ $k->id }}">
                        <td>{{ $k->hotel?->nama_hotel ?? '–' }}</td>
                        <td>{{ $k->nama_bulan }}</td>
                        <td>{{ $k->tahun }}</td>
                        <td class="n">{{ $angka($k->domestik) }}</td>
                        <td class="n">{{ $angka($k->mancanegara) }}</td>
                        <td class="n"><b>{{ $angka($k->total) }}</b></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="lihatDetail({{ $k->id }})">Detail</button>
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.kunjungan-hotel-form', 'buka-form-kunjungan-hotel', { id: {{ $k->id }} })">Ubah</button>
                                <button type="button" class="btn s d"
                                        wire:click="hapus({{ $k->id }})"
                                        wire:confirm="Hapus data kunjungan {{ $k->hotel?->nama_hotel }} bulan {{ $k->nama_bulan }} {{ $k->tahun }}?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">
                        @if ($adaFilter) Tidak ada data kunjungan yang cocok dengan filter.
                        @else Belum ada data kunjungan. Klik "+ Tambah data" untuk mulai.
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
            <div class="md" role="dialog" aria-modal="true" aria-labelledby="judul-detail-kunjungan-hotel">
                <div class="mh">
                    <h2 id="judul-detail-kunjungan-hotel">Detail kunjungan hotel</h2>
                    <button type="button" class="btn s" wire:click="tutupDetail" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    <div class="info full">
                        <small>Hotel</small>
                        <b>{{ $detail->hotel?->nama_hotel ?? '–' }}</b>
                        @if ($detail->hotel)
                            <p>{{ $detail->hotel->alamat }}</p>
                        @endif
                    </div>
                    <div class="info"><small>Periode</small><p>{{ $detail->nama_bulan }} {{ $detail->tahun }}</p></div>
                    <div class="info"><small>Total kunjungan</small><p><b>{{ $angka($detail->total) }}</b> orang</p></div>
                    <div class="info"><small>Tamu domestik</small><p>{{ $angka($detail->domestik) }} orang</p></div>
                    <div class="info"><small>Tamu mancanegara</small><p>{{ $angka($detail->mancanegara) }} orang</p></div>
                    <div class="info full"><small>Keterangan</small><p>{{ $detail->keterangan ?: '–' }}</p></div>
                    <div class="info"><small>Dicatat oleh</small><p>{{ $detail->pencatat?->name ?? '–' }}</p></div>
                    <div class="info"><small>Terakhir diubah</small><p>{{ $detail->updated_at?->locale('id')->translatedFormat('j F Y, H:i') ?? '–' }}</p></div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutupDetail">Tutup</button>
                    <button type="button" class="btn p" wire:click="ubahDariDetail">Ubah</button>
                </div>
            </div>
        </div>
    @endif

    <livewire:admin.kunjungan-hotel-form />
</div>
