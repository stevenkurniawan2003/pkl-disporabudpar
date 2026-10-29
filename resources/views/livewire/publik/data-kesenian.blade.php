<div>
<div class="page"><h1>Data Kesenian</h1><p>Sanggar, komunitas, dan lembaga kesenian Kabupaten Jember yang terdaftar dalam Kartu Induk Kesenian.</p>

<div class="tools">
    <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari nama kesenian atau nomor induk…" aria-label="Cari kesenian">
</div>
<div class="tools">
    <select wire:model.live="kecamatan" aria-label="Pilih kecamatan">
        <option value="">-- Pilih Kecamatan --</option>
        @foreach ($daftarKecamatan as $kc)
            <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
        @endforeach
    </select>
    <select wire:model.live="desa" aria-label="Pilih desa" @disabled($kecamatan === '') wire:key="pub-desa-{{ $kecamatan }}">
        <option value="">-- Pilih Desa --</option>
        @foreach ($daftarDesa as $d)
            <option value="{{ $d->id }}">{{ $d->nama }}</option>
        @endforeach
    </select>
    <select wire:model.live="jenis" aria-label="Pilih jenis kesenian" class="pilih-lebar">
        <option value="">-- Pilih Jenis Kesenian --</option>
        @foreach ($daftarJenis as $kode => $nama)
            <option value="{{ $kode }}">{{ $kode }} {{ $nama }}</option>
        @endforeach
    </select>
    <button type="button" class="chip" wire:click="resetFilter">↺ Reset filter</button>
</div>

<p class="meta">{{ number_format($daftar->total(), 0, ',', '.') }} data kesenian</p>
<div class="tw">
    <table>
        <thead>
            <tr><th class="no">No</th><th>Nomor induk</th><th>Nama kesenian</th><th>Jenis kesenian</th><th>Kecamatan</th><th>Desa</th><th class="n">Anggota</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($daftar as $i => $k)
                <tr wire:key="pub-kesenian-{{ $k->id_kesenian }}">
                    <td class="no">{{ $daftar->firstItem() + $i }}</td>
                    <td>{{ $k->nomor_induk }}</td>
                    <td><b>{{ $k->nama_kesenian }}</b></td>
                    <td>{{ $k->jenis_lengkap }}</td>
                    <td>{{ $k->kecamatan->nama ?? '–' }}</td>
                    <td>{{ $k->desa->nama ?? '–' }}</td>
                    <td class="n">{{ $k->jumlah_anggota }}</td>
                    <td><span class="bd">{{ $k->status_label }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8" class="kosong-sel">Tidak ada data kesenian yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $daftar->links('livewire.publik.partials.pagination', ['scrollTo' => '.page']) }}
</div>
</div>
