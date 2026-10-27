{{--
    Line chart kunjungan per bulan (Domestik & Mancanegara) untuk halaman publik.
    Dipakai halaman Data Kunjungan Wisata dan Data Kunjungan Hotel.
    Parameter: $grafik (label, domestik, mancanegara), $judulGrafik.
    Komponen Livewire-nya mengirim event "grafik-kunjungan" berisi data baru saat filter berubah.
--}}
<div class="card grafik-kartu">
    <div class="grafik-kepala">
        <h2>{{ $judulGrafik }}</h2>
        {{-- Pilih garis yang ditampilkan (sesuai chip di desain). Hanya mengubah grafik, tidak memuat ulang data. --}}
        <div class="seri-pilih" role="group" aria-label="Garis yang ditampilkan" wire:ignore>
            <button type="button" class="chip on" data-seri="semua" aria-pressed="true">Semua</button>
            <button type="button" class="chip" data-seri="domestik" aria-pressed="false">Domestik</button>
            <button type="button" class="chip" data-seri="mancanegara" aria-pressed="false">Mancanegara</button>
        </div>
    </div>
    <div class="grafik-wadah" wire:ignore>
        <canvas id="grafik-kunjungan" role="img" aria-label="Grafik garis jumlah kunjungan domestik dan mancanegara per bulan. Angka lengkap ada di tabel di bawahnya."></canvas>
    </div>
</div>

@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
@endassets

@script
<script>
    const canvas = document.getElementById('grafik-kunjungan');
    const angka = (n) => new Intl.NumberFormat('id-ID').format(n);

    // Warna dari token CSS (.grafik-kartu di publik.css) supaya ikut tema terang/gelap.
    const warna = () => {
        const css = getComputedStyle(canvas.closest('.grafik-kartu'));
        const v = (nama) => css.getPropertyValue(nama).trim();
        return { domestik: v('--seri-domestik'), mancanegara: v('--seri-mancanegara'), teks: v('--ink'), redup: v('--mut'), garis: v('--line'), latar: v('--bg') };
    };

    let w = warna();
    let seriAktif = 'semua';

    // Label garis bawah ditaruh di bawah titik, kecuali titiknya terlalu dekat sumbu X:
    // dipindah ke atas supaya tidak menabrak sumbu dan nama bulan.
    const posisiLabel = (atas) => (ctx) => {
        if (atas) return 'top';
        const y = ctx.chart.scales.y;
        const nilai = ctx.dataset.data[ctx.dataIndex];
        return (nilai - y.min) / ((y.max - y.min) || 1) < 0.18 ? 'top' : 'bottom';
    };

    const seri = (label, nilai, kunci, atas) => ({
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
        hidden: seriAktif !== 'semua' && seriAktif !== kunci,
        datalabels: { align: posisiLabel(atas), anchor: (ctx) => posisiLabel(atas)(ctx) === 'top' ? 'end' : 'start' },
    });

    const buatData = (d) => ({
        labels: d.label,
        datasets: [
            seri('Domestik', d.domestik, 'domestik', true),
            seri('Mancanegara', d.mancanegara, 'mancanegara', false),
        ],
    });

    const grafik = new Chart(canvas, {
        type: 'line',
        data: buatData(@js($grafik)),
        plugins: [ChartDataLabels],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 22, right: 12, bottom: 8, left: 4 } },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'start', onClick: null, labels: { color: w.teks, usePointStyle: true, pointStyle: 'line', boxWidth: 18, font: { family: 'Plus Jakarta Sans' } } },
                tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${angka(c.parsed.y)} orang` } },
                // Angka tiap titik selalu tampil; yang bertabrakan disembunyikan otomatis (tetap ada di tooltip & tabel).
                datalabels: { color: w.redup, offset: 4, clamp: true, display: 'auto', formatter: (n) => angka(n), font: { size: 10, weight: '600', family: 'Plus Jakarta Sans' } },
            },
            scales: {
                x: { grid: { display: false }, border: { color: w.garis }, ticks: { color: w.redup, padding: 8 } },
                y: { beginAtZero: true, grace: '15%', grid: { color: w.garis }, border: { display: false }, ticks: { color: w.redup, precision: 0, callback: (n) => angka(n) } },
            },
        },
    });

    // Filter tahun/destinasi berubah -> data baru dari server.
    $wire.on('grafik-kunjungan', ({ data }) => {
        grafik.data = buatData(data);
        grafik.update();
    });

    // Chip Semua / Domestik / Mancanegara.
    $wire.$el.querySelectorAll('.seri-pilih .chip').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            seriAktif = tombol.dataset.seri;
            $wire.$el.querySelectorAll('.seri-pilih .chip').forEach((t) => {
                const on = t === tombol;
                t.classList.toggle('on', on);
                t.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            grafik.data.datasets.forEach((ds, i) => {
                ds.hidden = seriAktif !== 'semua' && seriAktif !== (i === 0 ? 'domestik' : 'mancanegara');
            });
            grafik.update();
        });
    });

    // Tombol tema (◐) mengganti data-theme di <html>: warnakan ulang grafik.
    const pengamat = new MutationObserver(() => {
        w = warna();
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
    pengamat.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    document.addEventListener('livewire:navigating', () => { pengamat.disconnect(); grafik.destroy(); }, { once: true });
</script>
@endscript
