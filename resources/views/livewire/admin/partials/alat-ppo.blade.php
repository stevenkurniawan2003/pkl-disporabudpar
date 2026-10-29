{{--
    Toolbar daftar PPO (Database Atlet/Pelatih/Cabor, Data Atlet Berprestasi): pilihan cabor, pencarian,
    tombol tambah, dan tombol Extract PDF/Excel yang membawa filter yang sedang aktif.
    Parameter: $jenis (atlet|pelatih|cabor|prestasi), $placeholder, $daftarCabor, opsional $tambah = [komponen, event, label].
--}}
@php $filterEkspor = array_filter(['cabor' => $cabor, 'q' => trim($cari)]); @endphp
<div class="bar-tools">
    <select wire:model.live="cabor" aria-label="Pilih cabor" class="grow">
        <option value="">Semua cabor</option>
        @foreach ($daftarCabor as $kode => $nama)
            <option value="{{ $kode }}">{{ $nama }}</option>
        @endforeach
    </select>
    <input type="search" wire:model.live.debounce.300ms="cari" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}">
    @isset($tambah)
        <button type="button" class="btn p" wire:click="$dispatchTo('{{ $tambah[0] }}', '{{ $tambah[1] }}')">{{ $tambah[2] }}</button>
    @endisset
</div>
<div class="act" style="justify-content:flex-end;margin:-4px 0 12px">
    <span style="color:var(--mut);align-self:center">Extract data (sesuai filter):</span>
    <a class="btn s" href="{{ route('admin.ppo.ekspor', ['jenis' => $jenis, 'format' => 'pdf', 'unduh' => 0] + $filterEkspor) }}" target="_blank" rel="noopener">🖨 Cetak</a>
    <a class="btn s" href="{{ route('admin.ppo.ekspor', ['jenis' => $jenis, 'format' => 'pdf'] + $filterEkspor) }}">⬇ PDF</a>
    <a class="btn s" href="{{ route('admin.ppo.ekspor', ['jenis' => $jenis, 'format' => 'xlsx'] + $filterEkspor) }}">⬇ Excel</a>
</div>
