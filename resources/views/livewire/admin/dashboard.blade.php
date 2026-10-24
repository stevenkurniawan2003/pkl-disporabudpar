@php
    $angka = fn ($n) => number_format((int) $n, 0, ',', '.');
    $totalDomestik = array_sum($grafik['domestik']);
    $totalMancanegara = array_sum($grafik['mancanegara']);
@endphp
<div>
    <x-admin.topbar judul="Dashboard" sub="Ringkasan data Disporabudpar Kabupaten Jember" />

    <div class="stats">
        <div class="card stat"><small>Wisata</small><b>{{ $angka($jumlahWisata) }}</b><span>{{ $angka($jumlahUnggulan) }} unggulan</span></div>
        <div class="card stat"><small>Hotel</small><b>{{ $angka($jumlahHotel) }}</b><span>data terdaftar</span></div>
        <div class="card stat"><small>Berita</small><b>{{ $angka($jumlahBerita) }}</b><span>{{ $angka($jumlahBeritaTerbit) }} terbit</span></div>
        <div class="card stat"><small>Kesenian</small><b>{{ $angka($jumlahKesenian) }}</b><span>data terdaftar</span></div>
        <div class="card stat"><small>Ekraf</small><b>{{ $angka($jumlahEkraf) }}</b><span>pelaku usaha</span></div>
    </div>

    <div class="card grafik-kartu">
        <div class="grafik-kepala">
            <div>
                <h3>Kunjungan hotel per bulan, {{ $tahun }}</h3>
                <p>
                    Total {{ $tahun }}: <b>{{ $angka($totalDomestik) }}</b> domestik · <b>{{ $angka($totalMancanegara) }}</b> mancanegara
                    · <b>{{ $angka($totalDomestik + $totalMancanegara) }}</b> tamu
                </p>
            </div>
            <select wire:model.live="tahun" aria-label="Pilih tahun grafik">
                @foreach ($daftarTahun as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
        </div>

        @if ($totalDomestik + $totalMancanegara === 0)
            <p class="grafik-kosong">Belum ada data kunjungan hotel di tahun {{ $tahun }}. Isi lewat menu Data Kunjungan Hotel.</p>
        @endif

        <div class="grafik-wadah" wire:ignore>
            <canvas id="grafik-kunjungan-hotel" role="img" aria-label="Grafik garis jumlah tamu hotel domestik dan mancanegara per bulan"></canvas>
        </div>

        <details class="grafik-tabel">
            <summary>Lihat tabel data</summary>
            <div class="tw">
                <table>
                    <thead><tr><th>Bulan</th><th class="n">Domestik</th><th class="n">Mancanegara</th><th class="n">Total</th></tr></thead>
                    <tbody>
                        @foreach ($daftarBulan as $no => $nama)
                            <tr wire:key="tabel-grafik-{{ $tahun }}-{{ $no }}">
                                <td>{{ $nama }}</td>
                                <td class="n">{{ $angka($grafik['domestik'][$no - 1]) }}</td>
                                <td class="n">{{ $angka($grafik['mancanegara'][$no - 1]) }}</td>
                                <td class="n"><b>{{ $angka($grafik['domestik'][$no - 1] + $grafik['mancanegara'][$no - 1]) }}</b></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</div>

@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
@endassets

@script
<script>
    const canvas = document.getElementById('grafik-kunjungan-hotel');
    const angka = (n) => new Intl.NumberFormat('id-ID').format(n);

    // Warna diambil dari token CSS (lihat .grafik-kartu di admin.css) supaya ikut tema terang/gelap.
    const warna = () => {
        const css = getComputedStyle(canvas.closest('.grafik-kartu'));
        const v = (nama) => css.getPropertyValue(nama).trim();
        return { domestik: v('--seri-domestik'), mancanegara: v('--seri-mancanegara'), teks: v('--ink'), redup: v('--mut'), garis: v('--line'), latar: v('--bg') };
    };

    const seri = (label, nilai, kunci, w, align) => ({
        label,
        data: nilai,
        borderColor: w[kunci],
        backgroundColor: w[kunci],
        borderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBorderColor: w.latar,
        pointBorderWidth: 2,
        tension: 0.25,
        datalabels: { align, anchor: align === 'top' ? 'end' : 'start' },
    });

    const buatData = (d, w) => ({
        labels: d.label,
        datasets: [
            seri('Domestik', d.domestik, 'domestik', w, 'top'),
            seri('Mancanegara', d.mancanegara, 'mancanegara', w, 'bottom'),
        ],
    });

    let w = warna();
    const grafik = new Chart(canvas, {
        type: 'line',
        data: buatData(@js($grafik), w),
        plugins: [ChartDataLabels],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 22, right: 12, bottom: 4, left: 4 } },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'start', labels: { color: w.teks, usePointStyle: true, pointStyle: 'line', boxWidth: 18, font: { family: 'Plus Jakarta Sans' } } },
                tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${angka(c.parsed.y)} tamu` } },
                // Angka tiap titik selalu tampil; label yang bertabrakan disembunyikan otomatis (tetap ada di tooltip & tabel).
                datalabels: { color: w.redup, offset: 4, clamp: true, display: 'auto', formatter: (n) => angka(n), font: { size: 10, weight: '600', family: 'Plus Jakarta Sans' } },
            },
            scales: {
                x: { grid: { display: false }, border: { color: w.garis }, ticks: { color: w.redup } },
                y: { beginAtZero: true, grace: '10%', grid: { color: w.garis }, border: { display: false }, ticks: { color: w.redup, precision: 0, callback: (n) => angka(n) } },
            },
        },
    });

    $wire.on('grafik-kunjungan-hotel', ({ data }) => {
        grafik.data = buatData(data, w);
        grafik.update();
    });

    // Tombol tema (◐) mengganti data-theme di <html>: warnakan ulang grafik.
    const pengamat = new MutationObserver(() => {
        w = warna();
        grafik.data.datasets.forEach((ds, i) => {
            const kunci = i === 0 ? 'domestik' : 'mancanegara';
            ds.borderColor = ds.backgroundColor = w[kunci];
            ds.pointBorderColor = w.latar;
        });
        const o = grafik.options;
        o.plugins.legend.labels.color = w.teks;
        o.plugins.datalabels.color = w.redup;
        o.scales.x.border.color = o.scales.y.grid.color = w.garis;
        o.scales.x.ticks.color = o.scales.y.ticks.color = w.redup;
        grafik.update('none');
    });
    pengamat.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    document.addEventListener('livewire:navigating', () => { pengamat.disconnect(); grafik.destroy(); }, { once: true });
</script>
@endscript
