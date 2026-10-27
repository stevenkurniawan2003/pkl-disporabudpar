@php
    $angka = fn ($n) => number_format((int) $n, 0, ',', '.');
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

    {{-- Grafik kunjungan wisata dan hotel, masing-masing punya filter tahun sendiri. --}}
    @foreach ($daftarGrafik as $g)
        @php
            $totalDomestik = array_sum($g['data']['domestik']);
            $totalMancanegara = array_sum($g['data']['mancanegara']);
        @endphp
        <div class="card grafik-kartu" wire:key="{{ $g['kunci'] }}">
            <div class="grafik-kepala">
                <div>
                    <h3>{{ $g['judul'] }}, {{ $g['tahun'] }}</h3>
                    <p>
                        Total {{ $g['tahun'] }}: <b>{{ $angka($totalDomestik) }}</b> domestik · <b>{{ $angka($totalMancanegara) }}</b> mancanegara
                        · <b>{{ $angka($totalDomestik + $totalMancanegara) }}</b> {{ $g['satuan'] }}
                    </p>
                </div>
                <select wire:model.live="{{ $g['model'] }}" aria-label="Pilih tahun {{ \Illuminate\Support\Str::lower($g['judul']) }}">
                    @foreach ($g['daftarTahun'] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            @if ($totalDomestik + $totalMancanegara === 0)
                <p class="grafik-kosong">Belum ada data di tahun {{ $g['tahun'] }}. Isi lewat menu {{ $g['menu'] }}.</p>
            @endif

            <div class="grafik-wadah" wire:ignore>
                <canvas id="{{ $g['kunci'] }}" data-satuan="{{ $g['satuan'] }}" data-grafik='@json($g['data'])' role="img" aria-label="Grafik garis {{ \Illuminate\Support\Str::lower($g['judul']) }}, domestik dan mancanegara"></canvas>
            </div>

            <details class="grafik-tabel">
                <summary>Lihat tabel data</summary>
                <div class="tw">
                    <table>
                        <thead><tr><th>Bulan</th><th class="n">Domestik</th><th class="n">Mancanegara</th><th class="n">Total</th></tr></thead>
                        <tbody>
                            @foreach ($daftarBulan as $no => $nama)
                                <tr wire:key="tabel-{{ $g['kunci'] }}-{{ $g['tahun'] }}-{{ $no }}">
                                    <td>{{ $nama }}</td>
                                    <td class="n">{{ $angka($g['data']['domestik'][$no - 1]) }}</td>
                                    <td class="n">{{ $angka($g['data']['mancanegara'][$no - 1]) }}</td>
                                    <td class="n"><b>{{ $angka($g['data']['domestik'][$no - 1] + $g['data']['mancanegara'][$no - 1]) }}</b></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    @endforeach
</div>

@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
@endassets

@script
<script>
    const angka = (n) => new Intl.NumberFormat('id-ID').format(n);

    // Warna diambil dari token CSS (lihat .grafik-kartu di admin.css) supaya ikut tema terang/gelap.
    const warna = (canvas) => {
        const css = getComputedStyle(canvas.closest('.grafik-kartu'));
        const v = (nama) => css.getPropertyValue(nama).trim();
        return { domestik: v('--seri-domestik'), mancanegara: v('--seri-mancanegara'), teks: v('--ink'), redup: v('--mut'), garis: v('--line'), latar: v('--bg') };
    };

    // Label garis bawah ditaruh di bawah titik, kecuali titiknya terlalu dekat sumbu X:
    // dipindah ke atas supaya tidak menabrak sumbu dan nama bulan.
    const posisiLabel = (atas) => (ctx) => {
        if (atas) return 'top';
        const y = ctx.chart.scales.y;
        const nilai = ctx.dataset.data[ctx.dataIndex];
        return (nilai - y.min) / ((y.max - y.min) || 1) < 0.18 ? 'top' : 'bottom';
    };

    const seri = (label, nilai, kunci, w, atas) => ({
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
        datalabels: { align: posisiLabel(atas), anchor: (ctx) => posisiLabel(atas)(ctx) === 'top' ? 'end' : 'start' },
    });

    const buatData = (d, w) => ({
        labels: d.label,
        datasets: [
            seri('Domestik', d.domestik, 'domestik', w, true),
            seri('Mancanegara', d.mancanegara, 'mancanegara', w, false),
        ],
    });

    const daftar = [];

    $wire.$el.querySelectorAll('.grafik-wadah canvas').forEach((canvas) => {
        const satuan = canvas.dataset.satuan;
        let w = warna(canvas);
        const grafik = new Chart(canvas, {
            type: 'line',
            data: buatData(JSON.parse(canvas.dataset.grafik), w),
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 22, right: 12, bottom: 8, left: 4 } },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'start', labels: { color: w.teks, usePointStyle: true, pointStyle: 'line', boxWidth: 18, font: { family: 'Plus Jakarta Sans' } } },
                    tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${angka(c.parsed.y)} ${satuan}` } },
                    // Angka tiap titik selalu tampil; label yang bertabrakan disembunyikan otomatis (tetap ada di tooltip & tabel).
                    datalabels: { color: w.redup, offset: 4, clamp: true, display: 'auto', formatter: (n) => angka(n), font: { size: 10, weight: '600', family: 'Plus Jakarta Sans' } },
                },
                scales: {
                    x: { grid: { display: false }, border: { color: w.garis }, ticks: { color: w.redup, padding: 8 } },
                    y: { beginAtZero: true, grace: '15%', grid: { color: w.garis }, border: { display: false }, ticks: { color: w.redup, precision: 0, callback: (n) => angka(n) } },
                },
            },
        });

        $wire.on(canvas.id, ({ data }) => {
            grafik.data = buatData(data, w);
            grafik.update();
        });

        daftar.push({ canvas, grafik, warnaiUlang: () => { w = warna(canvas); return w; } });
    });

    // Tombol tema (◐) mengganti data-theme di <html>: warnakan ulang grafik.
    const pengamat = new MutationObserver(() => {
        daftar.forEach(({ grafik, warnaiUlang }) => {
            const w = warnaiUlang();
            grafik.data.datasets.forEach((ds, i) => {
                ds.borderColor = ds.backgroundColor = w[i === 0 ? 'domestik' : 'mancanegara'];
                ds.pointBorderColor = w.latar;
            });
            const o = grafik.options;
            o.plugins.legend.labels.color = w.teks;
            o.plugins.datalabels.color = w.redup;
            o.scales.x.border.color = o.scales.y.grid.color = w.garis;
            o.scales.x.ticks.color = o.scales.y.ticks.color = w.redup;
            grafik.update('none');
        });
    });
    pengamat.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    document.addEventListener('livewire:navigating', () => { pengamat.disconnect(); daftar.forEach(({ grafik }) => grafik.destroy()); }, { once: true });
</script>
@endscript
