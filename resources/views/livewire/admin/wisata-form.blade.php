<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="if (! document.querySelector('.tox-dialog')) $wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-wisata">
                <div class="mh">
                    <h2 id="judul-form-wisata">{{ $wisataId ? 'Ubah wisata' : 'Tambah wisata' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd">
                        <label for="nama">Nama wisata</label>
                        <input id="nama" type="text" wire:model="nama">
                        @error('nama') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" wire:model="kategori_id">
                            <option value="">Pilih kategori…</option>
                            @foreach ($daftarKategori as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                        @error('kategori_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kecamatan">Kecamatan</label>
                        <select id="kecamatan" wire:model="kecamatan_id">
                            <option value="">Pilih kecamatan…</option>
                            @foreach ($daftarKecamatan as $kc)
                                <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
                            @endforeach
                        </select>
                        @error('kecamatan_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="status">Status</label>
                        <select id="status" wire:model="status">
                            <option value="tayang">Tayang (tampil di publik)</option>
                            <option value="draf">Draf (disembunyikan)</option>
                        </select>
                        @error('status') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="deskripsi_singkat">Deskripsi singkat</label>
                        <textarea id="deskripsi_singkat" rows="2" maxlength="200" wire:model="deskripsi_singkat" placeholder="Satu-dua kalimat, tampil di popup peta wisata"></textarea>
                        @error('deskripsi_singkat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="narasi">Narasi <span class="opsional">(opsional)</span></label>
                        <x-admin.editor model="narasi" id="narasi" aria-label="Narasi" />
                        @error('narasi') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="alamat">Alamat resmi</label>
                        <input id="alamat" type="text" wire:model="alamat" placeholder="Contoh: Lojejer">
                        @error('alamat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="gambar_wisata">Gambar utama <span class="opsional">(opsional)</span></label>
                        <label class="up">
                            Tarik gambar ke sini atau klik untuk memilih (JPG/PNG/WEBP, maks 2 MB)
                            <input id="gambar_wisata" type="file" wire:model="gambarBaru" accept="image/png,image/jpeg,image/webp">
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
                        <label>Titik peta</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                            <div class="fd">
                                <label for="lat">Latitude</label>
                                <input id="lat" type="text" inputmode="decimal" wire:model="latitude" placeholder="-8.2151">
                                @error('latitude') <small class="err-msg">{{ $message }}</small> @enderror
                            </div>
                            <div class="fd">
                                <label for="lng">Longitude</label>
                                <input id="lng" type="text" inputmode="decimal" wire:model="longitude" placeholder="113.6662">
                                @error('longitude') <small class="err-msg">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <small style="color:var(--mut)">Wajib diisi. Tips: di Google Maps klik kanan pada lokasi, lalu klik angka koordinat untuk menyalin.</small>
                    </div>

                    <div class="fd full">
                        <label for="link_google_maps">Link Google Maps <span class="opsional">(opsional)</span></label>
                        <input id="link_google_maps" type="text" inputmode="url" wire:model="link_google_maps" placeholder="https://maps.app.goo.gl/…">
                        @error('link_google_maps') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label>Tampilkan di beranda publik (unggulan)</label>
                        <button type="button" class="tg {{ $unggulan ? 'on' : '' }}" wire:click="$toggle('unggulan')" aria-label="Unggulan" aria-pressed="{{ $unggulan ? 'true' : 'false' }}"></button>
                    </div>

                    <div class="fd full">
                        <label for="galeri">Galeri foto <span class="opsional">(opsional)</span></label>
                        <label class="up">
                            Tarik beberapa foto ke sini atau klik untuk memilih (maks 2 MB per foto, paling banyak {{ $maksGaleri }} foto)
                            <input id="galeri" type="file" multiple wire:model="unggahGaleri" wire:key="unggah-galeri-{{ count($galeriBaru) }}" accept="image/png,image/jpeg,image/webp">
                        </label>
                        <div wire:loading wire:target="unggahGaleri" style="color:var(--pinkink)">Mengunggah…</div>
                        @if ($errors->has('unggahGaleri') || $errors->has('unggahGaleri.*'))
                            <small class="err-msg">{{ $errors->first('unggahGaleri') ?: $errors->first('unggahGaleri.*') }}</small>
                        @endif

                        @if ($galeri || $galeriBaru)
                            <div class="galeri">
                                @foreach ($galeri as $i => $foto)
                                    <div class="galeri-item {{ $foto['hapus'] ? 'dihapus' : '' }}" wire:key="galeri-lama-{{ $foto['id'] }}">
                                        <img src="{{ asset('storage/'.$foto['gambar']) }}" alt="{{ $foto['keterangan'] ?: 'Foto galeri' }}">
                                        <input type="text" wire:model="galeri.{{ $i }}.keterangan" placeholder="Keterangan (opsional)" aria-label="Keterangan foto" @disabled($foto['hapus'])>
                                        @error("galeri.$i.keterangan") <small class="err-msg">{{ $message }}</small> @enderror
                                        @if ($foto['hapus'])
                                            <small class="err-msg">Akan dihapus saat disimpan.</small>
                                            <button type="button" class="btn s" wire:click="tandaiHapusGaleri({{ $i }})">Batal hapus</button>
                                        @else
                                            <button type="button" class="btn s d" wire:click="tandaiHapusGaleri({{ $i }})">Hapus</button>
                                        @endif
                                    </div>
                                @endforeach

                                @foreach ($galeriBaru as $i => $file)
                                    <div class="galeri-item" wire:key="galeri-baru-{{ $i }}-{{ $file->getFilename() }}">
                                        <img src="{{ $file->temporaryUrl() }}" alt="Pratinjau foto galeri baru">
                                        <span class="bd g">Baru</span>
                                        <input type="text" wire:model="keteranganBaru.{{ $i }}" placeholder="Keterangan (opsional)" aria-label="Keterangan foto">
                                        @error("galeriBaru.$i") <small class="err-msg">{{ $message }}</small> @enderror
                                        @error("keteranganBaru.$i") <small class="err-msg">{{ $message }}</small> @enderror
                                        <button type="button" class="btn s" wire:click="batalGaleriBaru({{ $i }})">Batalkan</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,gambarBaru,unggahGaleri">
                        <span wire:loading.remove wire:target="simpan">Simpan</span>
                        <span wire:loading wire:target="simpan">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.0/tinymce.min.js" referrerpolicy="origin"></script>
@endassets
