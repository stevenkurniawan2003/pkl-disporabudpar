@php $tgl = fn ($d) => $d?->locale('id')->translatedFormat('j F Y') ?? '–'; @endphp
<div>
    <x-admin.topbar judul="Perpanjangan Kartu Induk Kesenian" sub="Cari nomor induk dari data master, cek datanya, lalu ajukan perpanjangan untuk membuat kartu" />

    @if ($pesan)
        <div class="alert {{ $jenisPesan }}" role="{{ $jenisPesan === 'err' ? 'alert' : 'status' }}" wire:key="pesan-{{ md5($pesan) }}">
            <span>{{ $pesan }}</span>
            @if ($baruDiajukan)
                <a class="btn s p" href="{{ route('admin.kartu-kik', $baruDiajukan) }}" target="_blank" rel="noopener">Unduh kartu (PDF)</a>
            @endif
            <button type="button" class="alert-x" wire:click="$set('pesan', null)" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="card">
        <form wire:submit="cek" class="bar-tools" style="margin:0">
            <input type="search" wire:model.live.debounce.300ms="nomorInduk" placeholder="Ketik nomor induk, contoh 2.5-048" aria-label="Nomor induk kesenian" autocomplete="off">
            <button type="submit" class="btn p">CEK</button>
            @if ($kesenianId)
                <button type="button" class="btn" wire:click="batal">Ganti kesenian</button>
            @endif
        </form>
        @error('nomorInduk') <small class="err-msg">{{ $message }}</small> @enderror

        @if ($saran->isNotEmpty())
            <div class="act" style="flex-wrap:wrap;margin-top:10px" aria-label="Kesenian yang cocok">
                @foreach ($saran as $s)
                    <button type="button" class="btn s" wire:key="saran-{{ $s->id_kesenian }}" wire:click="pilih(@js($s->nomor_induk))">{{ $s->nomor_induk }} · {{ $s->nama_kesenian }}</button>
                @endforeach
            </div>
        @endif
    </div>

    @if ($kesenian)
        <form wire:submit="ajukan" class="card" style="margin-top:16px">
            <h3>Form perpanjangan</h3>
            <div class="mb" style="padding:0">
                <div class="info"><small>Nomor induk</small><b>{{ $kesenian->nomor_induk }}</b></div>
                <div class="info"><small>Nama kesenian</small><p>{{ $kesenian->nama_kesenian }}</p></div>
                <div class="info"><small>Nama pemilik</small><p>{{ $kesenian->nama_pemilik }}</p></div>
                <div class="info"><small>Jenis kesenian</small><p>{{ $kesenian->jenis_lengkap }}</p></div>
                <div class="info full"><small>Alamat</small><p>{{ $kesenian->alamat_lengkap }}</p></div>
                <div class="info"><small>Jumlah anggota</small><p>{{ $kesenian->jumlah_anggota }} orang</p></div>
                <div class="info">
                    <small>Kartu terakhir</small>
                    <p>
                        @if ($kesenian->perpanjanganTerakhir)
                            Berlaku s.d. {{ $tgl($kesenian->perpanjanganTerakhir->berlaku_sampai) }}
                            <span class="bd {{ $kesenian->perpanjanganTerakhir->masih_berlaku ? 'g' : 'warn' }}">{{ $kesenian->perpanjanganTerakhir->masih_berlaku ? 'Masih berlaku' : 'Kedaluwarsa' }}</span>
                        @else
                            Belum pernah dibuat
                        @endif
                    </p>
                </div>
                @if ($kesenian->status !== 'aktif')
                    <div class="alert err full" role="alert">Status kesenian ini Non Aktif. Pastikan datanya sudah benar sebelum mengajukan perpanjangan.</div>
                @endif

                <div class="fd">
                    <label for="kik_tanggal">Tanggal pengajuan perpanjangan</label>
                    <input id="kik_tanggal" type="date" wire:model.live="tanggal_pengajuan">
                    @error('tanggal_pengajuan') <small class="err-msg">{{ $message }}</small> @enderror
                </div>

                <div class="fd">
                    <label for="kik_tahun">Tahun berakhirnya KIK</label>
                    <select id="kik_tahun" wire:model="tahun_berakhir">
                        @foreach ($pilihanTahun as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('tahun_berakhir') <small class="err-msg">{{ $message }}</small> @enderror
                </div>

                <div class="fd full">
                    <label for="kik_foto">Foto diri ketua sanggar/komunitas/lembaga</label>
                    <label class="up">
                        Klik untuk memilih foto (JPG/PNG, maks 2 MB, rasio 3×4 disarankan)
                        <input id="kik_foto" type="file" wire:model="fotoKetua" accept="image/png,image/jpeg">
                    </label>
                    <div wire:loading wire:target="fotoKetua" style="color:var(--pinkink)">Mengunggah…</div>
                    @error('fotoKetua') <small class="err-msg">{{ $message }}</small> @enderror
                    @if ($fotoKetua && ! $errors->has('fotoKetua') && $fotoKetua->isPreviewable())
                        <div class="thumb" style="max-width:120px"><img src="{{ $fotoKetua->temporaryUrl() }}" alt="Pratinjau foto ketua"></div>
                    @endif
                </div>
            </div>
            <div class="mf" style="padding:0;border:0">
                <button type="submit" class="btn p" wire:loading.attr="disabled" wire:target="ajukan,fotoKetua">
                    <span wire:loading.remove wire:target="ajukan">Ajukan Perpanjangan</span>
                    <span wire:loading wire:target="ajukan">Memproses…</span>
                </button>
            </div>
        </form>
    @endif

    <h3 style="margin:24px 0 10px">Riwayat perpanjangan</h3>
    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>Tanggal pengajuan</th>
                    <th>Nomor induk</th>
                    <th>Nama kesenian</th>
                    <th>Berlaku sampai</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $r)
                    <tr wire:key="riwayat-{{ $r->id_perpanjangan }}">
                        <td>{{ $tgl($r->tanggal_pengajuan) }}</td>
                        <td>{{ $r->kesenian->nomor_induk ?? '–' }}</td>
                        <td>{{ $r->kesenian->nama_kesenian ?? '–' }}</td>
                        <td>{{ $tgl($r->berlaku_sampai) }} <span class="bd {{ $r->masih_berlaku ? 'g' : 'warn' }}">{{ $r->masih_berlaku ? 'Berlaku' : 'Kedaluwarsa' }}</span></td>
                        <td><div class="act"><a class="btn s" href="{{ route('admin.kartu-kik', $r) }}" target="_blank" rel="noopener">Unduh kartu</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada perpanjangan yang diajukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pg">
        <span>Menampilkan {{ $riwayat->count() }} dari {{ $riwayat->total() }} data</span>
        <span class="pgn">
            <button type="button" class="btn s" wire:click="previousPage" @disabled($riwayat->onFirstPage())>‹ Sebelumnya</button>
            Halaman {{ $riwayat->currentPage() }} dari {{ max($riwayat->lastPage(), 1) }}
            <button type="button" class="btn s" wire:click="nextPage" @disabled(! $riwayat->hasMorePages())>Berikutnya ›</button>
        </span>
    </div>
</div>
