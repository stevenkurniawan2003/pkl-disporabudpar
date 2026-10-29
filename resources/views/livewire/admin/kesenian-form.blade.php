<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-kesenian">
                <div class="mh">
                    <h2 id="judul-form-kesenian">{{ $kesenianId ? 'Ubah data master kesenian' : 'Input data master kesenian' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="fd">
                        <label for="kesenian_nomor">Nomor induk</label>
                        <input id="kesenian_nomor" type="text" value="{{ $nomorInduk ?? '' }}" placeholder="Terisi otomatis setelah memilih jenis" readonly>
                        @if ($kesenianId && $kode_jenis !== $kodeJenisLama && $nomorInduk)
                            <small style="color:var(--pinkink)">Jenis diganti: nomor induk lama {{ $nomorIndukLama }} akan diganti.</small>
                        @endif
                    </div>

                    <div class="fd">
                        <label for="kesenian_jenis">Jenis kesenian</label>
                        <select id="kesenian_jenis" wire:model.live="kode_jenis">
                            <option value="">Pilih jenis kesenian…</option>
                            @foreach ($daftarJenis as $kode => $nama)
                                <option value="{{ $kode }}">{{ $kode }} {{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('kode_jenis') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kesenian_nama">Nama kesenian</label>
                        <input id="kesenian_nama" type="text" wire:model="nama_kesenian" placeholder="Contoh: Agung Sejati">
                        @error('nama_kesenian') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kesenian_pemilik">Nama pemilik</label>
                        <input id="kesenian_pemilik" type="text" wire:model="nama_pemilik">
                        @error('nama_pemilik') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="kesenian_alamat">Alamat</label>
                        <input id="kesenian_alamat" type="text" wire:model="alamat" placeholder="Contoh: Dusun Krajan 01 RT. 001 / RW 001">
                        @error('alamat') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kesenian_kabupaten">Kabupaten</label>
                        <input id="kesenian_kabupaten" type="text" wire:model="kabupaten">
                        @error('kabupaten') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kesenian_kecamatan">Kecamatan</label>
                        <select id="kesenian_kecamatan" wire:model.live="kecamatan_id">
                            <option value="">Pilih kecamatan…</option>
                            @foreach ($daftarKecamatan as $kc)
                                <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
                            @endforeach
                        </select>
                        @error('kecamatan_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kesenian_desa">Desa/kelurahan</label>
                        <select id="kesenian_desa" wire:model="desa_id" @disabled($kecamatan_id === '') wire:key="desa-{{ $kecamatan_id }}">
                            <option value="">{{ $kecamatan_id === '' ? 'Pilih kecamatan dulu' : 'Pilih desa/kelurahan…' }}</option>
                            @foreach ($daftarDesa as $d)
                                <option value="{{ $d->id }}">{{ $d->nama }}</option>
                            @endforeach
                        </select>
                        @error('desa_id') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kesenian_anggota">Jumlah anggota</label>
                        <input id="kesenian_anggota" type="number" min="1" wire:model="jumlah_anggota">
                        @error('jumlah_anggota') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kesenian_telepon">Nomor telepon <span class="opsional">(opsional)</span></label>
                        <input id="kesenian_telepon" type="tel" wire:model="no_telepon" placeholder="0812 3456 7890">
                        @error('no_telepon') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="kesenian_status">Status</label>
                        <select id="kesenian_status" wire:model="status">
                            @foreach ($daftarStatus as $nilai => $label)
                                <option value="{{ $nilai }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    @include('livewire.admin.partials.titik-opsional', ['awalan' => 'kesenian', 'keterangan' => 'tampil di Peta Wisata sebagai Seni Budaya'])

                    <div class="fd full">
                        <label for="kesenian_dokumentasi">Dokumentasi kegiatan <span class="opsional">(opsional)</span></label>
                        <label class="up">
                            Klik untuk memilih foto (JPG/PNG, maks 2 MB)
                            <input id="kesenian_dokumentasi" type="file" wire:model="dokumentasiBaru" accept="image/png,image/jpeg">
                        </label>
                        <div wire:loading wire:target="dokumentasiBaru" style="color:var(--pinkink)">Mengunggah…</div>
                        @error('dokumentasiBaru') <small class="err-msg">{{ $message }}</small> @enderror

                        @if ($dokumentasiBaru && ! $errors->has('dokumentasiBaru') && $dokumentasiBaru->isPreviewable())
                            <div class="thumb"><img src="{{ $dokumentasiBaru->temporaryUrl() }}" alt="Pratinjau dokumentasi"></div>
                            <button type="button" class="btn s" style="align-self:flex-start" wire:click="batalDokumentasiBaru">Batalkan foto baru</button>
                        @elseif ($dokumentasiLama)
                            <div class="thumb {{ $hapusDokumentasi ? 'dihapus' : '' }}"><img src="{{ asset('storage/'.$dokumentasiLama) }}" alt="Dokumentasi saat ini"></div>
                            <button type="button" class="btn s {{ $hapusDokumentasi ? '' : 'd' }}" style="align-self:flex-start" wire:click="$toggle('hapusDokumentasi')">
                                {{ $hapusDokumentasi ? 'Batal hapus' : 'Hapus foto ini' }}
                            </button>
                            @if ($hapusDokumentasi)<small class="err-msg">Foto akan dihapus saat disimpan.</small>@endif
                        @endif
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn" wire:click="tutup">Batal</button>
                    <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan,dokumentasiBaru">
                        <span wire:loading.remove wire:target="simpan">Submit</span>
                        <span wire:loading wire:target="simpan">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
