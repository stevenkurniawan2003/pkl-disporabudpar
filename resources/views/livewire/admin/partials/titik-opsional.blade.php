{{-- Titik lokasi opsional untuk Peta Wisata (latitude/longitude berpasangan). Parameter: $awalan, $keterangan. --}}
@include('livewire.admin.partials.pilih-titik-peta', ['awalan' => $awalan, 'label' => 'Titik lokasi', 'keterangan' => $keterangan, 'opsional' => true])
