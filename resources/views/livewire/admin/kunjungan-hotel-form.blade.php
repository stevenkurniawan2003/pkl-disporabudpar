<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-kunjungan-hotel">
                <div class="mh">
                    <h2 id="judul-form-kunjungan-hotel">{{ $kunjunganId ? 'Ubah data kunjungan hotel' : 'Tambah data kunjungan hotel' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd full">
                        <label for="kunjungan_hotel_id">Hotel</label>
                        <select id="kunjungan_hotel_id" wire:model="hotel_id">
                            <option value="">Pilih hotel…</option>
                            @foreach ($daftarHotel as $h)
                                <option value="{{ $h->id_hotel }}">{{ $h->nama_hotel }}</option>
                            @endforeach
                        </select>
                        @error('hotel_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kunjungan_bulan">Bulan</label>
                        <select id="kunjungan_bulan" wire:model="bulan">
                            <option value="">Pilih bulan…</option>
                            @foreach ($daftarBulan as $no => $nama)
                                <option value="{{ $no }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('bulan') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kunjungan_tahun">Tahun</label>
                        <input id="kunjungan_tahun" type="number" inputmode="numeric" min="2000" max="{{ now()->year }}" wire:model="tahun">
                        @error('tahun') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kunjungan_domestik">Jumlah tamu domestik</label>
                        <input id="kunjungan_domestik" type="text" inputmode="numeric" wire:model="domestik" placeholder="0">
                        @error('domestik') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kunjungan_mancanegara">Jumlah tamu mancanegara</label>
                        <input id="kunjungan_mancanegara" type="text" inputmode="numeric" wire:model="mancanegara" placeholder="0">
                        @error('mancanegara') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="kunjungan_keterangan">Keterangan <span class="opsional">(opsional)</span></label>
                        <input id="kunjungan_keterangan" type="text" wire:model="keterangan" placeholder="Contoh: tutup 2 minggu karena renovasi">
                        @error('keterangan') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <small class="full" style="color:var(--mut)">Isi 0 kalau tidak ada tamu. Pemisah ribuan seperti 1.250 boleh dipakai.</small>
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
