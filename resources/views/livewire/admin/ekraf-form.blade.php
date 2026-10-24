<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-ekraf">
                <div class="mh">
                    <h2 id="judul-form-ekraf">{{ $ekrafId ? 'Ubah pelaku ekonomi kreatif' : 'Tambah pelaku ekonomi kreatif' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd">
                        <label for="nama_usaha">Nama usaha</label>
                        <input id="nama_usaha" type="text" wire:model="nama_usaha" placeholder="Contoh: Cokelat Raddina">
                        @error('nama_usaha') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="nama_pemilik">Nama pemilik</label>
                        <input id="nama_pemilik" type="text" wire:model="nama_pemilik">
                        @error('nama_pemilik') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="subsektor">Subsektor</label>
                        <select id="subsektor" wire:model="subsektor">
                            <option value="">Pilih subsektor…</option>
                            @foreach ($daftarSubsektor as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('subsektor') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="no_telepon_ekraf">Nomor telepon <span class="opsional">(opsional)</span></label>
                        <input id="no_telepon_ekraf" type="tel" wire:model="no_telepon" placeholder="08xx xxxx xxxx">
                        @error('no_telepon') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="alamat_ekraf">Alamat</label>
                        <input id="alamat_ekraf" type="text" wire:model="alamat" placeholder="Contoh: Jl. Kalimantan No. 10, Sumbersari">
                        @error('alamat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="media_sosial">Link media sosial <span class="opsional">(opsional)</span></label>
                        <input id="media_sosial" type="text" inputmode="url" wire:model="media_sosial" placeholder="https://instagram.com/nama_usaha">
                        @error('media_sosial') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="deskripsi_ekraf">Deskripsi</label>
                        <textarea id="deskripsi_ekraf" rows="5" wire:model="deskripsi" placeholder="Produk atau jasa yang ditawarkan"></textarea>
                        @error('deskripsi') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan">
                        <span wire:loading.remove wire:target="simpan">Simpan</span>
                        <span wire:loading wire:target="simpan">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
