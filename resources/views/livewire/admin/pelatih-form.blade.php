<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-pelatih">
                <div class="mh">
                    <h2 id="judul-form-pelatih">{{ $pelatihId ? 'Ubah data pelatih' : 'Tambah pelatih' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    @include('livewire.admin.partials.biodata-olahraga', ['awalan' => 'pelatih'])

                    <div class="fd">
                        <label for="pelatih_lisensi">Sertifikat/lisensi</label>
                        <select id="pelatih_lisensi" wire:model.live="punya_lisensi">
                            <option value="ya">Punya</option>
                            <option value="tidak">Tidak Punya</option>
                        </select>
                        @error('punya_lisensi') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    @if ($punya_lisensi === 'ya')
                        <div class="fd">
                            <label for="pelatih_nomor_lisensi">Nomor sertifikat/lisensi</label>
                            <input id="pelatih_nomor_lisensi" type="text" wire:model="nomor_lisensi">
                            @error('nomor_lisensi') <small class="err-msg">{{ $message }}</small> @enderror
                        </div>

                        <div class="fd full">
                            <label for="pelatih_file">Foto sertifikat/lisensi</label>
                            <label class="up">
                                Klik untuk memilih berkas (.jpeg / .png / .pdf, maks 2 MB)
                                <input id="pelatih_file" type="file" wire:model="fileBaru" accept="image/jpeg,image/png,application/pdf">
                            </label>
                            <div wire:loading wire:target="fileBaru" style="color:var(--pinkink)">Mengunggah…</div>
                            @error('fileBaru') <small class="err-msg">{{ $message }}</small> @enderror
                            @if ($fileBaru && ! $errors->has('fileBaru'))
                                <small>Berkas baru: {{ $fileBaru->getClientOriginalName() }}</small>
                            @elseif ($fileLama)
                                <small><a href="{{ asset('storage/'.$fileLama) }}" target="_blank" rel="noopener">Lihat berkas tersimpan ↗</a> (pilih berkas baru untuk mengganti)</small>
                            @endif
                        </div>
                    @elseif ($fileLama)
                        <div class="fd"><small class="err-msg">Berkas sertifikat/lisensi lama akan dihapus saat disimpan.</small></div>
                    @endif
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,fileBaru">
                        <span wire:loading.remove wire:target="simpan">Simpan</span>
                        <span wire:loading wire:target="simpan">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
