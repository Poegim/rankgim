<div class="rounded-xl border p-3 sm:p-5
    border-travertine-300 bg-travertine-50
    dark:border-zinc-700/60 dark:bg-zinc-800/40">
    <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
        🏅 Active players
    </p>
    <div id="chart-ranked-players" class="h-48"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark    = document.documentElement.classList.contains('dark');
    // Corrected: distinct light/dark axis label colors per app convention (rule #10)
    const textColor = isDark ? '#71717a' : '#78716c'; // zinc-500 / stone-500 warm
    const gridColor = isDark ? '#3f3f46' : '#d4cab0'; // zinc-700 / travertine-300

    const data   = @json($this->data);
    const dates  = data.map(r => r.date.substring(0, 7));
    const counts = data.map(r => r.count);

    new ApexCharts(document.querySelector('#chart-ranked-players'), {
        chart: {
            type: 'bar',
            height: 192,
            toolbar: { show: false },
            fontFamily: 'DM Sans, inherit',
            background: 'transparent',
            zoom: { enabled: false },
        },
        plotOptions: {
            bar: { borderRadius: 3, columnWidth: '70%' },
        },
        dataLabels: { enabled: false },
        series: [{ name: 'Ranked players', data: counts }],
        xaxis: {
            categories: dates,
            labels: { style: { colors: textColor } },
        },
        yaxis: {
            labels: {
                style: { colors: textColor },
                // Always show whole numbers — player count is always an integer
                formatter: (val) => Math.round(val),
            },
            min: 0,
        },
        grid: { borderColor: gridColor, strokeDashArray: 4 },
        colors: ['#f59e0b'],
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: { formatter: (val) => val + ' players' },
        },
    }).render();
});
</script>