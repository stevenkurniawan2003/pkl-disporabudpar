{{-- Titik lokasi opsional untuk Peta Wisata (latitude/longitude berpasangan). Parameter: $awalan, $keterangan. --}}
<div class="fd full">
    <label>Titik lokasi <span class="opsional">(opsional, {{ $keterangan }})</span></label>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div class="fd">
            <label for="{{ $awalan }}_lat">Latitude</label>
            <input id="{{ $awalan }}_lat" type="text" inputmode="decimal" wire:model="latitude" placeholder="-8.1724">
            @error('latitude') <small class="err-msg">{{ $message }}</small> @enderror
        </div>
        <div class="fd">
            <label for="{{ $awalan }}_lng">Longitude</label>
            <input id="{{ $awalan }}_lng" type="text" inputmode="decimal" wire:model="longitude" placeholder="113.6995">
            @error('longitude') <small class="err-msg">{{ $message }}</small> @enderror
        </div>
    </div>
</div>
