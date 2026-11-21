<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="if (! document.querySelector('.tox-dialog')) $wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-opk">
                <div class="mh">
                    <h2 id="judul-form-opk">{{ $opkId ? 'Ubah data OPK' : 'Tambah data OPK' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    @include('livewire.admin.partials.gambar-galeri', ['awalan' => 'opk', 'labelGambar' => 'Gambar utama', 'labelGaleri' => 'Gambar OPK tambahan'])

                    <div class="fd full">
                        <label for="opk_nama">Nama OPK</label>
                        <input id="opk_nama" type="text" wire:model="nama" placeholder="Contoh: Wayang Orang">
                        @error('nama') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="opk_jenis">Jenis OPK</label>
                        <select id="opk_jenis" wire:model="jenis">
                            <option value="">Pilih jenis OPK…</option>
                            @foreach ($daftarJenis as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                        @error('jenis') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="opk_kondisi">Kondisi</label>
                        <select id="opk_kondisi" wire:model="kondisi">
                            <option value="">Pilih kondisi…</option>
                            @foreach ($daftarKondisi as $k)
                                <option value="{{ $k }}">{{ $k }}</option>
                            @endforeach
                        </select>
                        @error('kondisi') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="opk_lokasi">Lokasi persebaran</label>
                        <input id="opk_lokasi" type="text" wire:model="lokasi_persebaran" placeholder="Contoh: Desa Glundengan, Kecamatan Wuluhan">
                        @error('lokasi_persebaran') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="opk_persebaran">Persebaran <span class="opsional">(opsional, ditulis manual)</span></label>
                        <textarea id="opk_persebaran" rows="2" wire:model="persebaran" placeholder="Contoh: Tersebar di wilayah Jember selatan dan sebagian Lumajang"></textarea>
                        @error('persebaran') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="opk_tahun">Tahun <span class="opsional">(opsional)</span></label>
                        <input id="opk_tahun" type="number" min="1900" max="{{ now()->year }}" wire:model="tahun" placeholder="Contoh: 1900">
                        @error('tahun') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="opk_pelaku">Pelaku budaya <span class="opsional">(opsional)</span></label>
                        <input id="opk_pelaku" type="text" wire:model="pelaku_budaya" placeholder="Nama maestro, sanggar, atau komunitas">
                        @error('pelaku_budaya') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="opk_deskripsi">Deskripsi OPK</label>
                        <x-admin.editor model="deskripsi" id="opk_deskripsi" aria-label="Deskripsi OPK" />
                        @error('deskripsi') <small class="err-msg">{{ $message }}</small> @enderror
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
