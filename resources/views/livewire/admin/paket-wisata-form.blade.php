<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-paket">
                <div class="mh">
                    <h2 id="judul-form-paket">{{ $paketId ? 'Ubah paket wisata' : 'Tambah paket wisata' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd full">
                        <label for="paket_nama">Nama paket</label>
                        <input id="paket_nama" type="text" wire:model="nama_paket" placeholder="Contoh: Paket 2 Hari Papuma – Rembangan">
                        @error('nama_paket') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="paket_flyer">Flyer</label>
                        <label class="up">
                            Klik untuk memilih flyer (JPG/PNG/WEBP, maks 4 MB)
                            <input id="paket_flyer" type="file" wire:model="flyerBaru" accept="image/png,image/jpeg,image/webp">
                        </label>
                        <div wire:loading wire:target="flyerBaru" style="color:var(--pinkink)">Mengunggah…</div>
                        @error('flyerBaru') <small class="err-msg">{{ $message }}</small> @enderror
                        @if ($flyerBaru && ! $errors->has('flyerBaru') && $flyerBaru->isPreviewable())
                            <div class="thumb"><img src="{{ $flyerBaru->temporaryUrl() }}" alt="Pratinjau flyer"></div>
                        @elseif ($flyerLama)
                            <div class="thumb"><img src="{{ asset('storage/'.$flyerLama) }}" alt="Flyer saat ini"></div>
                        @endif
                    </div>

                    <div class="fd full">
                        <label for="paket_deskripsi">Deskripsi/itinerary</label>
                        <textarea id="paket_deskripsi" rows="5" wire:model="deskripsi" placeholder="Tujuan, fasilitas, dan jadwal paket"></textarea>
                        @error('deskripsi') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="paket_harga">Harga <span class="opsional">(opsional)</span></label>
                        <input id="paket_harga" type="text" wire:model="harga" placeholder="Contoh: Rp 350.000/orang">
                        @error('harga') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="paket_durasi">Durasi <span class="opsional">(opsional)</span></label>
                        <input id="paket_durasi" type="text" wire:model="durasi" placeholder="Contoh: 2 hari 1 malam">
                        @error('durasi') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="paket_cp">Nama contact person (CP)</label>
                        <input id="paket_cp" type="text" wire:model="nama_cp">
                        @error('nama_cp') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="paket_no_cp">Nomor CP (WhatsApp)</label>
                        <input id="paket_no_cp" type="tel" wire:model="no_cp" placeholder="0812 3456 7890">
                        @error('no_cp') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label>Tampilkan di situs publik</label>
                        <button type="button" class="tg {{ $tayang ? 'on' : '' }}" wire:click="$toggle('tayang')" aria-label="Tayang" aria-pressed="{{ $tayang ? 'true' : 'false' }}"></button>
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,flyerBaru">Simpan</button>
                </div>
            </form>
        </div>
    @endif
</div>
