<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-lokasi">
                <div class="mh">
                    <h2 id="judul-form-lokasi">{{ $lokasiId ? 'Ubah lokasi peta' : 'Tambah lokasi peta' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd">
                        <label for="lokasi_nama">Nama tempat</label>
                        <input id="lokasi_nama" type="text" wire:model="nama" placeholder="Contoh: Pusat Oleh-oleh Suwar-Suwir">
                        @error('nama') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="lokasi_kategori">Kategori</label>
                        <select id="lokasi_kategori" wire:model="kategori">
                            <option value="">Pilih kategori…</option>
                            @foreach ($daftarKategori as $nilai => $label)
                                <option value="{{ $nilai }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kategori') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="lokasi_alamat">Alamat</label>
                        <input id="lokasi_alamat" type="text" wire:model="alamat">
                        @error('alamat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="lokasi_lat">Latitude</label>
                        <input id="lokasi_lat" type="text" inputmode="decimal" wire:model="latitude" placeholder="-8.1724">
                        @error('latitude') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="lokasi_lng">Longitude</label>
                        <input id="lokasi_lng" type="text" inputmode="decimal" wire:model="longitude" placeholder="113.6995">
                        @error('longitude') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="lokasi_deskripsi">Deskripsi singkat <span class="opsional">(opsional, tampil di popup peta)</span></label>
                        <input id="lokasi_deskripsi" type="text" maxlength="255" wire:model="deskripsi_singkat">
                        @error('deskripsi_singkat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="lokasi_telepon">Nomor telepon <span class="opsional">(opsional)</span></label>
                        <input id="lokasi_telepon" type="tel" wire:model="no_telepon">
                        @error('no_telepon') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="lokasi_gambar">Foto <span class="opsional">(opsional)</span></label>
                        <input id="lokasi_gambar" type="file" wire:model="gambarBaru" accept="image/png,image/jpeg,image/webp">
                        <div wire:loading wire:target="gambarBaru" style="color:var(--pinkink)">Mengunggah…</div>
                        @error('gambarBaru') <small class="err-msg">{{ $message }}</small> @enderror
                        @if ($gambarBaru && ! $errors->has('gambarBaru') && $gambarBaru->isPreviewable())
                            <div class="thumb"><img src="{{ $gambarBaru->temporaryUrl() }}" alt="Pratinjau foto"></div>
                        @elseif ($gambarLama)
                            <div class="thumb"><img src="{{ asset('storage/'.$gambarLama) }}" alt="Foto saat ini"></div>
                        @endif
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,gambarBaru">Simpan</button>
                </div>
            </form>
        </div>
    @endif
</div>
