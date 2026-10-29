<div>
    @if ($terbuka)
        <div class="ov on" x-on:keydown.escape.window="$wire.tutup()">
            <form wire:submit="simpan" class="md" role="dialog" aria-modal="true" aria-labelledby="judul-form-atlet">
                <div class="mh">
                    <h2 id="judul-form-atlet">{{ $atletId ? 'Ubah data atlet' : 'Tambah atlet' }}</h2>
                    <button type="button" class="btn s" wire:click="tutup" aria-label="Tutup">✕</button>
                </div>

                <div class="mb">
                    @error('simpan')
                        <div class="alert err full" role="alert">{{ $message }}</div>
                    @enderror

                    @include('livewire.admin.partials.biodata-olahraga', ['awalan' => 'atlet'])

                    <div class="fd">
                        <label for="atlet_klub">Klub <span class="opsional">(opsional)</span></label>
                        <input id="atlet_klub" type="text" wire:model="klub">
                        @error('klub') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd">
                        <label for="atlet_binaan">Status binaan</label>
                        <select id="atlet_binaan" wire:model="status_binaan">
                            <option value="">Pilih status binaan…</option>
                            @foreach ($daftarBinaan as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                        @error('status_binaan') <small class="err-msg">{{ $message }}</small> @enderror
                    </div>

                    <div class="fd full">
                        <label for="atlet_prestasi">Prestasi terakhir <span class="opsional">(opsional)</span></label>
                        <textarea id="atlet_prestasi" rows="2" wire:model="prestasi_terakhir" placeholder="Contoh: Juara 1 Porprov Jatim 2025, nomor 100 m putra"></textarea>
                        @error('prestasi_terakhir') <small class="err-msg">{{ $message }}</small> @enderror
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
