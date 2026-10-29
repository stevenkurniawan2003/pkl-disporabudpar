@php $nomorFoto = 0; @endphp
<div>
<div class="page"><h1>Sorotan Prestasi</h1><p>Prestasi atlet Kabupaten Jember: galeri dokumentasi dan rekap medali.</p>

<div class="grafik-daftar">
    @include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])
</div>

<h2 class="judul-tabel">Galeri Prestasi</h2>
@if ($galeri->isEmpty())
    <p class="info-kosong" style="margin-top:12px">Belum ada dokumentasi prestasi.</p>
@else
    <div class="dd-galeri galeri-prestasi" style="margin-top:12px">
        @foreach ($galeri as $g)
            <figure wire:key="galeri-{{ $g->id_galeri_prestasi }}">
                @if ($g->jenis === 'video')
                    <video src="{{ $g->file_url }}" controls preload="metadata"></video>
                @else
                    <button type="button" class="dd-foto" data-i="{{ $nomorFoto++ }}" aria-label="Lihat foto: {{ $g->judul }}">
                        <img src="{{ $g->file_url }}" alt="{{ $g->judul }}" loading="lazy">
                    </button>
                @endif
                <figcaption>{{ $g->judul }}@if ($g->tahun) <span class="meta">· {{ $g->tahun }}</span>@endif</figcaption>
            </figure>
        @endforeach
    </div>
    @if ($fotoLightbox)
        @include('livewire.publik.partials.lightbox', ['galeri' => $fotoLightbox, 'nama' => 'Galeri Prestasi'])
    @endif
@endif

<h2 class="judul-tabel">Data Atlet Berprestasi</h2>
<div class="tools" style="margin-top:12px">
    <select wire:model.live="cabor" aria-label="Pilih cabor" class="pilih-lebar">
        <option value="">Semua cabor</option>
        @foreach ($daftarCabor as $kode => $nama)
            <option value="{{ $kode }}">{{ $nama }}</option>
        @endforeach
    </select>
    <select wire:model.live="tahun" aria-label="Pilih tahun">
        <option value="">Semua tahun</option>
        @foreach ($daftarTahun as $t)
            <option value="{{ $t }}">{{ $t }}</option>
        @endforeach
    </select>
</div>
<div class="tw">
    <table>
        <thead><tr><th class="no">No</th><th>Nama</th><th>Jenis medali</th><th>Cabor</th><th>Nomor pertandingan</th><th>Kejuaraan</th><th class="n">Tahun</th></tr></thead>
        <tbody>
            @forelse ($daftar as $i => $p)
                <tr wire:key="prestasi-{{ $p->id_prestasi }}">
                    <td class="no">{{ $daftar->firstItem() + $i }}</td>
                    <td><b>{{ $p->nama_atlet }}</b></td>
                    <td><span class="bd medali {{ strtolower($p->jenis_medali) }}">{{ \Illuminate\Support\Str::upper($p->jenis_medali) }}</span></td>
                    <td>{{ $p->nama_cabor }}</td>
                    <td>{{ $p->nomor_pertandingan }}</td>
                    <td>{{ $p->nama_kejuaraan ?? '–' }}</td>
                    <td class="n">{{ $p->tahun }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="kosong-sel">Belum ada data atlet berprestasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $daftar->links('livewire.publik.partials.pagination', ['scrollTo' => '.page']) }}
</div>
</div>
