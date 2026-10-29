@php $angka = fn ($n) => number_format((int) $n, 0, ',', '.'); @endphp
<div>
    <x-admin.topbar judul="Cetak Data EKRAF" sub="Saring data master pelaku ekraf lalu unduh PDF atau cetak langsung" />

    <div class="card">
        <h3 style="text-align:center">CETAK</h3>
        <div class="mb" style="padding:0">
            <div class="fd">
                <label for="cetak_ekraf_kecamatan">Pilih kecamatan</label>
                <select id="cetak_ekraf_kecamatan" wire:model.live="kecamatan">
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach ($daftarKecamatan as $kc)
                        <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fd">
                <label for="cetak_ekraf_desa">Pilih desa</label>
                <select id="cetak_ekraf_desa" wire:model.live="desa" @disabled($kecamatan === '') wire:key="cetak-ekraf-desa-{{ $kecamatan }}">
                    <option value="">-- Pilih Desa --</option>
                    @foreach ($daftarDesa as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fd">
                <label for="cetak_ekraf_subsektor">Pilih subsektor</label>
                <select id="cetak_ekraf_subsektor" wire:model.live="subsektor">
                    <option value="">-- Pilih Sektor --</option>
                    @foreach ($daftarSubsektor as $kode => $nama)
                        <option value="{{ $kode }}">{{ $kode }} {{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fd">
                <label for="cetak_ekraf_status">Pilih status</label>
                <select id="cetak_ekraf_status" wire:model.live="status">
                    <option value="">-- Pilih Status --</option>
                    @foreach ($daftarStatus as $nilai => $label)
                        <option value="{{ $nilai }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="act" style="justify-content:flex-end;margin-top:14px">
            <button type="button" class="btn" wire:click="resetFilter">↺ Reset Filter</button>
            <a class="btn" href="{{ route('admin.cetak-ekraf.pdf', $filter) }}" target="_blank" rel="noopener">🖨 Cetak</a>
            <a class="btn p" href="{{ route('admin.cetak-ekraf.pdf', $filter + ['unduh' => 1]) }}">⬇ Unduh PDF</a>
        </div>
    </div>

    <p style="margin:16px 0 8px;color:var(--mut)">
        {{ $jumlah }} data cocok dengan filter.
        @if ($jumlah > $daftar->count()) Pratinjau menampilkan {{ $daftar->count() }} data pertama; PDF berisi semuanya. @endif
    </p>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th class="n">No</th>
                    <th>ID usaha/pelaku</th>
                    <th>Nama usaha/pelaku</th>
                    <th>Subsektor</th>
                    <th>Alamat</th>
                    <th>Kecamatan</th>
                    <th>Desa</th>
                    <th class="n">Omzet</th>
                    <th>Telp</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $i => $e)
                    <tr wire:key="cetak-ekraf-{{ $e->id_ekraf }}">
                        <td class="n">{{ $i + 1 }}</td>
                        <td>{{ $e->nomor_ekraf }}</td>
                        <td>{{ $e->nama_usaha }}</td>
                        <td>{{ $e->subsektor_lengkap }}</td>
                        <td>{{ $e->alamat }}</td>
                        <td>{{ $e->kecamatan->nama ?? '–' }}</td>
                        <td>{{ $e->desa->nama ?? '–' }}</td>
                        <td class="n">{{ $angka($e->omzet) }}</td>
                        <td>{{ $e->no_telepon ?? '–' }}</td>
                        <td>{{ $e->status_label }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="empty">Tidak ada data pelaku ekraf yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
