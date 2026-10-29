<div>
    <x-admin.topbar judul="Cetak Data Kesenian" sub="Saring data master kesenian lalu unduh PDF atau cetak langsung" />

    <div class="card">
        <h3 style="text-align:center">CETAK</h3>
        <div class="mb" style="padding:0">
            <div class="fd">
                <label for="cetak_kecamatan">Pilih kecamatan</label>
                <select id="cetak_kecamatan" wire:model.live="kecamatan">
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach ($daftarKecamatan as $kc)
                        <option value="{{ $kc->id }}">{{ $kc->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fd">
                <label for="cetak_desa">Pilih desa</label>
                <select id="cetak_desa" wire:model.live="desa" @disabled($kecamatan === '') wire:key="cetak-desa-{{ $kecamatan }}">
                    <option value="">-- Pilih Desa --</option>
                    @foreach ($daftarDesa as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fd">
                <label for="cetak_jenis">Pilih jenis kesenian</label>
                <select id="cetak_jenis" wire:model.live="jenis">
                    <option value="">-- Pilih Jenis Kesenian --</option>
                    @foreach ($daftarJenis as $kode => $nama)
                        <option value="{{ $kode }}">{{ $kode }} {{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fd">
                <label for="cetak_status">Pilih status</label>
                <select id="cetak_status" wire:model.live="status">
                    <option value="">-- Pilih Status --</option>
                    @foreach ($daftarStatus as $nilai => $label)
                        <option value="{{ $nilai }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fd">
                <label for="cetak_tahun">Pilih tahun terdaftar</label>
                <select id="cetak_tahun" wire:model.live="tahun">
                    <option value="">-- Semua Tahun --</option>
                    @foreach ($daftarTahun as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="act" style="justify-content:flex-end;margin-top:14px">
            <button type="button" class="btn" wire:click="resetFilter">↺ Reset Filter</button>
            <a class="btn" href="{{ route('admin.cetak-kesenian.pdf', $filter) }}" target="_blank" rel="noopener">🖨 Cetak</a>
            <a class="btn p" href="{{ route('admin.cetak-kesenian.pdf', $filter + ['unduh' => 1]) }}">⬇ Unduh PDF</a>
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
                    <th>Nomor induk</th>
                    <th>Nama kesenian</th>
                    <th>Nama pemilik</th>
                    <th>Jenis kesenian</th>
                    <th>Alamat</th>
                    <th>Kecamatan</th>
                    <th>Desa</th>
                    <th class="n">Anggota</th>
                    <th>Telp</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $i => $k)
                    <tr wire:key="cetak-{{ $k->id_kesenian }}">
                        <td class="n">{{ $i + 1 }}</td>
                        <td>{{ $k->nomor_induk }}</td>
                        <td>{{ $k->nama_kesenian }}</td>
                        <td>{{ $k->nama_pemilik }}</td>
                        <td>{{ $k->jenis_lengkap }}</td>
                        <td>{{ $k->alamat }}</td>
                        <td>{{ $k->kecamatan->nama ?? '–' }}</td>
                        <td>{{ $k->desa->nama ?? '–' }}</td>
                        <td class="n">{{ $k->jumlah_anggota }}</td>
                        <td>{{ $k->no_telepon ?? '–' }}</td>
                        <td>{{ $k->status_label }}</td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="empty">Tidak ada data kesenian yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
