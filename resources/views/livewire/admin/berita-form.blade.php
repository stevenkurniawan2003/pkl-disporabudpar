<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-berita">
                <div class="mh">
                    <h2 id="judul-form-berita">{{ $beritaId ? 'Ubah berita' : 'Tulis berita' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd full">
                        <label for="judul">Judul</label>
                        <input id="judul" type="text" wire:model="judul">
                        @error('judul') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" wire:model="kategori_berita_id">
                            <option value="">Pilih kategori…</option>
                            @foreach ($daftarKategori as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                        @error('kategori_berita_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="tanggal">Tanggal terbit</label>
                        <input id="tanggal" type="date" wire:model="tanggal">
                        <small style="color:var(--mut)">Tanggal di masa depan = terjadwal, baru tampil saat harinya tiba.</small>
                        @error('tanggal') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="status">Status</label>
                        <select id="status" wire:model="status">
                            <option value="terbit">Terbit (tampil di publik)</option>
                            <option value="draf">Draf (disembunyikan)</option>
                        </select>
                        @error('status') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="gambar_berita">Gambar sampul <span class="opsional">(opsional)</span></label>
                        <label class="up">
                            Tarik gambar ke sini atau klik untuk memilih (JPG/PNG/WEBP, maks 2 MB)
                            <input id="gambar_berita" type="file" wire:model="gambarBaru" accept="image/png,image/jpeg,image/webp">
                        </label>
                        <div wire:loading wire:target="gambarBaru" style="color:var(--pinkink)">Mengunggah…</div>
                        @error('gambarBaru') <small class="err-msg">{{ $message }}</small> @enderror

                        @if ($gambarBaru && ! $errors->has('gambarBaru') && $gambarBaru->isPreviewable())
                            <div class="thumb"><img src="{{ $gambarBaru->temporaryUrl() }}" alt="Pratinjau gambar baru"></div>
                            <button type="button" class="btn s" style="align-self:flex-start" wire:click="hapusGambar">Batalkan gambar baru</button>
                        @elseif ($gambarLama)
                            <div class="thumb"><img src="{{ asset('storage/'.$gambarLama) }}" alt="Gambar saat ini"></div>
                        @endif
                    </div>

                    <div class="fd full">
                        <label for="ringkasan">Ringkasan <span class="opsional">(opsional)</span></label>
                        <textarea id="ringkasan" rows="2" wire:model="ringkasan" placeholder="Kalimat singkat untuk daftar berita. Kosongkan untuk diambil otomatis dari isi."></textarea>
                        @error('ringkasan') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="isi">Isi berita</label>
                        <textarea id="isi" rows="12" wire:model="isi"></textarea>
                        <small style="color:var(--mut)">Pisahkan paragraf dengan satu baris kosong.</small>
                        @error('isi') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,gambarBaru">
                        <span wire:loading.remove wire:target="simpan">Simpan</span>
                        <span wire:loading wire:target="simpan">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
