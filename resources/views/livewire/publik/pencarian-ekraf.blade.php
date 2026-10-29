<div>
<div class="page"><h1>Pencarian Data Ekonomi Kreatif</h1><p>Cari pelaku ekonomi kreatif Kabupaten Jember berdasarkan kecamatan, desa, dan subsektor.</p>

<div class="card" style="margin-bottom:16px">
    <div class="tools" style="margin:0">
        <select wire:model.live="kecamatan" aria-label="Pilih kecamatan">
            <option value="">-- Pilih Kecamatan --</option>
            @foreach ($daftarKecamatan as $kc)
                <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
            @endforeach
        </select>
        <select wire:model.live="desa" aria-label="Pilih desa" @disabled($kecamatan === '') wire:key="pub-ekraf-desa-{{ $kecamatan }}">
            <option value="">-- Pilih Desa --</option>
            @foreach ($daftarDesa as $d)
                <option value="{{ $d->id }}">{{ $d->nama }}</option>
            @endforeach
        </select>
        <select wire:model.live="subsektor" aria-label="Pilih subsektor" class="pilih-lebar">
            <option value="">-- Pilih Sektor --</option>
            @foreach ($daftarSubsektor as $kode => $nama)
                <option value="{{ $kode }}">{{ $kode }} {{ $nama }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari nama usaha/pelaku…" aria-label="Cari nama usaha/pelaku">
        <button type="button" class="chip" wire:click="resetFilter">↺ Reset Filter</button>
    </div>
</div>

<p class="meta">{{ number_format($daftar->total(), 0, ',', '.') }} pelaku ekonomi kreatif</p>
<div class="tw">
    <table>
        <thead>
            <tr><th class="no">No</th><th>ID</th><th>Nama usaha/pelaku</th><th>Subsektor</th><th>Kecamatan</th><th>Desa</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($daftar as $i => $e)
                <tr wire:key="pub-ekraf-{{ $e->id_ekraf }}">
                    <td class="no">{{ $daftar->firstItem() + $i }}</td>
                    <td>{{ $e->nomor_ekraf }}</td>
                    <td><b>{{ $e->nama_usaha }}</b></td>
                    <td>{{ $e->subsektor_lengkap }}</td>
                    <td>{{ $e->kecamatan->nama ?? '–' }}</td>
                    <td>{{ $e->desa->nama ?? '–' }}</td>
                    <td><span class="bd">{{ $e->status_label }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="kosong-sel">Tidak ada pelaku ekonomi kreatif yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $daftar->links('livewire.publik.partials.pagination', ['scrollTo' => '.page']) }}
</div>
</div>
