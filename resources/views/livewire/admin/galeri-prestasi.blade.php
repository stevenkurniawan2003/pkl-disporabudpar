<div>
    <x-admin.topbar judul="Galeri Prestasi" sub="Sorotan Prestasi: dokumentasi foto dan video prestasi olahraga" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <form wire:submit="simpan" class="card" style="margin-bottom:16px">
        <h3>{{ $ubahId ? 'Ubah keterangan dokumentasi' : 'Unggah foto/video' }}</h3>
        <div class="mb" style="padding:0">
            <div class="fd full">
                <label for="galeri_judul">Judul</label>
                <input id="galeri_judul" type="text" wire:model="judul" placeholder="Contoh: Atlet pencak silat Jember raih emas Porprov">
                @error('judul') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="galeri_tahun">Tahun <span class="opsional">(opsional)</span></label>
                <input id="galeri_tahun" type="text" inputmode="numeric" maxlength="4" wire:model="tahun">
                @error('tahun') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="galeri_ket">Keterangan <span class="opsional">(opsional)</span></label>
                <input id="galeri_ket" type="text" wire:model="keterangan">
                @error('keterangan') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            @unless ($ubahId)
                <div class="fd full">
                    <label for="galeri_berkas">Foto/video</label>
                    <label class="up">
                        Klik untuk memilih foto (JPG/PNG/WEBP, maks 2 MB) atau video (MP4/WEBM, maks 50 MB)
                        <input id="galeri_berkas" type="file" wire:model="berkas" accept="image/png,image/jpeg,image/webp,video/mp4,video/webm">
                    </label>
                    <div wire:loading wire:target="berkas" style="color:var(--pinkink)">Mengunggah…</div>
                    @error('berkas') <small class="err-msg">{{ $message }}</small> @enderror
                    @if ($berkas && ! $errors->has('berkas'))
                        @if ($berkas->isPreviewable() && str_starts_with((string) $berkas->getMimeType(), 'image/'))
                            <div class="thumb"><img src="{{ $berkas->temporaryUrl() }}" alt="Pratinjau foto"></div>
                        @else
                            <small>Berkas terpilih: {{ $berkas->getClientOriginalName() }}</small>
                        @endif
                    @endif
                </div>
            @endunless
        </div>
        <div class="act" style="justify-content:flex-end;margin-top:12px">
            @if ($ubahId)<button type="button" class="btn" wire:click="batal">Batal ubah</button>@endif
            <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,berkas">{{ $ubahId ? 'Simpan perubahan' : 'Unggah' }}</button>
        </div>
    </form>

    <div class="galeri">
        @forelse ($daftar as $g)
            <div class="galeri-item" wire:key="galeri-prestasi-{{ $g->id_galeri_prestasi }}">
                @if ($g->jenis === 'video')
                    <video src="{{ $g->file_url }}" controls preload="metadata" style="width:100%;border-radius:10px"></video>
                @else
                    <a href="{{ $g->file_url }}" target="_blank" rel="noopener"><img src="{{ $g->file_url }}" alt="{{ $g->judul }}" loading="lazy"></a>
                @endif
                <span class="bd {{ $g->jenis === 'video' ? '' : 'g' }}">{{ $g->jenis === 'video' ? 'Video' : 'Foto' }}{{ $g->tahun ? ' · '.$g->tahun : '' }}</span>
                <small class="galeri-ket">{{ $g->judul }}</small>
                <div class="act">
                    <button type="button" class="btn s" wire:click="ubah({{ $g->id_galeri_prestasi }})">Ubah</button>
                    <button type="button" class="btn s d" wire:click="hapus({{ $g->id_galeri_prestasi }})" wire:confirm="Hapus &quot;{{ $g->judul }}&quot; dari galeri? Berkasnya ikut terhapus permanen.">Hapus</button>
                </div>
            </div>
        @empty
            <p class="empty">Belum ada dokumentasi prestasi.</p>
        @endforelse
    </div>

    <div class="pg">
        <span>Menampilkan {{ $daftar->count() }} dari {{ $daftar->total() }} dokumentasi</span>
        <span class="pgn">
            <button type="button" class="btn s" wire:click="previousPage" @disabled($daftar->onFirstPage())>‹ Sebelumnya</button>
            Halaman {{ $daftar->currentPage() }} dari {{ max($daftar->lastPage(), 1) }}
            <button type="button" class="btn s" wire:click="nextPage" @disabled(! $daftar->hasMorePages())>Berikutnya ›</button>
        </span>
    </div>
</div>
