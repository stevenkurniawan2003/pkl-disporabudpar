<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-cabor">
                <div class="mh">
                    <h2 id="judul-form-cabor">{{ $caborId ? 'Ubah data cabor' : 'Tambah cabor' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd">
                        <label for="cabor_nama">Nama cabang olahraga</label>
                        <select id="cabor_nama" wire:model.live="kode_cabor">
                            <option value="">Pilih cabor…</option>
                            @foreach ($daftarCabor as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('kode_cabor') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="cabor_induk">Induk organisasi</label>
                        <input id="cabor_induk" type="text" value="{{ $induk }}" placeholder="Terisi otomatis dari cabor" readonly>
                    </div>

                    <div class="fd">
                        <label for="cabor_ketua">Ketua Pengkab</label>
                        <input id="cabor_ketua" type="text" wire:model="ketua_pengkab">
                        @error('ketua_pengkab') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="cabor_kecamatan">Kecamatan</label>
                        <select id="cabor_kecamatan" wire:model="kecamatan_id">
                            <option value="">Pilih kecamatan…</option>
                            @foreach ($daftarKecamatan as $kc)
                                <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
                            @endforeach
                        </select>
                        @error('kecamatan_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="cabor_alamat">Alamat sekretariat</label>
                        <input id="cabor_alamat" type="text" wire:model="alamat_sekretariat">
                        @error('alamat_sekretariat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    @include('livewire.admin.partials.pilih-titik-peta', ['awalan' => 'cabor', 'label' => 'Titik lokasi sekretariat', 'keterangan' => 'untuk peta', 'opsional' => true])

                    <div class="fd">
                        <label for="cabor_atlet">Jumlah atlet aktif</label>
                        <input id="cabor_atlet" type="text" value="{{ $jumlahAtlet ?? '' }}" placeholder="Otomatis dari Database Atlet" readonly>
                    </div>

                    <div class="fd">
                        <label for="cabor_pelatih">Jumlah pelatih aktif</label>
                        <input id="cabor_pelatih" type="text" value="{{ $jumlahPelatih ?? '' }}" placeholder="Otomatis dari Database Pelatih" readonly>
                    </div>

                    <div class="fd full">
                        <label for="cabor_prestasi">Prestasi terakhir <span class="opsional">(opsional)</span></label>
                        <textarea id="cabor_prestasi" rows="2" wire:model="prestasi_terakhir"></textarea>
                        @error('prestasi_terakhir') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="cabor_kontak">Info kontak cabor <span class="opsional">(opsional)</span></label>
                        <input id="cabor_kontak" type="text" wire:model="kontak" placeholder="Nomor kontak yang bisa dihubungi">
                        @error('kontak') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="cabor_status">Status keaktifan</label>
                        <select id="cabor_status" wire:model="status">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Tidak Aktif</option>
                        </select>
                        @error('status') <small class="err-msg">{{ $message }}</small> @enderror
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
