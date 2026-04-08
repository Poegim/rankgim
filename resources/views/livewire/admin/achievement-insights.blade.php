<div class="flex flex-col gap-8 p-6 max-w-7xl mx-auto">

    <div>
        <h1 class="text-2xl font-bold text-white">Achievement Insights</h1>
        <p class="text-sm text-zinc-500 mt-1">Data to help design meaningful achievements.</p>
    </div>

    {{-- Top summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            ['Total Players', $data['totalPlayers']],
            ['Total Games', number_format($data['totalGames'])],
            ['Draw Rate', $data['drawStats']->total_games > 0 ? round($data['drawStats']->total_draws / $data['drawStats']->total_games * 100, 2) . '%' : '—'],
            ['Glass Cannon Candidates', $data['glassCannonCount']],
        ] as [$label, $value])
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-4">
            <p class="text-xs text-zinc-500 uppercase tracking-wider">{{ $label }}</p>
            <p class="text-2xl font-bold font-mono text-white mt-1">{{ $value }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Games played distribution --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Games Played Distribution</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Bucket</th>
                    <th class="text-right pb-2">Players</th>
                    <th class="text-right pb-2">%</th>
                </tr></thead>
                <tbody>
                @foreach($data['gamesDistribution'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $row->bucket }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $row->players }}</td>
                    <td class="py-1.5 text-right text-zinc-400 font-mono">{{ $data['totalPlayers'] > 0 ? round($row->players / $data['totalPlayers'] * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Rating milestones — how many ever reached X --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Players Who Ever Reached Rating X</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Rating</th>
                    <th class="text-right pb-2">Players</th>
                    <th class="text-right pb-2">%</th>
                </tr></thead>
                <tbody>
                @foreach($data['ratingMilestones'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $row['rating'] }}+</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $row['players'] }}</td>
                    <td class="py-1.5 text-right text-zinc-400 font-mono">{{ $data['totalPlayers'] > 0 ? round($row['players'] / $data['totalPlayers'] * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Current rating distribution --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Current Rating Distribution</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Rating Range</th>
                    <th class="text-right pb-2">Players</th>
                    <th class="text-right pb-2">%</th>
                </tr></thead>
                <tbody>
                @foreach($data['ratingDistribution'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $row->bucket }}–{{ $row->bucket + 99 }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $row->players }}</td>
                    <td class="py-1.5 text-right text-zinc-400 font-mono">{{ $data['totalPlayers'] > 0 ? round($row->players / $data['totalPlayers'] * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Win streak distribution --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Longest Win Streak Distribution (top 30)</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Streak</th>
                    <th class="text-right pb-2">Players</th>
                </tr></thead>
                <tbody>
                @foreach($data['streakDistribution'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $row->streak }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $row->players }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Loss streak distribution --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Longest Loss Streak Distribution (top 20)</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Streak</th>
                    <th class="text-right pb-2">Players</th>
                </tr></thead>
                <tbody>
                @foreach($data['lossStreakDistribution'] as $streak => $players)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $streak }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $players }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Active months distribution --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Active Months per Player (total, not consecutive)</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Months</th>
                    <th class="text-right pb-2">Players</th>
                    <th class="text-right pb-2">%</th>
                </tr></thead>
                <tbody>
                @foreach($data['activeMonthsDistribution'] as $bucket => $count)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $bucket }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $count }}</td>
                    <td class="py-1.5 text-right text-zinc-400 font-mono">{{ $data['totalPlayers'] > 0 ? round($count / $data['totalPlayers'] * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Rivalry depth --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Games vs Single Opponent (pairs)</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Games Together</th>
                    <th class="text-right pb-2">Pairs</th>
                </tr></thead>
                <tbody>
                @foreach($data['rivalryDepth'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $row->bucket }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $row->pairs }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Opponent count distribution --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Unique Opponents per Player</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Opponents</th>
                    <th class="text-right pb-2">Players</th>
                </tr></thead>
                <tbody>
                @foreach($data['opponentCounts'] as $bucket => $count)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $bucket }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $count }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Race distribution --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Race Distribution</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Race</th>
                    <th class="text-right pb-2">Players</th>
                    <th class="text-right pb-2">%</th>
                </tr></thead>
                <tbody>
                @foreach($data['raceDistribution'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300">{{ $row->race }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $row->players }}</td>
                    <td class="py-1.5 text-right text-zinc-400 font-mono">{{ $data['totalPlayers'] > 0 ? round($row->players / $data['totalPlayers'] * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Race matchups --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Race Matchup Win Counts</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Winner</th>
                    <th class="text-left pb-2">Loser</th>
                    <th class="text-right pb-2">Games</th>
                </tr></thead>
                <tbody>
                @foreach($data['raceMatchups'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300">{{ $row->winner_race }}</td>
                    <td class="py-1.5 text-zinc-300">{{ $row->loser_race }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $row->games }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Upset stats --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Upset Games (winner rated lower by X)</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Rating Diff</th>
                    <th class="text-right pb-2">Games</th>
                </tr></thead>
                <tbody>
                @foreach([
                    ['200+', $data['upsetStats']->upsets_200],
                    ['300+', $data['upsetStats']->upsets_300],
                    ['400+', $data['upsetStats']->upsets_400],
                    ['500+', $data['upsetStats']->upsets_500],
                ] as [$label, $count])
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $label }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ number_format($count) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Games per year --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Games per Year</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Year</th>
                    <th class="text-right pb-2">Games</th>
                </tr></thead>
                <tbody>
                @foreach($data['gamesPerYear'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300 font-mono">{{ $row->year }}</td>
                    <td class="py-1.5 text-right text-white font-mono">{{ number_format($row->games) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Top countries --}}
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5">
            <h2 class="text-sm font-semibold text-zinc-300 mb-4">Players by Country (top 15)</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-zinc-500 border-b border-zinc-700">
                    <th class="text-left pb-2">Country</th>
                    <th class="text-right pb-2">Players</th>
                </tr></thead>
                <tbody>
                @foreach($data['playersByCountry'] as $row)
                <tr class="border-b border-zinc-800">
                    <td class="py-1.5 text-zinc-300">
                        <img src="{{ asset('flags/' . strtolower($row->country_code) . '.svg') }}"
                             class="inline w-5 h-3.5 rounded-sm mr-1.5 align-middle">
                        {{ $row->country }}
                    </td>
                    <td class="py-1.5 text-right text-white font-mono">{{ $row->players }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
           
        </div>

        {{-- Plain list for copy --}}
        <div class="mt-6 p-4 bg-zinc-900/50 rounded-lg border border-zinc-700/30 lg:col-span-1">
            <h3 class="text-xs font-semibold text-zinc-400 mb-3 uppercase tracking-wider">Raw Data</h3>
            <div class="space-y-1 font-mono text-xs text-zinc-300">
                @foreach($data['monthlyActivity'] as $row)
                <div>{{ $row->month }} - {{ $row->games }} games</div>
                @endforeach
            </div>
        </div>

    {{-- Monthly activity --}}
    <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/50 p-5 lg:col-span-2">
        <h2 class="text-sm font-semibold text-zinc-300 mb-4">Games per Month (last 24 months)</h2>
        
        {{-- ApexCharts --}}
        <div id="monthlyActivityChart"></div>
        

    </div>

<script>
    (function() {
        // Prepare data
        const monthlyData = @json($data['monthlyActivity']);
        const months = monthlyData.map(item => item.month);
        const games = monthlyData.map(item => parseInt(item.games));
        
        // Chart options
        const options = {
            series: [{
                name: 'Games',
                data: games
            }],
            chart: {
                type: 'bar',
                height: 350,
                parentHeightOffset: 0,
                toolbar: {
                    show: true
                }
            },
            colors: ['#6366f1'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '75%',
                    borderRadius: 4,
                    borderRadiusApplication: 'end'
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: months,
                labels: {
                    style: {
                        colors: '#71717a',
                        fontSize: '11px'
                    },
                    rotateAlways: true
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#71717a',
                        fontSize: '11px'
                    }
                }
            },
            grid: {
                borderColor: '#27272a',
                strokeDashArray: 3
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function(value) {
                        return value + ' games';
                    }
                }
            }
        };
        
        // Render chart
        setTimeout(function() {
            const chart = new ApexCharts(document.querySelector('#monthlyActivityChart'), options);
            chart.render();
        }, 100);
    })();
    </script>

    </div>
    
</div>