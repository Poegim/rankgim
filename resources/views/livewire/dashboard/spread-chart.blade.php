<div class="rounded-xl border p-3 sm:p-5
    border-travertine-300 bg-travertine-50
    dark:border-zinc-700/60 dark:bg-zinc-800/40">
    <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
        📊 Top 15 vs Bottom 15 rating spread
    </p>
    <div id="chart-spread-trend" class="h-48"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark    = document.documentElement.classList.contains('dark');
    // Corrected: distinct light/dark values per app convention (rule #10)
    const textColor = isDark ? '#71717a' : '#78716c'; // zinc-500 / stone-500 warm
    const gridColor = isDark ? '#3f3f46' : '#d4cab0'; // zinc-700 / travertine-300

    new ApexCharts(document.querySelector('#chart-spread-trend'), {
        chart: {
            type: 'line',
            height: 192,
            toolbar: { show: false },
            fontFamily: 'DM Sans, inherit',
            background: 'transparent',
            zoom: { enabled: false },
        },
        stroke: { curve: 'smooth', width: [2, 2] },
        dataLabels: { enabled: false },
        series: [
            { name: 'Top 15 avg', data: @json($this->data->pluck('top_avg')) },
            { name: 'Bot 15 avg', data: @json($this->data->pluck('bot_avg')) },
        ],
        xaxis: {
            categories: @json($this->data->pluck('date')),
            labels: {
                style: { colors: textColor },
                formatter: (val) => val ? val.substring(0, 7) : val,
            },
        },
        yaxis: { labels: { style: { colors: textColor } } },
        grid: { borderColor: gridColor, strokeDashArray: 4 },
        // Semantic green/red — brand colors, stay fixed across themes (rule #5)
        colors: ['#22c55e', '#ef4444'],
        legend: { labels: { colors: textColor } },
        tooltip: { theme: isDark ? 'dark' : 'light' },
    }).render();
});
</script>