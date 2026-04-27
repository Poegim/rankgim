<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-3 sm:p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">📊 Games per year</p>
        <div id="chart-games-year" class="h-48"></div>
    </div>
    <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-3 sm:p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">👥 Active players per year</p>
        <div id="chart-players-year" class="h-48"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark     = document.documentElement.classList.contains('dark');
    const textColor  = isDark ? '#71717a' : '#71717a';
    const gridColor  = isDark ? '#27272a' : '#e4e4e7';

    const base = {
        chart: { toolbar: { show: false }, fontFamily: 'DM Sans, inherit', background: 'transparent', zoom: { enabled: false } },
        dataLabels: { enabled: false },
        grid: { borderColor: gridColor, strokeDashArray: 4 },
        xaxis: { labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        tooltip: { theme: isDark ? 'dark' : 'light' },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
    };

    new ApexCharts(document.querySelector('#chart-games-year'), {
        ...base,
        chart: { ...base.chart, type: 'bar', height: 192 },
        series: [{ name: 'Games', data: @json($this->gamesPerYear->pluck('total')) }],
        xaxis: { ...base.xaxis, categories: @json($this->gamesPerYear->pluck('year')) },
        colors: ['#6366f1'],
    }).render();

    new ApexCharts(document.querySelector('#chart-players-year'), {
        ...base,
        chart: { ...base.chart, type: 'bar', height: 192 },
        series: [{ name: 'Players', data: @json($this->activePlayersPerYear->pluck('total')) }],
        xaxis: { ...base.xaxis, categories: @json($this->activePlayersPerYear->pluck('year')) },
        colors: ['#8b5cf6'],
    }).render();
});
</script>