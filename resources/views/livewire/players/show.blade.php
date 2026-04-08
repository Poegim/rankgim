@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6">
    {{-- Player header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{-- Flag — links to rankings filtered by country --}}
            <a href="{{ route('rankings.index', ['filterCountryCode' => $this->player->country_code]) }}"
               title="View all {{ $this->player->country }} players"
               class="hover:opacity-80 transition-opacity">
                <img
                    src="{{ asset('images/country_flags/' . strtolower($this->player->country_code) . '.svg') }}"
                    alt="{{ $this->player->country }}"
                    class="w-10 h-7 rounded-sm"
                >
            </a>
            <div>
                <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">{{ $this->player->name }}</h1>
                {{-- Race — links to rankings filtered by race --}}
                <p class="text-zinc-500 dark:text-zinc-400">
                    <a href="{{ route('rankings.index', ['filterRace' => $this->player->race]) }}"
                       class="hover:underline transition-colors
                           {{ match($this->player->race) {
                               'Terran'  => 'text-blue-400 hover:text-blue-300',
                               'Zerg'    => 'text-purple-400 hover:text-purple-300',
                               'Protoss' => 'text-yellow-400 hover:text-yellow-300',
                               'Random'  => 'text-orange-400 hover:text-orange-300',
                               default   => 'text-zinc-400 hover:text-zinc-200',
                           } }}">{{ $this->player->race }}</a>
                    · 
                    <a href="{{ route('rankings.index', ['filterCountryCode' => $this->player->country_code]) }}"
                       class="hover:underline hover:text-zinc-200 transition-colors">{{ $this->player->country }}</a>
                </p>
            </div>
        </div>
            <flux:button
                variant="primary"
                icon="identification"
                x-on:click="$flux.modal('player-card').show()"
            >
                Player Card
            </flux:button>
        </div>

        {{-- Inactive / unranked status banner --}}
        @if($this->stats['too_few_games'])
        <div class="flex items-center gap-3 rounded-xl border border-zinc-700/60 bg-zinc-800/40 px-4 py-3 text-sm text-zinc-400">
            <span class="text-base">🎮</span>
            <span>
                This player has only <span class="font-semibold text-zinc-200">{{ $this->stats['games_played'] }} / 15</span> ranked games and does not appear in the ranking yet.
            </span>
        </div>
        @elseif($this->stats['is_inactive'])
        <div class="flex items-center gap-3 rounded-xl border border-amber-700/40 bg-amber-900/20 px-4 py-3 text-sm text-amber-400">
            <span class="text-base">💤</span>
            <span>
                Inactive — last game played on
                <span class="font-semibold text-amber-200">
                    {{ \Carbon\Carbon::parse($this->stats['last_played_at'])->format('d M Y') }}
                </span>.
                Not currently ranked.
            </span>
        </div>
        @endif

    {{-- Stats --}}
    @if($this->rating)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        {{-- Current Rank --}}
        @if($this->stats['current_rank'])
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4 group">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent pointer-events-none"></div>
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-1">Current rank</p>
            <p class="text-3xl font-black text-indigo-300 tabular-nums">#{{ $this->stats['current_rank'] }}</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">📍</span>
        </div>
        @endif

        {{-- Rating --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/8 to-transparent pointer-events-none"></div>
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-1">Rating</p>
            <p class="text-3xl font-black text-amber-300 tabular-nums">{{ $this->rating->rating }}</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">⚡</span>
        </div>

        {{-- Peak --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/8 to-transparent pointer-events-none"></div>
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-1">Peak</p>
            <p class="text-3xl font-black text-purple-300 tabular-nums">{{ $this->stats['peak_rating'] }}</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">🏆</span>
        </div>

        {{-- Best rank --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/8 to-transparent pointer-events-none"></div>
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-1">Best rank</p>
            <p class="text-3xl font-black text-yellow-300 tabular-nums">#{{ $this->stats['best_rank'] }}</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">👑</span>
        </div>

        {{-- Games --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-1">Games</p>
            <p class="text-3xl font-black text-white tabular-nums">{{ $this->rating->games_played }}</p>
            <div class="mt-2 flex gap-1 text-xs text-zinc-500">
                <span class="text-green-400">{{ $this->rating->wins }}W</span>
                <span>/</span>
                <span class="text-red-400">{{ $this->rating->losses }}L</span>
            </div>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">🎮</span>
        </div>

        {{-- Win% --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">
            <div class="absolute inset-0 bg-gradient-to-br {{ $this->stats['win_ratio'] >= 50 ? 'from-green-500/8' : 'from-red-500/8' }} to-transparent pointer-events-none"></div>
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-1">Win rate</p>
            <p class="text-3xl font-black tabular-nums {{ $this->stats['win_ratio'] >= 50 ? 'text-green-400' : 'text-red-400' }}">
                {{ $this->stats['win_ratio'] }}<span class="text-lg font-semibold">%</span>
            </p>
            {{-- Mini progress bar --}}
            <div class="mt-2 h-1 rounded-full bg-zinc-700 overflow-hidden">
                <div class="h-full rounded-full {{ $this->stats['win_ratio'] >= 50 ? 'bg-green-500' : 'bg-red-500' }}"
                     style="width: {{ $this->stats['win_ratio'] }}%"></div>
            </div>
        </div>

        {{-- Best streak --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-1">Best streak</p>
            <p class="text-3xl font-black text-white tabular-nums">{{ $this->stats['longest_win_streak'] }}</p>
            <p class="text-xs text-zinc-500 mt-1">wins in a row</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">🔥</span>
        </div>

        {{-- Current streak --}}
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">
            <div class="absolute inset-0 bg-gradient-to-br {{ $this->stats['current_streak'] >= 0 ? 'from-green-500/8' : 'from-red-500/8' }} to-transparent pointer-events-none"></div>
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-1">Streak</p>
            <p class="text-3xl font-black tabular-nums {{ $this->stats['current_streak'] > 0 ? 'text-green-400' : ($this->stats['current_streak'] < 0 ? 'text-red-400' : 'text-zinc-400') }}">
                {{ $this->stats['current_streak'] > 0 ? '+' : '' }}{{ $this->stats['current_streak'] }}
            </p>
            <p class="text-xs text-zinc-500 mt-1">{{ $this->stats['current_streak'] >= 0 ? 'win streak' : 'loss streak' }}</p>
        </div>

    </div>

    {{-- Race stats --}}
    <div class="grid grid-cols-3 gap-3">
        @foreach($this->raceStats as $stat)
        @php
            $raceHex = match($stat['race']) {
                'Terran'  => '#3b82f6',
                'Zerg'    => '#a855f7',
                'Protoss' => '#eab308',
                default   => '#71717a',
            };
            $raceText = match($stat['race']) {
                'Terran'  => 'text-blue-400',
                'Zerg'    => 'text-purple-400',
                'Protoss' => 'text-yellow-400',
                default   => 'text-zinc-400',
            };
        @endphp
        <div class="relative overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">

            {{-- Race accent line top --}}
            <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-xl"
                 style="background: {{ $raceHex }}"></div>

            <p class="text-xs font-semibold uppercase tracking-widest {{ $raceText }} mb-2">vs {{ $stat['race'] }}</p>

            <p class="text-3xl font-black tabular-nums {{ $stat['ratio'] >= 50 ? 'text-green-400' : 'text-red-400' }}">
                {{ $stat['ratio'] }}<span class="text-base font-semibold">%</span>
            </p>

            <p class="text-xs text-zinc-500 mt-2">
                <span class="text-green-400 font-medium">{{ $stat['wins'] }}W</span>
                <span class="mx-1">/</span>
                <span class="text-red-400 font-medium">{{ $stat['losses'] }}L</span>
            </p>
        </div>
        @endforeach
    </div>
    
    <livewire:players.achievements :playerId="$this->player->id" />

{{-- Head to Head --}}
<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-4">
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">Head to Head</p>

    <div class="flex flex-col gap-2">
        @foreach($this->headToHead as $h2h)
        @php
            $raceHex = match($h2h['opponent']->race) {
                'Terran'  => '#3b82f6',
                'Zerg'    => '#a855f7',
                'Protoss' => '#eab308',
                default   => '#71717a',
            };
            $raceText = match($h2h['opponent']->race) {
                'Terran'  => 'text-blue-400',
                'Zerg'    => 'text-purple-400',
                'Protoss' => 'text-yellow-400',
                default   => 'text-zinc-500',
            };
            $isWinning = $h2h['ratio'] >= 50;
        @endphp

        <a href="{{ route('players.show', ['id' => $h2h['opponent']->id, 'slug' => Str::slug($h2h['opponent']->name)]) }}"
           class="group block rounded-xl overflow-hidden border border-zinc-700/40 hover:border-zinc-600/60 transition-all duration-200 hover:scale-[1.01]">

            {{-- Win% fill bar as background --}}
            <div class="relative">
                <div class="absolute inset-0 {{ $isWinning ? 'bg-green-500/8' : 'bg-red-500/8' }} transition-all duration-300 group-hover:opacity-150"
                     style="width: {{ $h2h['ratio'] }}%"></div>

                <div class="relative flex items-center gap-4 px-4 py-3">

                    {{-- Flag + name --}}
                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                        <img
                            src="{{ asset('images/country_flags/' . strtolower($h2h['opponent']->country_code) . '.svg') }}"
                            alt="{{ $h2h['opponent']->country }}"
                            class="w-7 h-5 rounded-sm shrink-0 opacity-90"
                            title="{{ $h2h['opponent']->country }}"
                        >
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-zinc-200 group-hover:text-white truncate transition-colors">
                                {{ $h2h['opponent']->name }}
                            </p>
                            <p class="text-xs {{ $raceText }}">{{ $h2h['opponent']->race }}</p>
                        </div>
                    </div>

                    {{-- W / L --}}
                    <div class="flex items-center gap-1 text-sm font-mono shrink-0">
                        <span class="text-green-400 font-bold">{{ $h2h['wins'] }}</span>
                        <span class="text-zinc-600">–</span>
                        <span class="text-red-400 font-bold">{{ $h2h['losses'] }}</span>
                    </div>

                    {{-- Win% badge --}}
                    <div class="shrink-0 w-14 text-right">
                        <span class="text-lg font-black tabular-nums {{ $isWinning ? 'text-green-400' : 'text-red-400' }}">
                            {{ $h2h['ratio'] }}<span class="text-xs font-semibold">%</span>
                        </span>
                    </div>

                    {{-- Arrow --}}
                    <span class="text-zinc-700 group-hover:text-zinc-400 transition-colors text-xs">›</span>
                </div>

                {{-- Bottom progress line --}}
                <div class="h-px bg-zinc-700/60">
                    <div class="h-full {{ $isWinning ? 'bg-green-500/50' : 'bg-red-500/50' }} transition-all duration-500"
                         style="width: {{ $h2h['ratio'] }}%"></div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
    @else
    {{-- No rating yet --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 text-center">
        <p class="text-zinc-400 dark:text-zinc-500">No rating data yet — this player has no recalculated games.</p>
    </div>
    @endif

    {{-- Rank History Chart --}}
    @if($this->rankHistory->isNotEmpty())
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">📊 Ranking position history</p>

        <script>
            window._rankHistoryData = {!! json_encode(
                $this->rankHistory->map(fn($s) => [
                    'x'      => $s->snapshot_date,
                    'y'      => $s->rank,
                    'rating' => $s->rating,
                ])
            ) !!};
        </script>

        <div x-data="{
                init() {
                    new ApexCharts(this.$refs.chart, {
                        chart: {
                            type: 'line',
                            height: 300,
                            background: 'transparent',
                            toolbar: { show: false },
                            animations: { enabled: false },
                            zoom: {
        enabled: false  // disables scroll-to-zoom, restores page scroll
    },
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        },
                        series: [{
                            name: 'Rank',
                            data: window._rankHistoryData.map(d => ({
                                x: d.x ? new Date(d.x).getTime() : null,
                                y: d.y,
                                rating: d.rating,
                            }))
                        }],
                        colors: ['#a78bfa'],
                        xaxis: {
                            type: 'datetime',
                            labels: { style: { fontSize: '11px' } },
                        },
                        yaxis: {
                            reversed: true,
                            min: Math.max(1, Math.min(...window._rankHistoryData.map(d => d.y)) - 5),
                            max: Math.max(...window._rankHistoryData.map(d => d.y)) + 5,
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
                        markers: { size: 0 },
                        tooltip: {
                            custom({ seriesIndex, dataPointIndex, w }) {
                                const point = w.config.series[seriesIndex].data[dataPointIndex];
                                const color = w.globals.colors[seriesIndex];
                                return '<div style=\'padding:8px 12px;font-size:12px;\'>'
                                    + '<span style=\'color:' + color + ';font-weight:700;\'>Rank</span><br>'
                                    + 'Position: <b>#' + point.y + '</b><br>'
                                    + 'Rating: <b>' + point.rating + '</b>'
                                    + '</div>';
                            }
                        },
                        grid: {
                            borderColor: '#3f3f46',
                        },
                    }).render();
                }
            }">
            <div x-ref="chart"></div>
        </div>
    </div>
    @endif

    {{-- ELO Chart --}}
    @if($this->history->isNotEmpty())
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">ELO History</p>
        <div
            x-data="{
                init() {
                    new ApexCharts(this.$refs.chart, {
                        chart: {
                            type: 'line',
                            height: 300,
                            toolbar: { show: false },
                            background: 'transparent',
                            zoom: {
        enabled: false  // disables scroll-to-zoom, restores page scroll
    },
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        },
                        series: [{
                            name: 'Rating',
                            data: @js($this->history->pluck('rating_after'))
                        }],
                        xaxis: {
                            categories: @js($this->history->pluck('played_at')),
                            labels: { show: false },
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2,
                        },
                        tooltip: {
                            x: {
                                formatter: (val, { dataPointIndex }) => {
                                    const dates = @js($this->history->pluck('played_at'));
                                    return dates[dataPointIndex];
                                }
                            }
                        },
                        grid: {
                            borderColor: document.documentElement.classList.contains('dark') ? '#3f3f46' : '#e4e4e7',
                        },
                    }).render();
                }
            }"
        >
            <div x-ref="chart"></div>
        </div>
    </div>
    @endif

    {{-- Game history --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">Game History</p>
        <livewire:players.game-history :playerId="$this->player->id" />
    </div>

    {{-- Player Card Modal --}}
    <flux:modal name="player-card" class="w-96">
        <div class="relative overflow-hidden rounded-2xl text-white"
             style="background: linear-gradient(145deg, #18181b 0%, #1c1c22 60%, #1e1a2e 100%);">

            {{-- Subtle top glow --}}
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-24 rounded-full blur-3xl opacity-20"
                 style="background: {{ match($this->player->race) { 'Terran' => '#3b82f6', 'Zerg' => '#a855f7', 'Protoss' => '#eab308', 'Random' => '#f97316', default => '#6366f1' } }};"></div>

            {{-- Race accent bar --}}
            <div class="h-1 w-full"
                 style="background: linear-gradient(to right, {{ match($this->player->race) { 'Terran' => '#3b82f6', 'Zerg' => '#a855f7', 'Protoss' => '#eab308', 'Random' => '#f97316', default => '#6366f1' } }}, transparent);"></div>

            <div class="p-6 flex flex-col gap-5">

                {{-- Header --}}
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <img
                            src="{{ asset('images/country_flags/' . strtolower($this->player->country_code) . '.svg') }}"
                            class="w-10 h-7 rounded-sm shadow-lg"
                        >
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xl font-black tracking-tight truncate">{{ $this->player->name }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full border"
                                  style="color: {{ match($this->player->race) { 'Terran' => '#93c5fd', 'Zerg' => '#d8b4fe', 'Protoss' => '#fde047', 'Random' => '#fdba74', default => '#a1a1aa' } }}; border-color: {{ match($this->player->race) { 'Terran' => '#3b82f620', 'Zerg' => '#a855f720', 'Protoss' => '#eab30820', 'Random' => '#f9731620', default => '#52525b' } }}; background: {{ match($this->player->race) { 'Terran' => '#3b82f610', 'Zerg' => '#a855f710', 'Protoss' => '#eab30810', 'Random' => '#f9731610', default => '#3f3f46' } }}">
                                {{ $this->player->race }}
                            </span>
                            <span class="text-xs text-zinc-500">{{ $this->player->country }}</span>
                        </div>
                    </div>
                    @if($this->stats['current_rank'])
                    <div class="text-right shrink-0">
                        <p class="text-2xl font-black text-indigo-300">#{{ $this->stats['current_rank'] }}</p>
                        <p class="text-xs text-zinc-500">rank</p>
                    </div>
                    @endif
                </div>



                @if($this->rating)

                {{-- Main stats row --}}
                <div class="grid grid-cols-3 gap-2">
                    <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/40 p-3 text-center">
                        <p class="text-xl font-black text-amber-300">{{ $this->rating->rating }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">Rating</p>
                    </div>
                    <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/40 p-3 text-center">
                        <p class="text-xl font-black text-purple-300">{{ $this->stats['peak_rating'] }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">Peak</p>
                    </div>
                    <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/40 p-3 text-center">
                        <p class="text-xl font-black text-yellow-300">#{{ $this->stats['best_rank'] }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">Best rank</p>
                    </div>
                </div>

                {{-- W/L bar --}}
                <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/40 p-3">
                    <div class="flex justify-between text-xs text-zinc-400 mb-2">
                        <span class="font-semibold text-green-400">{{ $this->rating->wins }}W</span>
                        <span class="font-semibold
                            {{ $this->stats['win_ratio'] >= 50 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $this->stats['win_ratio'] }}%
                        </span>
                        <span class="font-semibold text-red-400">{{ $this->rating->losses }}L</span>
                    </div>
                    <div class="h-2 rounded-full bg-zinc-700 overflow-hidden flex">
                        <div class="h-full bg-gradient-to-r from-green-500 to-green-400 transition-all"
                             style="width: {{ $this->stats['win_ratio'] }}%"></div>
                        <div class="h-full bg-gradient-to-r from-red-500 to-red-400 flex-1"></div>
                    </div>
                    <div class="flex justify-between text-xs text-zinc-600 mt-1.5">
                        <span>{{ $this->rating->games_played }} games total</span>
                        <span>streak: <span class="{{ $this->stats['current_streak'] >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ $this->stats['current_streak'] > 0 ? '+' : '' }}{{ $this->stats['current_streak'] }}</span></span>
                    </div>
                </div>

                {{-- Race matchups --}}
                @if($this->raceStats->isNotEmpty())
                <div>
                    <p class="text-xs text-zinc-600 uppercase tracking-widest mb-2">vs race</p>
                    <div class="flex flex-col gap-1.5">
                        @foreach($this->raceStats as $stat)
                        @php
                            $raceHex = match($stat['race']) {
                                'Terran'  => '#3b82f6',
                                'Zerg'    => '#a855f7',
                                'Protoss' => '#eab308',
                                default   => '#71717a',
                            };
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="text-xs w-14 font-medium" style="color: {{ $raceHex }}">{{ $stat['race'] }}</span>
                            <div class="flex-1 h-1.5 rounded-full bg-zinc-700 overflow-hidden">
                                <div class="h-full rounded-full transition-all"
                                     style="width: {{ $stat['ratio'] }}%; background: {{ $raceHex }}; opacity: 0.7;"></div>
                            </div>
                            <span class="text-xs font-bold w-9 text-right {{ $stat['ratio'] >= 50 ? 'text-green-400' : 'text-red-400' }}">{{ $stat['ratio'] }}%</span>
                            <span class="text-xs text-zinc-600 w-16 text-right">{{ $stat['wins'] }}W/{{ $stat['losses'] }}L</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @else
                <p class="text-zinc-500 text-sm text-center py-4">No rating data yet.</p>
                @endif

            </div>
        </div>
    </flux:modal>
</div>