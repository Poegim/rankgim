<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-5">
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">📊 Top 15 vs Bottom 15 rating spread</p>
    <div id="chart-spread-trend" class="h-48"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark    = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#71717a' : '#71717a';
    const gridColor = isDark ? '#27272a' : '#e4e4e7';

    new ApexCharts(document.querySelector('#chart-spread-trend'), {
        chart: { type: 'line', height: 192, toolbar: { show: false }, fontFamily: 'DM Sans, inherit', background: 'transparent', zoom: { enabled: false } },
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
        colors: ['#22c55e', '#ef4444'],
        legend: { labels: { colors: textColor } },
        tooltip: { theme: isDark ? 'dark' : 'light' },
    }).render();
});
</script>