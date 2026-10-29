{{--
    Beberapa line chart kategori (bukan per bulan) untuk halaman publik, misalnya dashboard Kebudayaan.
    Parameter: $daftarGrafik = [['id', 'judul', 'satuan', 'label' => [...], 'seri' => [['label', 'warna' => '--seri-1', 'data' => [...]], ...]], ...]
    Data statis per render (tidak ada filter), jadi tidak perlu event Livewire.
--}}
@foreach ($daftarGrafik as $g)
    <div class="card grafik-kartu" wire:key="{{ $g['id'] }}">
        <div class="grafik-kepala"><h2>{{ $g['judul'] }}</h2></div>
        @if (collect($g['seri'])->sum(fn ($s) => array_sum($s['data'])) === 0)
            <p class="info-kosong">Belum ada data.</p>
        @else
            <div class="grafik-wadah {{ count($g['label']) > 12 ? 'lebar' : '' }}" wire:ignore>
                <canvas id="{{ $g['id'] }}" data-grafik="{{ json_encode($g) }}" role="img" aria-label="{{ $g['judul'] }}. Grafik garis jumlah {{ $g['satuan'] }} per kategori."></canvas>
            </div>
        @endif
    </div>
@endforeach

@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
@endassets

@script
<script>
    const angka = (n) => new Intl.NumberFormat('id-ID').format(n);
    const daftar = [];

    // Warna dari token CSS (.grafik-kartu di publik.css) supaya ikut tema terang/gelap.
    const warna = (el) => {
        const css = getComputedStyle(el.closest('.grafik-kartu'));
        return (nama) => css.getPropertyValue(nama).trim();
    };

    $wire.$el.querySelectorAll('canvas[data-grafik]').forEach((canvas) => {
        const g = JSON.parse(canvas.dataset.grafik);
        const v = warna(canvas);

        const grafik = new Chart(canvas, {
            type: 'line',
            data: {
                labels: g.label,
                datasets: g.seri.map((s) => ({
                    label: s.label,
                    data: s.data,
                    warnaToken: s.warna,
                    borderColor: v(s.warna),
                    backgroundColor: v(s.warna),
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBorderColor: v('--bg'),
                    pointBorderWidth: 2,
                    tension: 0.25,
                })),
            },
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 22, right: 12, bottom: 4, left: 4 } },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: g.seri.length > 1, position: 'top', align: 'start', labels: { color: v('--ink'), usePointStyle: true, pointStyle: 'line', boxWidth: 18, font: { family: 'Plus Jakarta Sans' } } },
                    tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${angka(c.parsed.y)} ${g.satuan}` } },
                    datalabels: { color: v('--mut'), align: 'top', anchor: 'end', offset: 2, clamp: true, display: 'auto', formatter: (n) => angka(n), font: { size: 10, weight: '600', family: 'Plus Jakarta Sans' } },
                },
                scales: {
                    x: { grid: { display: false }, border: { color: v('--line') }, ticks: { color: v('--mut'), autoSkip: false, maxRotation: 70, minRotation: g.label.length > 8 ? 45 : 0, font: { size: 11 } } },
                    y: { beginAtZero: true, grace: '15%', grid: { color: v('--line') }, border: { display: false }, ticks: { color: v('--mut'), precision: 0, callback: (n) => angka(n) } },
                },
            },
        });

        daftar.push(grafik);
    });

    // Tombol tema (◐) mengganti data-theme di <html>: warnakan ulang semua grafik.
    const pengamat = new MutationObserver(() => {
        daftar.forEach((grafik) => {
            const v = warna(grafik.canvas);
            grafik.data.datasets.forEach((ds) => {
                ds.borderColor = ds.backgroundColor = v(ds.warnaToken);
                ds.pointBorderColor = v('--bg');
            });
            const o = grafik.options;
            o.plugins.legend.labels.color = v('--ink');
            o.plugins.datalabels.color = v('--mut');
            o.scales.x.border.color = o.scales.y.grid.color = v('--line');
            o.scales.x.ticks.color = o.scales.y.ticks.color = v('--mut');
            grafik.update('none');
        });
    });
    pengamat.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    document.addEventListener('livewire:navigating', () => { pengamat.disconnect(); daftar.forEach((g) => g.destroy()); }, { once: true });
</script>
@endscript
