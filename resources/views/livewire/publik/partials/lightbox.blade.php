{{--
    Lightbox galeri foto. Dipakai detail wisata/hotel dan detail berita.
    Parameter: $galeri (list berisi url, keterangan), $nama (untuk teks alternatif).
    Tombol pembukanya: <button class="dd-foto" data-i="...">. Script-nya ada di layouts/publik (delegasi klik).
--}}
<dialog class="lightbox" id="lightbox" aria-label="Galeri foto {{ $nama }}" data-foto='@json($galeri)' data-nama="{{ $nama }}">
    <figure>
        <img id="lb-img" src="" alt="">
        <figcaption><span id="lb-ket"></span><span id="lb-no" class="lb-no"></span></figcaption>
    </figure>
    <button type="button" class="lb-btn lb-tutup" data-aksi="tutup" aria-label="Tutup galeri"><i class="bi bi-x-lg"></i></button>
    @if (count($galeri) > 1)
        <button type="button" class="lb-btn lb-kiri" data-aksi="kiri" aria-label="Foto sebelumnya"><i class="bi bi-chevron-left"></i></button>
        <button type="button" class="lb-btn lb-kanan" data-aksi="kanan" aria-label="Foto berikutnya"><i class="bi bi-chevron-right"></i></button>
    @endif
</dialog>
