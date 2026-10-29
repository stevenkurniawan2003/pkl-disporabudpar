<div>
    <x-admin.topbar judul="Data Atlet Berprestasi" sub="Sorotan Prestasi: rekap medali atlet Kabupaten Jember" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <details class="card" style="margin-bottom:16px" open>
        <summary style="cursor:pointer;font-weight:600">Grafik jumlah atlet berprestasi tiap tahun</summary>
        <div style="margin-top:14px">@include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])</div>
    </details>

    <form wire:submit="simpan" class="card" style="margin-bottom:16px">
        <h3>{{ $ubahId ? 'Ubah data prestasi' : 'Input data atlet berprestasi' }}</h3>
        <div class="mb" style="padding:0">
            <div class="fd">
                <label for="prestasi_nama">Nama</label>
                <input id="prestasi_nama" type="text" wire:model="nama_atlet" list="saran-atlet" autocomplete="off">
                <datalist id="saran-atlet">
                    @foreach ($saranNama as $n)
                        <option value="{{ $n }}"></option>
                    @endforeach
                </datalist>
                @error('nama_atlet') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="prestasi_medali">Jenis medali</label>
                <select id="prestasi_medali" wire:model="jenis_medali">
                    <option value="">Pilih medali…</option>
                    @foreach ($daftarMedali as $m)
                        <option value="{{ $m }}">{{ \Illuminate\Support\Str::upper($m) }}</option>
                    @endforeach
                </select>
                @error('jenis_medali') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="prestasi_cabor">Cabor</label>
                <select id="prestasi_cabor" wire:model="kode_cabor">
                    <option value="">Pilih cabor…</option>
                    @foreach ($daftarCabor as $kode => $nama)
                        <option value="{{ $kode }}">{{ $nama }}</option>
                    @endforeach
                </select>
                @error('kode_cabor') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="prestasi_nomor">Nomor pertandingan</label>
                <input id="prestasi_nomor" type="text" wire:model="nomor_pertandingan" placeholder="Contoh: Lari 100 m putra">
                @error('nomor_pertandingan') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="prestasi_kejuaraan">Kejuaraan <span class="opsional">(opsional)</span></label>
                <input id="prestasi_kejuaraan" type="text" wire:model="nama_kejuaraan" placeholder="Contoh: Porprov Jatim IX">
                @error('nama_kejuaraan') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
            <div class="fd">
                <label for="prestasi_tahun">Tahun</label>
                <input id="prestasi_tahun" type="text" inputmode="numeric" maxlength="4" wire:model="tahun">
                @error('tahun') <small class="err-msg">{{ $message }}</small> @enderror
            </div>
        </div>
        <div class="act" style="justify-content:flex-end;margin-top:12px">
            @if ($ubahId)<button type="button" class="btn" wire:click="batal">Batal ubah</button>@endif
            <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="simpan">{{ $ubahId ? 'Simpan perubahan' : '+ Tambahkan ke tabel' }}</button>
        </div>
    </form>

    @include('livewire.admin.partials.alat-ppo', ['jenis' => 'prestasi', 'placeholder' => 'Cari nama atlet…'])

    <div class="tw">
        <table>
            <thead>
                <tr><th class="n">No</th><th>Nama</th><th>Jenis medali</th><th>Cabor</th><th>Nomor pertandingan</th><th>Kejuaraan</th><th class="n">Tahun</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($daftar as $i => $p)
                    <tr wire:key="prestasi-{{ $p->id_prestasi }}">
                        <td class="n">{{ $daftar->firstItem() + $i }}</td>
                        <td>{{ $p->nama_atlet }}</td>
                        <td><span class="bd medali {{ strtolower($p->jenis_medali) }}">{{ \Illuminate\Support\Str::upper($p->jenis_medali) }}</span></td>
                        <td>{{ $p->nama_cabor }}</td>
                        <td>{{ $p->nomor_pertandingan }}</td>
                        <td>{{ $p->nama_kejuaraan ?? '–' }}</td>
                        <td class="n">{{ $p->tahun }}</td>
                        <td>
                            <div class="act">
                                <button type="button" class="btn s" wire:click="ubah({{ $p->id_prestasi }})">Edit</button>
                                <button type="button" class="btn s d" wire:click="hapus({{ $p->id_prestasi }})" wire:confirm="Hapus prestasi &quot;{{ $p->nama_atlet }}&quot; ({{ $p->jenis_medali }})?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">Belum ada data atlet berprestasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pg">
        <span>Menampilkan {{ $daftar->count() }} dari {{ $daftar->total() }} data</span>
        <span class="pgn">
            <button type="button" class="btn s" wire:click="previousPage" @disabled($daftar->onFirstPage())>‹ Sebelumnya</button>
            Halaman {{ $daftar->currentPage() }} dari {{ max($daftar->lastPage(), 1) }}
            <button type="button" class="btn s" wire:click="nextPage" @disabled(! $daftar->hasMorePages())>Berikutnya ›</button>
        </span>
    </div>
</div>
