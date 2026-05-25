@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6">

    {{-- ════════════════════════════════════════════════════════════════════
         Player header — name + race/country + Player Card button
         ════════════════════════════════════════════════════════════════════ --}}
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
                <h1 class="text-2xl font-bold text-travertine-900 dark:text-white">{{ $this->player->name }}</h1>
                {{-- Race — links to rankings filtered by race --}}
                <p class="text-travertine-600 dark:text-zinc-400">
                    <a href="{{ route('rankings.index', ['filterRace' => $this->player->race]) }}"
                       class="hover:underline transition-colors
                           {{ match($this->player->race) {
                               'Terran'  => 'text-blue-700 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300',
                               'Zerg'    => 'text-purple-700 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300',
                               'Protoss' => 'text-yellow-700 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300',
                               'Random'  => 'text-orange-700 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300',
                               default   => 'text-travertine-600 hover:text-travertine-900 dark:text-zinc-400 dark:hover:text-zinc-200',
                           } }}">{{ $this->player->race }}</a>
                    ·
                    <a href="{{ route('rankings.index', ['filterCountryCode' => $this->player->country_code]) }}"
                       class="hover:underline transition-colors
                              text-travertine-700 hover:text-travertine-900
                              dark:text-zinc-400 dark:hover:text-zinc-200">{{ $this->player->country }}</a>
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

    {{-- ════════════════════════════════════════════════════════════════════
         Reactions & Comments bar
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex items-center gap-3">
        <livewire:reactions.reaction-bar :model="$this->player" :key="'reactions-player-'.$this->player->id" />
        <button
            wire:click="$dispatch('open-comments', { modelType: 'App\\Models\\Player', modelId: {{ $this->player->id }} })"
            class="flex items-center gap-1.5 text-xs transition-colors
                   text-travertine-600 hover:text-oxblood
                   dark:text-zinc-500 dark:hover:text-zinc-300"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            @php $commentCount = $this->player->comments()->whereNull('parent_id')->count() @endphp
            {{ $commentCount }} {{ Str::plural('comment', $commentCount) }}
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         Inactive / unranked status banner
         ════════════════════════════════════════════════════════════════════ --}}
    @if($this->stats['too_few_games'])
        <div class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm
                    border border-travertine-300 bg-travertine-50 text-travertine-700
                    dark:border-zinc-700/60 dark:bg-zinc-800/40 dark:text-zinc-400">
            <span class="text-base">🎮</span>
            <span>
                This player has only
                <span class="font-semibold text-travertine-900 dark:text-zinc-200">
                    {{ $this->stats['games_played'] }} / 15
                </span>
                ranked games and does not appear in the ranking yet.
            </span>
        </div>
    @elseif($this->stats['is_inactive'])
        <div class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm
                    border border-amber-300 bg-amber-50 text-amber-800
                    dark:border-amber-700/40 dark:bg-amber-900/20 dark:text-amber-400">
            <span class="text-base">💤</span>
            <span>
                Inactive — last game played on
                <span class="font-semibold text-amber-900 dark:text-amber-200">
                    {{ \Carbon\Carbon::parse($this->stats['last_played_at'])->format('d M Y') }}
                </span>.
                Not currently ranked.
            </span>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════
         Stats grid — 8 cards, each with subtle race/state-tinted gradient
         ════════════════════════════════════════════════════════════════════ --}}
    @if($this->rating)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        {{-- Current Rank --}}
        @if($this->stats['current_rank'])
        <div class="relative overflow-hidden rounded-xl p-4 group
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">
            <div class="absolute inset-0 bg-gradient-to-br from-oxblood/10 to-transparent pointer-events-none dark:from-indigo-500/10"></div>
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-1
                      text-oxblood dark:text-zinc-500">Current rank</p>
            <p class="text-3xl font-black tabular-nums
                      text-oxblood dark:text-indigo-300">#{{ $this->stats['current_rank'] }}</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">📍</span>
        </div>
        @endif

        {{-- Rating --}}
        <div class="relative overflow-hidden rounded-xl p-4
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/8 to-transparent pointer-events-none"></div>
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-1
                      text-oxblood dark:text-zinc-500">Rating</p>
            <p class="text-3xl font-black tabular-nums
                      text-amber-700 dark:text-amber-300">{{ $this->rating->rating }}</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">⚡</span>
        </div>

        {{-- Peak --}}
        <div class="relative overflow-hidden rounded-xl p-4
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/8 to-transparent pointer-events-none"></div>
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-1
                      text-oxblood dark:text-zinc-500">Peak</p>
            <p class="text-3xl font-black tabular-nums
                      text-purple-700 dark:text-purple-300">{{ $this->stats['peak_rating'] }}</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">🏆</span>
        </div>

        {{-- Best rank --}}
        <div class="relative overflow-hidden rounded-xl p-4
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/8 to-transparent pointer-events-none"></div>
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-1
                      text-oxblood dark:text-zinc-500">Best rank</p>
            <p class="text-3xl font-black tabular-nums
                      text-yellow-700 dark:text-yellow-300">#{{ $this->stats['best_rank'] }}</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">👑</span>
        </div>

        {{-- Games --}}
        <div class="relative overflow-hidden rounded-xl p-4
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-1
                      text-oxblood dark:text-zinc-500">Games</p>
            <p class="text-3xl font-black tabular-nums
                      text-travertine-900 dark:text-white">{{ $this->rating->games_played }}</p>
            <div class="mt-2 flex gap-1 text-xs
                        text-travertine-500 dark:text-zinc-500">
                <span class="text-emerald-700 dark:text-green-400">{{ $this->rating->wins }}W</span>
                <span>/</span>
                <span class="text-red-700 dark:text-red-400">{{ $this->rating->losses }}L</span>
            </div>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">🎮</span>
        </div>

        {{-- Win% --}}
        <div class="relative overflow-hidden rounded-xl p-4
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">
            <div @class([
                'absolute inset-0 bg-gradient-to-br to-transparent pointer-events-none',
                'from-emerald-500/8' => $this->stats['win_ratio'] >= 50,
                'from-red-500/8' => $this->stats['win_ratio'] < 50,
            ])></div>
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-1
                      text-oxblood dark:text-zinc-500">Win rate</p>
            <p @class([
                'text-3xl font-black tabular-nums',
                'text-emerald-700 dark:text-green-400' => $this->stats['win_ratio'] >= 50,
                'text-red-700 dark:text-red-400' => $this->stats['win_ratio'] < 50,
            ])>
                {{ $this->stats['win_ratio'] }}<span class="text-lg font-semibold">%</span>
            </p>
            {{-- Mini progress bar --}}
            <div class="mt-2 h-1 rounded-full overflow-hidden
                        bg-travertine-300 dark:bg-zinc-700">
                <div @class([
                        'h-full rounded-full',
                        'bg-emerald-600 dark:bg-green-500' => $this->stats['win_ratio'] >= 50,
                        'bg-red-600 dark:bg-red-500' => $this->stats['win_ratio'] < 50,
                     ])
                     style="width: {{ $this->stats['win_ratio'] }}%"></div>
            </div>
        </div>

        {{-- Best streak --}}
        <div class="relative overflow-hidden rounded-xl p-4
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-1
                      text-oxblood dark:text-zinc-500">Best streak</p>
            <p class="text-3xl font-black tabular-nums
                      text-travertine-900 dark:text-white">{{ $this->stats['longest_win_streak'] }}</p>
            <p class="text-xs mt-1
                      text-travertine-500 dark:text-zinc-500">wins in a row</p>
            <span class="absolute bottom-3 right-3 text-xl opacity-20">🔥</span>
        </div>

        {{-- Current streak --}}
        <div class="relative overflow-hidden rounded-xl p-4
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">
            <div @class([
                'absolute inset-0 bg-gradient-to-br to-transparent pointer-events-none',
                'from-emerald-500/8' => $this->stats['current_streak'] >= 0,
                'from-red-500/8' => $this->stats['current_streak'] < 0,
            ])></div>
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-1
                      text-oxblood dark:text-zinc-500">Streak</p>
            <p @class([
                'text-3xl font-black tabular-nums',
                'text-emerald-700 dark:text-green-400' => $this->stats['current_streak'] > 0,
                'text-red-700 dark:text-red-400' => $this->stats['current_streak'] < 0,
                'text-travertine-600 dark:text-zinc-400' => $this->stats['current_streak'] === 0,
            ])>
                {{ $this->stats['current_streak'] > 0 ? '+' : '' }}{{ $this->stats['current_streak'] }}
            </p>
            <p class="text-xs mt-1
                      text-travertine-500 dark:text-zinc-500">
                {{ $this->stats['current_streak'] >= 0 ? 'win streak' : 'loss streak' }}
            </p>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         Race matchup stats — vs Terran/Zerg/Protoss
         Race colors use CSS vars from app.css (auto theme-adjust).
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-3 gap-3">
        @foreach($this->raceStats as $stat)
        @php
            // Use CSS vars for consistency with rest of app.
            // Auto-adjusts: darker on cream, lighter on near-black.
            $raceVar = match($stat['race']) {
                'Terran'  => 'var(--color-race-terran)',
                'Zerg'    => 'var(--color-race-zerg)',
                'Protoss' => 'var(--color-race-protoss)',
                default   => 'var(--color-race-unknown)',
            };
            $raceVarSoft = match($stat['race']) {
                'Terran'  => 'var(--color-race-terran-soft)',
                'Zerg'    => 'var(--color-race-zerg-soft)',
                'Protoss' => 'var(--color-race-protoss-soft)',
                default   => 'var(--color-race-unknown-soft)',
            };
        @endphp
        <div class="relative overflow-hidden rounded-xl p-4
                    border border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/60 dark:bg-zinc-800/40">

            {{-- Race accent line top --}}
            <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-xl"
                 style="background: {{ $raceVar }};"></div>

            <p class="text-xs font-semibold uppercase tracking-widest mb-2"
               style="color: {{ $raceVarSoft }};">vs {{ $stat['race'] }}</p>

            <p @class([
                'text-3xl font-black tabular-nums',
                'text-emerald-700 dark:text-green-400' => $stat['ratio'] >= 50,
                'text-red-700 dark:text-red-400' => $stat['ratio'] < 50,
            ])>
                {{ $stat['ratio'] }}<span class="text-base font-semibold">%</span>
            </p>

            <p class="text-xs mt-2 text-travertine-500 dark:text-zinc-500">
                <span class="font-medium text-emerald-700 dark:text-green-400">{{ $stat['wins'] }}W</span>
                <span class="mx-1">/</span>
                <span class="font-medium text-red-700 dark:text-red-400">{{ $stat['losses'] }}L</span>
            </p>
        </div>
        @endforeach
    </div>

    <livewire:players.achievements :playerId="$this->player->id" />

    {{-- ════════════════════════════════════════════════════════════════════
         Head to Head — opponents with mutual game history
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="rounded-xl p-4
                border border-travertine-300 bg-travertine-50
                dark:border-zinc-700/60 dark:bg-zinc-800/40">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-4
                  text-oxblood dark:text-zinc-500">Head to Head</p>

        <div class="flex flex-col gap-2">
            @foreach($this->headToHead as $h2h)
            @php
                $raceVarSoft = match($h2h['opponent']->race) {
                    'Terran'  => 'var(--color-race-terran-soft)',
                    'Zerg'    => 'var(--color-race-zerg-soft)',
                    'Protoss' => 'var(--color-race-protoss-soft)',
                    default   => 'var(--color-race-unknown-soft)',
                };
                $isWinning = $h2h['ratio'] >= 50;
            @endphp

            <a href="{{ route('players.show', ['id' => $h2h['opponent']->id, 'slug' => Str::slug($h2h['opponent']->name)]) }}"
               class="group block rounded-xl overflow-hidden transition-all duration-200 hover:scale-[1.01]
                      border border-travertine-300 hover:border-travertine-400
                      dark:border-zinc-700/40 dark:hover:border-zinc-600/60">

                {{-- Win% fill bar as background --}}
                <div class="relative">
                    <div @class([
                            'absolute inset-0 transition-all duration-300 group-hover:opacity-150',
                            'bg-emerald-500/15 dark:bg-green-500/8' => $isWinning,
                            'bg-red-500/15 dark:bg-red-500/8' => ! $isWinning,
                         ])
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
                                <p class="text-sm font-bold truncate transition-colors
                                          text-travertine-800 group-hover:text-oxblood
                                          dark:text-zinc-200 dark:group-hover:text-white">
                                    {{ $h2h['opponent']->name }}
                                </p>
                                <p class="text-xs" style="color: {{ $raceVarSoft }};">{{ $h2h['opponent']->race }}</p>
                            </div>
                        </div>

                        {{-- W / L --}}
                        <div class="flex items-center gap-1 text-sm font-mono shrink-0">
                            <span class="font-bold text-emerald-700 dark:text-green-400">{{ $h2h['wins'] }}</span>
                            <span class="text-travertine-400 dark:text-zinc-600">–</span>
                            <span class="font-bold text-red-700 dark:text-red-400">{{ $h2h['losses'] }}</span>
                        </div>

                        {{-- Win% badge --}}
                        <div class="shrink-0 w-14 text-right">
                            <span @class([
                                'text-lg font-black tabular-nums',
                                'text-emerald-700 dark:text-green-400' => $isWinning,
                                'text-red-700 dark:text-red-400' => ! $isWinning,
                            ])>
                                {{ $h2h['ratio'] }}<span class="text-xs font-semibold">%</span>
                            </span>
                        </div>

                        {{-- Arrow --}}
                        <span class="text-xs transition-colors
                                     text-travertine-400 group-hover:text-travertine-600
                                     dark:text-zinc-700 dark:group-hover:text-zinc-400">›</span>
                    </div>

                    {{-- Bottom progress line --}}
                    <div class="h-px bg-travertine-300/60 dark:bg-zinc-700/60">
                        <div @class([
                                'h-full transition-all duration-500',
                                'bg-emerald-600/70 dark:bg-green-500/50' => $isWinning,
                                'bg-red-600/70 dark:bg-red-500/50' => ! $isWinning,
                             ])
                             style="width: {{ $h2h['ratio'] }}%"></div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    @else
    {{-- No rating yet --}}
    <div class="rounded-xl p-6 text-center
                border border-travertine-300 bg-travertine-50
                dark:border-zinc-700 dark:bg-zinc-800/40">
        <p class="text-travertine-500 dark:text-zinc-500">No rating data yet — this player has no recalculated games.</p>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════
         Rank History Chart (ApexCharts).
         Grid border + chart line color must change per theme; we read them
         at init time via classList check + CSS vars.
         ════════════════════════════════════════════════════════════════════ --}}
    @if($this->rankHistory->isNotEmpty())
    <div class="rounded-xl p-4
                border border-travertine-300 bg-travertine-50
                dark:border-zinc-700 dark:bg-zinc-800/40">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-4
                  text-oxblood dark:text-zinc-500">📊 Ranking position history</p>

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
                    // Detect theme at render time. Apex theme.mode controls text color.
                    const isDark = document.documentElement.classList.contains('dark');
                    const gridColor = isDark ? '#3f3f46' : '#d4cab0';
                    // Purple-600 reads OK on both cream and near-black.
                    const lineColor = '#7c3aed';

                    new ApexCharts(this.$refs.chart, {
                        chart: {
                            type: 'line',
                            height: 300,
                            background: 'transparent',
                            toolbar: { show: false },
                            animations: { enabled: false },
                            zoom: { enabled: false },
                        },
                        theme: { mode: isDark ? 'dark' : 'light' },
                        series: [{
                            name: 'Rank',
                            data: window._rankHistoryData.map(d => ({
                                x: d.x ? new Date(d.x).getTime() : null,
                                y: d.y,
                                rating: d.rating,
                            }))
                        }],
                        colors: [lineColor],
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
                        stroke: { width: 2, curve: 'stepline' },
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
                        grid: { borderColor: gridColor },
                    }).render();
                }
            }">
            <div x-ref="chart"></div>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════
         ELO History Chart (ApexCharts)
         ════════════════════════════════════════════════════════════════════ --}}
    @if($this->history->isNotEmpty())
    <div class="rounded-xl p-4
                border border-travertine-300 bg-travertine-50
                dark:border-zinc-700 dark:bg-zinc-800/40">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-4
                  text-oxblood dark:text-zinc-500">ELO History</p>
        <div
            x-data="{
                init() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const gridColor = isDark ? '#3f3f46' : '#d4cab0';

                    new ApexCharts(this.$refs.chart, {
                        chart: {
                            type: 'line',
                            height: 300,
                            toolbar: { show: false },
                            background: 'transparent',
                            zoom: { enabled: false },
                        },
                        theme: { mode: isDark ? 'dark' : 'light' },
                        series: [{
                            name: 'Rating',
                            data: @js($this->history->pluck('rating_after'))
                        }],
                        xaxis: {
                            categories: @js($this->history->pluck('played_at')),
                            labels: { show: false },
                        },
                        stroke: { curve: 'smooth', width: 2 },
                        tooltip: {
                            x: {
                                formatter: (val, { dataPointIndex }) => {
                                    const dates = @js($this->history->pluck('played_at'));
                                    return dates[dataPointIndex];
                                }
                            }
                        },
                        grid: { borderColor: gridColor },
                    }).render();
                }
            }"
        >
            <div x-ref="chart"></div>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════
         Game history widget
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="rounded-xl p-4
                border border-travertine-300 bg-travertine-50
                dark:border-zinc-700 dark:bg-zinc-800/40">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] mb-4
                  text-oxblood dark:text-zinc-500">Game History</p>
        <livewire:players.game-history :playerId="$this->player->id" />
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         Player Card Modal — INTENTIONALLY kept dark in BOTH themes.
         This is a display artifact (trading-card aesthetic), like
         achievement-card. Dramatic dark gradient is the design intent.
         ════════════════════════════════════════════════════════════════════ --}}
    <flux:modal name="player-card" class="w-96">
        <div class="relative overflow-hidden rounded-2xl text-white"
             style="background: linear-gradient(145deg, #18181b 0%, #1c1c22 60%, #1e1a2e 100%);">

            {{-- Subtle top glow keyed to race --}}
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