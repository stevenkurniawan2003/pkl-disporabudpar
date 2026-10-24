<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-kesenian">
                <div class="mh">
                    <h2 id="judul-form-kesenian">{{ $kesenianId ? 'Ubah kesenian' : 'Tambah kesenian' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd full">
                        <label for="nama_kesenian">Nama kesenian</label>
                        <input id="nama_kesenian" type="text" wire:model="nama_kesenian" placeholder="Contoh: Tari Lahbako">
                        @error('nama_kesenian') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kategori_kesenian">Kategori</label>
                        <select id="kategori_kesenian" wire:model="kategori">
                            <option value="">Pilih kategori…</option>
                            @foreach ($daftarKategori as $k)
                                <option value="{{ $k }}">{{ $k }}</option>
                            @endforeach
                        </select>
                        @error('kategori') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="asal_daerah">Asal daerah <span class="opsional">(opsional)</span></label>
                        <input id="asal_daerah" type="text" wire:model="asal_daerah" placeholder="Contoh: Kec. Kalisat">
                        @error('asal_daerah') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="deskripsi_kesenian">Deskripsi</label>
                        <textarea id="deskripsi_kesenian" rows="6" wire:model="deskripsi" placeholder="Sejarah, makna, dan ciri khas kesenian ini"></textarea>
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
