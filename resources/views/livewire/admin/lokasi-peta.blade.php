<div>
    <x-admin.topbar judul="Lokasi Peta" sub="Restoran & rumah makan, pusat oleh-oleh, dan lokasi lain untuk Peta Wisata" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <p style="color:var(--mut);margin:0 0 12px">Lapisan peta lain diambil dari data masing-masing: Desa Wisata &amp; Objek Wisata dari menu Wisata, Hotel dari menu Hotel, Ekonomi Kreatif &amp; Seni Budaya dari titik lokasi di Data Master EKRAF/Kesenian, Situs Budaya dari Cagar Budaya, dan Sekretariat Cabor dari Database Cabor.</p>

    <div class="bar-tools">
        <select wire:model.live="kategori" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach ($daftarKategori as $nilai => $label)
                <option value="{{ $nilai }}">{{ $label }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.300ms="cari" placeholder="Cari nama tempat…" aria-label="Cari lokasi">
        <button type="button" class="btn p" wire:click="$dispatchTo('admin.lokasi-peta-form', 'buka-form-lokasi')">+ Tambah lokasi</button>
    </div>

    <div class="tw">
        <table>
            <thead><tr><th style="width:84px">Foto</th><th>Nama tempat</th><th>Kategori</th><th>Alamat</th><th>Titik</th><th></th></tr></thead>
            <tbody>
                @forelse ($daftar as $l)
                    <tr wire:key="lokasi-{{ $l->id_lokasi }}">
                        <td>@if ($l->gambar_url)<img class="thumb-sm" src="{{ $l->gambar_url }}" alt="Foto {{ $l->nama }}" loading="lazy">@else<span class="thumb-sm kosong"></span>@endif</td>
                        <td>{{ $l->nama }}</td>
                        <td>{{ $l->kategori_label }}</td>
                        <td>{{ $l->alamat }}</td>
                        <td><a href="https://www.google.com/maps?q={{ $l->latitude }},{{ $l->longitude }}" target="_blank" rel="noopener">{{ $l->latitude }}, {{ $l->longitude }} ↗</a></td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="$dispatchTo('admin.lokasi-peta-form', 'buka-form-lokasi', { id: {{ $l->id_lokasi }} })">Ubah</button>
                                <button type="button" class="btn s d" wire:click="hapus({{ $l->id_lokasi }})" wire:confirm="Hapus lokasi &quot;{{ $l->nama }}&quot;?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Belum ada lokasi. Klik "+ Tambah lokasi" untuk mulai.</td></tr>
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

    <livewire:admin.lokasi-peta-form />
</div>
