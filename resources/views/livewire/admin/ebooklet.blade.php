<div>
    <x-admin.topbar judul="E-Booklet" sub="E-booklet wisata Jember terbaru untuk diunduh wisatawan" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <form wire:submit="simpan" class="card" style="margin-bottom:16px">
        <h3>Unggah e-booklet</h3>
        <div class="mb" style="padding:0">
            <div class="fd">
                <label for="eb_judul">Judul</label>
                <input id="eb_judul" type="text" wire:model="judul" placeholder="Contoh: Jember Travel Guide 2026">
                @error('judul') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="eb_tahun">Tahun</label>
                <input id="eb_tahun" type="text" inputmode="numeric" maxlength="4" wire:model="tahun">
                @error('tahun') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd full">
                <label for="eb_deskripsi">Deskripsi <span class="opsional">(opsional)</span></label>
                <input id="eb_deskripsi" type="text" wire:model="deskripsi">
                @error('deskripsi') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="eb_pdf">Berkas PDF (maks 20 MB)</label>
                <input id="eb_pdf" type="file" wire:model="pdf" accept="application/pdf">
                <div wire:loading wire:target="pdf" style="color:var(--pinkink)">Mengunggah…</div>
                @error('pdf') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="eb_sampul">Sampul <span class="opsional">(opsional, gambar)</span></label>
                <input id="eb_sampul" type="file" wire:model="sampul" accept="image/png,image/jpeg,image/webp">
                @error('sampul') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
        </div>
        <div class="act" style="justify-content:flex-end;margin-top:12px">
            <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,pdf,sampul">Unggah</button>
        </div>
    </form>

    <div class="tw">
        <table>
            <thead><tr><th style="width:84px">Sampul</th><th>Judul</th><th class="n">Tahun</th><th>Berkas</th><th></th></tr></thead>
            <tbody>
                @forelse ($daftar as $e)
                    <tr wire:key="ebooklet-{{ $e->id_ebooklet }}">
                        <td>@if ($e->sampul_url)<img class="thumb-sm" src="{{ $e->sampul_url }}" alt="Sampul {{ $e->judul }}" loading="lazy">@else<span class="thumb-sm kosong"></span>@endif</td>
                        <td>{{ $e->judul }}@if ($e->deskripsi)<br><small style="color:var(--mut)">{{ $e->deskripsi }}</small>@endif</td>
                        <td class="n">{{ $e->tahun }}</td>
                        <td><a href="{{ $e->pdf_url }}" target="_blank" rel="noopener">Buka PDF ↗</a></td>
                        <td><div class="act"><button type="button" class="btn s d" wire:click="hapus({{ $e->id_ebooklet }})" wire:confirm="Hapus e-booklet &quot;{{ $e->judul }}&quot;? Berkas PDF-nya ikut terhapus.">Hapus</button></div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada e-booklet.</td></tr>
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
</div>
