<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-ekraf">
                <div class="mh">
                    <h2 id="judul-form-ekraf">{{ $ekrafId ? 'Ubah data usaha/pelaku EKRAF' : 'Form data nama usaha/pelaku EKRAF' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd">
                        <label for="ekraf_nomor">ID usaha/pelaku</label>
                        <input id="ekraf_nomor" type="text" value="{{ $nomor ?? '' }}" placeholder="Terisi otomatis setelah memilih subsektor" readonly>
                        @if ($ekrafId && $kode_subsektor !== $kodeSubsektorLama && $nomor)
                            <small style="color:var(--pinkink)">Subsektor diganti: ID lama {{ $nomorLama }} akan diganti.</small>
                        @endif
                    </div>

                    <div class="fd">
                        <label for="ekraf_subsektor">Subsektor</label>
                        <select id="ekraf_subsektor" wire:model.live="kode_subsektor">
                            <option value="">Pilih subsektor…</option>
                            @foreach ($daftarSubsektor as $kode => $nama)
                                <option value="{{ $kode }}">{{ $kode }} {{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('kode_subsektor') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="ekraf_nama">Nama usaha/pelaku</label>
                        <input id="ekraf_nama" type="text" wire:model="nama_usaha" placeholder="Contoh: Tas Anyaman Kasmirah/Adzimiyah Sukma Karyasari">
                        @error('nama_usaha') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="ekraf_alamat">Alamat</label>
                        <textarea id="ekraf_alamat" rows="2" wire:model="alamat" placeholder="Contoh: Jln. Dusun Sumberpinang RT 003/RW 004"></textarea>
                        @error('alamat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="ekraf_kabupaten">Kabupaten</label>
                        <input id="ekraf_kabupaten" type="text" wire:model="kabupaten">
                        @error('kabupaten') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="ekraf_kecamatan">Kecamatan</label>
                        <select id="ekraf_kecamatan" wire:model.live="kecamatan_id">
                            <option value="">Pilih kecamatan…</option>
                            @foreach ($daftarKecamatan as $kc)
                                <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
                            @endforeach
                        </select>
                        @error('kecamatan_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="ekraf_desa">Desa/kelurahan</label>
                        <select id="ekraf_desa" wire:model="desa_id" @disabled($kecamatan_id === '') wire:key="ekraf-desa-{{ $kecamatan_id }}">
                            <option value="">{{ $kecamatan_id === '' ? 'Pilih kecamatan dulu' : 'Pilih desa/kelurahan…' }}</option>
                            @foreach ($daftarDesa as $d)
                                <option value="{{ $d->id }}">{{ $d->nama }}</option>
                            @endforeach
                        </select>
                        @error('desa_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="ekraf_telepon">Telp <span class="opsional">(opsional)</span></label>
                        <input id="ekraf_telepon" type="tel" wire:model="no_telepon" placeholder="0813 3555 0178">
                        @error('no_telepon') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    {{-- Slider dan kotak angka memakai nilai yang sama; nilainya dikirim ke server saat Submit. --}}
                    <div class="fd full" x-data="{ omzet: $wire.entangle('omzet') }">
                        <label for="ekraf_omzet">Omzet (Rp)</label>
                        <div class="omzet">
                            <input type="range" min="0" max="{{ $maksSlider }}" step="100000" x-model.number="omzet" aria-label="Geser untuk mengatur omzet">
                            <input id="ekraf_omzet" type="number" min="0" step="1000" x-model.number="omzet">
                            <b x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(omzet || 0)"></b>
                        </div>
                        @error('omzet') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    @include('livewire.admin.partials.titik-opsional', ['awalan' => 'ekraf', 'keterangan' => 'tampil di Peta Wisata sebagai Ekonomi Kreatif'])

                    <div class="fd">
                        <label for="ekraf_status">Status</label>
                        <select id="ekraf_status" wire:model="status">
                            @foreach ($daftarStatus as $nilai => $label)
                                <option value="{{ $nilai }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan">
                        <span wire:loading.remove wire:target="simpan">Submit</span>
                        <span wire:loading wire:target="simpan">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
