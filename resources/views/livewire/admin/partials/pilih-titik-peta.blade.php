{{--
    Pemilih titik lokasi lewat peta (Leaflet + OpenStreetMap). Klik peta / geser penanda,
    latitude & longitude langsung terisi ke properti Livewire `latitude` dan `longitude`.
    Parameter: $awalan (prefix id), $label, $keterangan (opsional), $opsional (bool, default false).
--}}
@php($opsional = $opsional ?? false)
<div class="fd full">
    <label>{{ $label }} @if ($opsional)<span class="opsional">(opsional{{ filled($keterangan ?? null) ? ', '.$keterangan : '' }})</span>@endif</label>

    <div
        wire:ignore
        x-data="{
            peta: null,
            penanda: null,
            init() {
                const mulai = () => window.L ? this.buatPeta() : setTimeout(mulai, 100);
                mulai();
            },
            buatPeta() {
                const lat = parseFloat(this.$wire.latitude);
                const lng = parseFloat(this.$wire.longitude);
                const adaTitik = ! isNaN(lat) && ! isNaN(lng);

                this.peta = L.map(this.$refs.peta).setView(adaTitik ? [lat, lng] : [-8.1724, 113.6995], adaTitik ? 15 : 10);
                this.peta.attributionControl.setPrefix(false);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href=&quot;https://www.openstreetmap.org/copyright&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot;>OpenStreetMap</a>',
                }).addTo(this.peta);

                if (adaTitik) {
                    this.pasangPenanda(lat, lng);
                }

                this.peta.on('click', (e) => this.setTitik(e.latlng.lat, e.latlng.lng));
                setTimeout(() => this.peta.invalidateSize(), 200);
            },
            pasangPenanda(lat, lng) {
                if (this.penanda) {
                    this.penanda.setLatLng([lat, lng]);
                    return;
                }
                this.penanda = L.marker([lat, lng], { draggable: true }).addTo(this.peta);
                this.penanda.on('dragend', () => {
                    const posisi = this.penanda.getLatLng();
                    this.setTitik(posisi.lat, posisi.lng);
                });
            },
            setTitik(lat, lng) {
                this.pasangPenanda(lat, lng);
                this.$wire.latitude = lat.toFixed(7);
                this.$wire.longitude = lng.toFixed(7);
            },
            hapusTitik() {
                if (this.penanda) {
                    this.peta.removeLayer(this.penanda);
                    this.penanda = null;
                }
                this.$wire.latitude = '';
                this.$wire.longitude = '';
            },
        }"
    >
        <div x-ref="peta" class="peta-pilih" role="application" aria-label="Peta untuk memilih titik lokasi"></div>
        <div class="peta-pilih-aksi">
            <small>Klik peta untuk menentukan titik, atau geser penanda untuk memperbaikinya.</small>
            @if ($opsional)
                <button type="button" class="btn s" x-on:click="hapusTitik()">Hapus titik</button>
            @endif
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div class="fd">
            <label for="{{ $awalan }}_lat">Latitude</label>
            <input id="{{ $awalan }}_lat" type="text" wire:model="latitude" placeholder="Pilih di peta" readonly>
            @error('latitude') <small class="err-msg">{{ $message }}</small> @enderror
        </div>
        <div class="fd">
            <label for="{{ $awalan }}_lng">Longitude</label>
            <input id="{{ $awalan }}_lng" type="text" wire:model="longitude" placeholder="Pilih di peta" readonly>
            @error('longitude') <small class="err-msg">{{ $message }}</small> @enderror
        </div>
    </div>
</div>

