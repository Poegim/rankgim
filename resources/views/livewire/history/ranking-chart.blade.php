<div class="flex flex-col gap-4">
    <h2 class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
        Ranking history
    </h2>

    {{-- Pass data via script tag to avoid Alpine expression size limits --}}
    <script>
        window._rankingChartSeries = {!! json_encode($this->chartSeries) !!};

        // CSS var values resolved at runtime for ApexCharts (can't read CSS vars directly)
        window._rankingChartColors = (function () {
            const style = getComputedStyle(document.documentElement);
            return {
                terran:  style.getPropertyValue('--color-race-terran').trim(),
                zerg:    style.getPropertyValue('--color-race-zerg').trim(),
                protoss: style.getPropertyValue('--color-race-protoss').trim(),
                random:  style.getPropertyValue('--color-race-random').trim(),
            };
        })();
    </script>

    <div class="rounded-xl border p-4
        border-travertine-300 bg-travertine-50
        dark:border-zinc-700 dark:bg-zinc-800/50"
         x-data="{
             init() {
                 const isDark  = document.documentElement.classList.contains('dark');
                 const series  = window._rankingChartSeries;
                 const raceColors = window._rankingChartColors;

                 // Axis/grid colors per app convention (rule #10)
                 const textColor = isDark ? '#71717a' : '#78716c';
                 const gridColor = isDark ? '#3f3f46' : '#d4cab0';

                 new ApexCharts(this.$refs.chart, {
                     chart: {
                         type: 'line',
                         height: 380,
                         background: 'transparent',
                         toolbar: { show: false },
                         animations: { enabled: false },
                         // Disable scroll-to-zoom so page scroll works normally
                         zoom: { enabled: false },
                     },
                     theme: {
                         mode: isDark ? 'dark' : 'light',
                     },
                     series: series.map(s => ({
                         name: s.name,
                         data: s.data.map(d => ({ x: new Date(d.x).getTime(), y: d.y, rating: d.rating }))
                     })),
                     colors: series.map(s => raceColors[s.race?.toLowerCase()] ?? '#6366f1'),
                     xaxis: {
                         type: 'datetime',
                         labels: { style: { colors: textColor, fontSize: '11px' } },
                     },
                     yaxis: {
                         reversed: true,
                         min: 1,
                         forceNiceScale: false,
                         labels: {
                             formatter: v => '#' + Math.round(v),
                             style: { colors: textColor, fontSize: '11px' },
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
                         theme: isDark ? 'dark' : 'light',
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
                         borderColor: gridColor,
                         strokeDashArray: 4,
                     },
                     legend: {
                         position: 'top',
                         fontSize: '12px',
                         labels: { colors: textColor },
                     },
                 }).render();
             }
         }">
        <div x-ref="chart"></div>
    </div>
</div>