<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="if (! document.querySelector('.tox-dialog')) $wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-cb">
                <div class="mh">
                    <h2 id="judul-form-cb">{{ $cagarBudayaId ? 'Ubah data ODCB/CB' : 'Tambah data ODCB/CB' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    @include('livewire.admin.partials.gambar-galeri', ['awalan' => 'cb', 'labelGambar' => 'Gambar utama', 'labelGaleri' => 'Gambar ODCB/CB tambahan'])

                    <div class="fd full">
                        <label for="cb_nama">Nama ODCB/CB</label>
                        <input id="cb_nama" type="text" wire:model="nama" placeholder="Contoh: Situs Duplang">
                        @error('nama') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="cb_alamat">Alamat</label>
                        <input id="cb_alamat" type="text" wire:model="alamat" placeholder="Contoh: Desa Kamal, Kecamatan Arjasa">
                        @error('alamat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    @include('livewire.admin.partials.pilih-titik-peta', ['awalan' => 'cb', 'label' => 'Titik koordinat'])

                    <div class="fd">
                        <label for="cb_kategori">Kategori ODCB/CB</label>
                        <select id="cb_kategori" wire:model="kategori">
                            <option value="">Pilih kategori…</option>
                            @foreach ($daftarKategori as $k)
                                <option value="{{ $k }}">{{ $k }}</option>
                            @endforeach
                        </select>
                        @error('kategori') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="cb_tahun">Tahun <span class="opsional">(opsional)</span></label>
                        <input id="cb_tahun" type="text" inputmode="numeric" maxlength="4" wire:model="tahun" placeholder="Contoh: 1930">
                        @error('tahun') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="cb_status">Status</label>
                        <select id="cb_status" wire:model="status">
                            <option value="">Pilih status…</option>
                            @foreach ($daftarStatus as $kode => $label)
                                <option value="{{ $kode }}">{{ $kode }} ({{ $label }})</option>
                            @endforeach
                        </select>
                        @error('status') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="cb_kondisi">Kondisi</label>
                        <select id="cb_kondisi" wire:model="kondisi">
                            <option value="">Pilih kondisi…</option>
                            @foreach ($daftarKondisi as $k)
                                <option value="{{ $k }}">{{ $k }}</option>
                            @endforeach
                        </select>
                        @error('kondisi') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="cb_sejarah">Deskripsi sejarah ODCB/CB</label>
                        <x-admin.editor model="deskripsi_sejarah" id="cb_sejarah" aria-label="Deskripsi sejarah" />
                        @error('deskripsi_sejarah') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,gambarBaru,unggahGaleri">
                        <span wire:loading.remove wire:target="simpan">Submit</span>
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
