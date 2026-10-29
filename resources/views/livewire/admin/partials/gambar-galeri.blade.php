{{--
    Field gambar utama + galeri foto untuk form yang memakai trait KelolaGambarGaleri (OPK, Cagar Budaya).
    Parameter: $awalan (awalan id input & wire:key), $labelGambar, $labelGaleri.
--}}
<div class="fd full">
    <label for="{{ $awalan }}_gambar">{{ $labelGambar }}</label>
    <label class="up">
        Tarik gambar ke sini atau klik untuk memilih (JPG/PNG/WEBP, maks 2 MB)
        <input id="{{ $awalan }}_gambar" type="file" wire:model="gambarBaru" accept="image/png,image/jpeg,image/webp">
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
    <label for="{{ $awalan }}_galeri">{{ $labelGaleri }} <span class="opsional">(opsional)</span></label>
    <label class="up">
        Tarik beberapa foto ke sini atau klik untuk memilih (maks 2 MB per foto, paling banyak {{ $maksGaleri }} foto)
        <input id="{{ $awalan }}_galeri" type="file" multiple wire:model="unggahGaleri" wire:key="unggah-galeri-{{ $awalan }}-{{ count($galeriBaru) }}" accept="image/png,image/jpeg,image/webp">
    </label>
    <div wire:loading wire:target="unggahGaleri" style="color:var(--pinkink)">Mengunggah…</div>
    @if ($errors->has('unggahGaleri') || $errors->has('unggahGaleri.*'))
        <small class="err-msg">{{ $errors->first('unggahGaleri') ?: $errors->first('unggahGaleri.*') }}</small>
    @endif

    @if ($galeri || $galeriBaru)
        <div class="galeri">
            @foreach ($galeri as $i => $foto)
                <div class="galeri-item {{ $foto['hapus'] ? 'dihapus' : '' }}" wire:key="galeri-{{ $awalan }}-lama-{{ $foto['id'] }}">
                    <img src="{{ asset('storage/'.$foto['gambar']) }}" alt="Foto galeri">
                    @if ($foto['hapus'])
                        <small class="err-msg">Akan dihapus saat disimpan.</small>
                        <button type="button" class="btn s" wire:click="tandaiHapusGaleri({{ $i }})">Batal hapus</button>
                    @else
                        <button type="button" class="btn s d" wire:click="tandaiHapusGaleri({{ $i }})">Hapus</button>
                    @endif
                </div>
            @endforeach

            @foreach ($galeriBaru as $i => $file)
                <div class="galeri-item" wire:key="galeri-{{ $awalan }}-baru-{{ $i }}-{{ $file->getFilename() }}">
                    <img src="{{ $file->temporaryUrl() }}" alt="Pratinjau foto galeri baru">
                    <span class="bd g">Baru</span>
                    @error("galeriBaru.$i") <small class="err-msg">{{ $message }}</small> @enderror
                    <button type="button" class="btn s" wire:click="batalGaleriBaru({{ $i }})">Batalkan</button>
                </div>
            @endforeach
        </div>
    @endif
</div>
