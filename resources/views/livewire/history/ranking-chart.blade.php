<div class="flex flex-col gap-4">
    <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Ranking history</h2>

    {{-- Pass data via script tag to avoid Alpine expression size limits --}}
    <script>
        window._rankingChartSeries = {!! json_encode($this->chartSeries) !!};
    </script>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 bg-zinc-50 dark:bg-zinc-800/50"
         x-data="{
             init() {
                 const series = window._rankingChartSeries;
                 const raceColors = {
                     Terran:  '#3b82f6',
                     Zerg:    '#a855f7',
                     Protoss: '#eab308',
                     Random:  '#f97316',
                 };

                 new ApexCharts(this.$refs.chart, {
                     chart: {
                         type: 'line',
                         height: 380,
                         background: 'transparent',
                         toolbar: { show: false },
                         animations: { enabled: false },
                     },
                     theme: {
                         mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                     },
                     series: series.map(s => ({
                         name: s.name,
                         data: s.data.map(d => ({ x: new Date(d.x).getTime(), y: d.y, rating: d.rating }))
                     })),
                     colors: series.map(s => raceColors[s.race] ?? '#6366f1'),
                     xaxis: {
                         type: 'datetime',
                         labels: { style: { fontSize: '11px' } },
                     },
                     yaxis: {
                         reversed: true,
                         min: 1,
                         forceNiceScale: false,
                         labels: {
                             formatter: v => '#' + Math.round(v),
                             style: { fontSize: '11px' },
                         },
                     },
                     stroke: {
                         width: 2,
                         curve: 'stepline',
                     },
                     markers: {
                         size: 0,
                     },
                     tooltip: {
                         custom({ seriesIndex, dataPointIndex, w }) {
                             const point = w.config.series[seriesIndex].data[dataPointIndex];
                             const name  = w.config.series[seriesIndex].name;
                             const color = w.globals.colors[seriesIndex];
                             return '<div style=\'padding:8px 12px;font-size:12px;\'>'
                                 + '<span style=\'color:' + color + ';font-weight:700;\'>' + name + '</span><br>'
                                 + 'Rank: <b>#' + point.y + '</b><br>'
                                 + 'Rating: <b>' + point.rating + '</b>'
                                 + '</div>';
                         }
                     },
                     grid: {
                         borderColor: '#3f3f46',
                     },
                     legend: {
                         position: 'top',
                         fontSize: '12px',
                     },
                 }).render();
             }
         }">
        <div x-ref="chart"></div>
    </div>
</div>