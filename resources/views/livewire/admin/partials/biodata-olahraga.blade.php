{{-- Field biodata form Atlet/Pelatih (trait FormBiodataOlahraga). Parameter: $awalan (awalan id input). --}}
<div class="fd full">
    <label for="{{ $awalan }}_nama">Nama lengkap</label>
    <input id="{{ $awalan }}_nama" type="text" wire:model="nama_lengkap">
    @error('nama_lengkap') <small class="err-msg">{{ $message }}</small> @enderror
</div>

<div class="fd">
    <label for="{{ $awalan }}_tempat">Tempat lahir</label>
    <input id="{{ $awalan }}_tempat" type="text" wire:model="tempat_lahir" placeholder="Contoh: Jember">
    @error('tempat_lahir') <small class="err-msg">{{ $message }}</small> @enderror
</div>

<div class="fd">
    <label for="{{ $awalan }}_tanggal">Tanggal lahir</label>
    <input id="{{ $awalan }}_tanggal" type="date" wire:model.live="tanggal_lahir" max="{{ now()->subDay()->toDateString() }}">
    @error('tanggal_lahir') <small class="err-msg">{{ $message }}</small> @enderror
</div>

<div class="fd">
    <label for="{{ $awalan }}_umur">Umur</label>
    <input id="{{ $awalan }}_umur" type="text" value="{{ $umur !== null ? $umur.' tahun' : '' }}" placeholder="Terisi otomatis dari tanggal lahir" readonly>
</div>

<div class="fd">
    <label for="{{ $awalan }}_jk">Jenis kelamin</label>
    <select id="{{ $awalan }}_jk" wire:model="jenis_kelamin">
        <option value="">Pilih L/P…</option>
        <option value="L">L (Laki-laki)</option>
        <option value="P">P (Perempuan)</option>
    </select>
    @error('jenis_kelamin') <small class="err-msg">{{ $message }}</small> @enderror
</div>

<div class="fd full">
    <label for="{{ $awalan }}_alamat">Alamat</label>
    <input id="{{ $awalan }}_alamat" type="text" wire:model="alamat" placeholder="Alamat rumah">
    @error('alamat') <small class="err-msg">{{ $message }}</small> @enderror
</div>

<div class="fd">
    <label for="{{ $awalan }}_kecamatan">Kecamatan</label>
    <select id="{{ $awalan }}_kecamatan" wire:model="kecamatan_id">
        <option value="">Pilih kecamatan…</option>
        @foreach ($daftarKecamatan as $kc)
            <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
        @endforeach
    </select>
    @error('kecamatan_id') <small class="err-msg">{{ $message }}</small> @enderror
</div>

<div class="fd">
    <label for="{{ $awalan }}_hp">No. HP <span class="opsional">(opsional)</span></label>
    <input id="{{ $awalan }}_hp" type="tel" wire:model="no_hp" placeholder="0812 3456 7890">
    @error('no_hp') <small class="err-msg">{{ $message }}</small> @enderror
</div>

<div class="fd">
    <label for="{{ $awalan }}_cabor">Cabang olahraga</label>
    <select id="{{ $awalan }}_cabor" wire:model="kode_cabor">
        <option value="">Pilih cabor…</option>
        @foreach ($daftarCabor as $kode => $nama)
            <option value="{{ $kode }}">{{ $nama }}</option>
        @endforeach
    </select>
    @error('kode_cabor') <small class="err-msg">{{ $message }}</small> @enderror
</div>

<div class="fd">
    <label for="{{ $awalan }}_status">Status keaktifan</label>
    <select id="{{ $awalan }}_status" wire:model="status">
        <option value="aktif">Aktif</option>
        <option value="nonaktif">Tidak Aktif</option>
    </select>
    @error('status') <small class="err-msg">{{ $message }}</small> @enderror
</div>
