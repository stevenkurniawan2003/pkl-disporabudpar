{{-- Dipakai bersama oleh PpoAtlet, PpoPelatih, dan PpoCabor (lihat PpoDatabase). --}}
<div>
<div class="page"><h1>{{ $teks['judul'] }}</h1><p>{{ $teks['deskripsi'] }}</p>

<div class="grafik-daftar">
    @include('livewire.publik.partials.grafik-garis', ['daftarGrafik' => $daftarGrafik])
</div>

<div class="tools" style="margin-top:20px">
    <select wire:model.live="cabor" aria-label="Pilih cabor" class="pilih-lebar">
        <option value="">Semua cabor</option>
        @foreach ($daftarCabor as $kode => $nama)
            <option value="{{ $kode }}">{{ $nama }}</option>
        @endforeach
    </select>
    <input type="search" wire:model.live.debounce.400ms="cari" placeholder="{{ $teks['placeholder'] }}" aria-label="{{ $teks['placeholder'] }}">
</div>

<p class="meta">{{ number_format($daftar->total(), 0, ',', '.') }} data</p>
<div class="tw">
    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                @foreach ($kolom as $k)
                    <th>{{ $k }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($baris as $i => $isi)
                <tr wire:key="ppo-{{ $daftar->currentPage() }}-{{ $i }}">
                    <td class="no">{{ $daftar->firstItem() + $i }}</td>
                    @foreach ($isi as $j => $nilai)
                        <td>@if ($j === 0)<b>{{ $nilai }}</b>@else{{ filled($nilai) ? $nilai : '–' }}@endif</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($kolom) + 1 }}" class="kosong-sel">Tidak ada data yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $daftar->links('livewire.publik.partials.pagination', ['scrollTo' => '.page']) }}
</div>
</div>
