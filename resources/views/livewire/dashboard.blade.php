@use('Illuminate\Support\Str')
<div class="flex flex-col gap-8" x-data="{ showMore: false }">
    @php
        $raceColors = [
            'Terran'  => 'text-blue-400',
            'Zerg'    => 'text-purple-400',
            'Protoss' => 'text-yellow-400',
            'Random'  => 'text-orange-400',
            'Unknown' => 'text-zinc-400',
        ];
        $raceBorders = [
            'Terran'  => 'border-l-blue-500',
            'Zerg'    => 'border-l-purple-500',
            'Protoss' => 'border-l-yellow-500',
            'Random'  => 'border-l-orange-500',
            'Unknown' => 'border-l-zinc-500',
        ];
        $raceBg = [
            'Terran'  => 'bg-blue-500/8',
            'Zerg'    => 'bg-purple-500/8',
            'Protoss' => 'bg-yellow-500/8',
        ];
    @endphp

    {{-- Top 10 --}}
    <div>
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-zinc-800 dark:text-white">🏆 Top 10</h2>
            <a href="{{ route('rankings.index') }}" class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">View full rankings →</a>
        </div>

        {{-- Top 3 --}}
        <div class="flex flex-col gap-3 mb-4">
            @foreach($this->top10->take(3) as $index => $row)
            @php
                $winRatio = $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0;
                $change = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
                $medals = ['🥇', '🥈', '🥉'];
                $podiumGradients = [
                    'from-amber-500/10 via-amber-500/5 to-transparent',
                    'from-zinc-400/10 via-zinc-400/5 to-transparent',
                    'from-orange-700/10 via-orange-700/5 to-transparent',
                ];
            @endphp
            <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
               class="group relative flex items-center px-4 sm:px-6 py-3 sm:py-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-gradient-to-r {{ $podiumGradients[$index] }} hover:bg-zinc-800/40 transition-all duration-150"
               style="border-left: 4px solid {{ match($row->player->race) { 'Terran' => '#3b82f6', 'Zerg' => '#a855f7', 'Protoss' => '#eab308', 'Random' => '#f97316', default => '#52525b' } }};">
                {{-- Flag background --}}
                <div class="absolute inset-0 overflow-hidden rounded-xl">
                    <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                         class="absolute right-0 top-0 h-full w-auto opacity-[0.07] object-cover"
                         style="-webkit-mask-image: linear-gradient(to left, black 30%, transparent 100%); mask-image: linear-gradient(to left, black 30%, transparent 100%);">
                </div>

                <div class="relative z-10 flex items-center justify-between w-full gap-2">
                    {{-- Left: rank + medal + flag + name + race --}}
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <span class="font-mono text-sm sm:text-base text-zinc-400 font-bold shrink-0">{{ $index + 1 }}</span>
                        <span class="text-xl sm:text-2xl shrink-0">{{ $medals[$index] }}</span>
                        <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-7 sm:w-8 h-5 sm:h-6 rounded-sm shrink-0">
                        <span class="font-bold text-base sm:text-lg text-zinc-800 dark:text-white group-hover:underline truncate">{{ $row->player->name }}</span>
                        <span class="text-xs sm:text-sm shrink-0 hidden sm:inline {{ $raceColors[$row->player->race] ?? 'text-zinc-400' }}">{{ $row->player->race }}</span>
                    </div>

                    {{-- Right: rating + change + win% + W/L --}}
                    <div class="flex items-center shrink-0">
                        <span class="font-mono text-xl sm:text-2xl font-bold text-zinc-800 dark:text-white w-14 sm:w-16 text-right">{{ $row->rating }}</span>
                        <span class="hidden sm:inline-block font-mono text-sm font-medium w-14 text-right {{ $change !== null && $change != 0 ? ($change > 0 ? 'text-green-400' : 'text-red-400') : '' }}">
                            @if($change !== null && $change != 0)
                                {{ $change > 0 ? '▲' : '▼' }}{{ abs($change) }}
                            @endif
                        </span>
                        <span class="text-sm sm:text-base font-semibold w-11 sm:w-12 text-right {{ $winRatio >= 50 ? 'text-green-400' : 'text-red-400' }}">{{ $winRatio }}%</span>
                        <div class="hidden md:flex items-center gap-1 w-24 justify-end">
                            <span class="text-sm text-green-400">{{ $row->wins }}W</span>
                            <span class="text-sm text-zinc-600">/</span>
                            <span class="text-sm text-red-400">{{ $row->losses }}L</span>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- 4-10 compact list --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-200 dark:divide-zinc-700 overflow-hidden">
            @foreach($this->top10->slice(3) as $index => $row)
            @php
                $winRatio = $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0;
                $change = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
            @endphp
            <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
                class="flex items-center px-3 sm:px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group border-l-4 {{ $raceBorders[$row->player->race] ?? 'border-l-zinc-500' }}">
                <div class="flex items-center justify-between w-full gap-2">
                    {{-- Left: rank + flag + name + race --}}
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <span class="font-mono text-sm text-zinc-400 w-5 sm:w-6 text-right shrink-0">{{ $loop->iteration + 3 }}</span>
                        <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-6 sm:w-7 h-4 sm:h-5 rounded-sm shrink-0">
                        <span class="font-semibold text-sm sm:text-base text-zinc-800 dark:text-white group-hover:underline truncate">{{ $row->player->name }}</span>
                        <span class="text-xs shrink-0 hidden sm:inline {{ $raceColors[$row->player->race] ?? 'text-zinc-400' }}">{{ $row->player->race }}</span>
                    </div>

                    {{-- Right: rating + change + win% + W/L --}}
                    <div class="flex items-center shrink-0">
                        <span class="font-mono text-base sm:text-lg font-bold text-zinc-800 dark:text-white w-14 sm:w-16 text-right">{{ $row->rating }}</span>
                        <span class="hidden sm:inline-block font-mono text-xs font-medium w-14 text-right {{ $change !== null && $change != 0 ? ($change > 0 ? 'text-green-400' : 'text-red-400') : '' }}">
                            @if($change !== null && $change != 0)
                                {{ $change > 0 ? '▲' : '▼' }}{{ abs($change) }}
                            @endif
                        </span>
                        <span class="text-xs sm:text-sm font-semibold w-11 sm:w-12 text-right {{ $winRatio >= 50 ? 'text-green-400' : 'text-red-400' }}">{{ $winRatio }}%</span>
                        <div class="hidden md:flex items-center gap-1 w-20 justify-end">
                            <span class="text-xs text-green-400">{{ $row->wins }}W</span>
                            <span class="text-xs text-zinc-600">/</span>
                            <span class="text-xs text-red-400">{{ $row->losses }}L</span>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Race matchups --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
        <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400 mb-5">⚔️ Global race matchups</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $matchups = $this->raceMatchups->keyBy(fn($r) => $r->winner_race . '-' . $r->loser_race);
                $pairs = [['Terran','Zerg'], ['Terran','Protoss'], ['Zerg','Protoss']];
                $raceBgBar = ['Terran' => 'bg-blue-500', 'Zerg' => 'bg-purple-500', 'Protoss' => 'bg-yellow-500'];
            @endphp
            @foreach($pairs as [$r1, $r2])
                @php
                    $r1wins = $matchups->get($r1 . '-' . $r2)?->games ?? 0;
                    $r2wins = $matchups->get($r2 . '-' . $r1)?->games ?? 0;
                    $total  = $r1wins + $r2wins;
                    $r1ratio = $total > 0 ? round(($r1wins / $total) * 100) : 50;
                    $r2ratio = 100 - $r1ratio;
                @endphp
                <div class="rounded-xl bg-zinc-50 dark:bg-zinc-800/60 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold {{ $raceColors[$r1] }}">{{ $r1 }}</span>
                        <span class="text-xs text-zinc-500">{{ number_format($total) }} games</span>
                        <span class="text-sm font-bold {{ $raceColors[$r2] }}">{{ $r2 }}</span>
                    </div>
                    <div class="flex items-end justify-between mb-2">
                        <div>
                            <p class="font-mono text-2xl font-bold {{ $raceColors[$r1] }}">{{ $r1ratio }}%</p>
                            <p class="text-xs text-zinc-500">{{ number_format($r1wins) }}W</p>
                        </div>
                        <div class="text-right">
                            <p class="font-mono text-2xl font-bold {{ $raceColors[$r2] }}">{{ $r2ratio }}%</p>
                            <p class="text-xs text-zinc-500">{{ number_format($r2wins) }}W</p>
                        </div>
                    </div>
                    <div class="h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden flex">
                        <div class="h-full {{ $raceBgBar[$r1] }} rounded-l-full" style="width: {{ $r1ratio }}%"></div>
                        <div class="h-full {{ $raceBgBar[$r2] }} rounded-r-full" style="width: {{ $r2ratio }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Charts: Games & Active Players per Year --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
            <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400 mb-4">📊 Games per year</h2>
            <div id="chart-games-year" class="h-56"></div>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
            <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400 mb-4">👥 Active players per year</h2>
            <div id="chart-players-year" class="h-56"></div>
        </div>
    </div>

    {{-- Charts: Rating trends --}}
    {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
            <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400 mb-4">📈 Avg rating of top 10 over time</h2>
            <div id="chart-top10-avg" class="h-56"></div>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
            <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400 mb-4">📊 Rating spread <span class="text-xs font-normal">(15+ games)</span></h2>
            <div id="chart-rating-spread" class="h-56"></div>
        </div>
    </div> --}}

    {{-- Recent games + Highest peaks --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
            <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400 mb-4">🎮 Recent games</h2>
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach($this->recentGames as $entry)
                <div class="flex items-center gap-3 py-2.5">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm shrink-0">
                        <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}" class="hover:underline font-semibold text-green-400 truncate">{{ $entry->game->winner->name }}</a>
                    </div>
                    <span class="text-zinc-500 text-xs shrink-0">beat</span>
                    <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
                        <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}" class="hover:underline text-zinc-400 truncate">{{ $entry->game->loser->name }}</a>
                        <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm shrink-0">
                    </div>
                    <span class="text-xs text-zinc-500 w-20 text-right shrink-0">{{ \Carbon\Carbon::parse($entry->played_at)->format('M d') }}</span>
                </div>
                @endforeach
            </div>
            <div class="mt-3 text-center">
                <a href="{{ route('games.index') }}" class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">View all games →</a>
            </div>
        </div>

        <livewire:highest-peaks />
    </div>

    {{-- Show more button --}}
    <div x-cloak x-show="!showMore" class="flex justify-center my-2">
        <button
            wire:click="$set('showMore', true)"
            x-on:click="showMore = true"
            class="cursor-pointer group inline-flex items-center gap-3 px-6 py-2.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-semibold text-sm shadow-md hover:shadow-indigo-500/30 hover:shadow-lg transition-all duration-200"
        >
            <span class="text-base">✨</span>
            <span class="tracking-wide">Show more interesting stats</span>
            <svg class="w-4 h-4 opacity-70 group-hover:translate-y-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    {{-- Loading spinner --}}
    <div wire:loading wire:target="showMore" class="flex justify-center items-center py-12">
        <div class="flex flex-col items-center gap-3 text-zinc-400">
            <svg class="animate-spin w-8 h-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span class="text-sm">Loading stats...</span>
        </div>
    </div>

    <div wire:show="showMore" class="flex flex-col gap-6">

        {{-- Row 2: Risers + Fallers + Streaks --}}
        <div x-show="showMore" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="rounded-xl border-l-4 border-l-green-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-base font-bold text-green-500 mb-4">📈 Biggest risers <span class="text-xs text-zinc-400 font-normal">last month</span></p>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->biggestRisers as $row)
                    <div class="flex items-center gap-3 py-2.5">
                        <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm">
                        <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}" class="hover:underline font-semibold text-zinc-800 dark:text-white flex-1">{{ $row->name }}</a>
                        <span class="text-green-400 font-mono font-bold">+{{ $row->rating_change }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border-l-4 border-l-red-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-base font-bold text-red-500 mb-4">📉 Biggest fallers <span class="text-xs text-zinc-400 font-normal">last month</span></p>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->biggestFallers as $row)
                    <div class="flex items-center gap-3 py-2.5">
                        <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm">
                        <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}" class="hover:underline font-semibold text-zinc-800 dark:text-white flex-1">{{ $row->name }}</a>
                        <span class="text-red-400 font-mono font-bold">{{ $row->rating_change }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border-l-4 border-l-orange-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-base font-bold text-orange-500 mb-4">🔥 Hot streaks</p>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->longestStreaks as $row)
                    <div class="flex items-center gap-3 py-2.5">
                        <img src="{{ asset('images/country_flags/' . strtolower($row['player']->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm">
                        <a href="{{ route('players.show', ['id' => $row['player']->id, 'slug' => Str::slug($row['player']->name)]) }}" class="hover:underline font-semibold text-zinc-800 dark:text-white flex-1">{{ $row['player']->name }}</a>
                        <span class="text-orange-400 font-mono font-bold">{{ $row['streak'] }}W</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Row 3: Most active + Biggest upsets + Most dominant --}}
        <div x-show="showMore" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="rounded-xl border-l-4 border-l-blue-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-base font-bold text-blue-500 mb-4">⚡ Most active <span class="text-xs text-zinc-400 font-normal">last year</span></p>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->mostActives as $row)
                    <div class="flex items-center gap-3 py-2.5">
                        <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm">
                        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}" class="hover:underline font-semibold text-zinc-800 dark:text-white flex-1">{{ $row->player->name }}</a>
                        <span class="font-mono font-bold text-blue-400">{{ $row->games_count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border-l-4 border-l-purple-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-base font-bold text-purple-500 mb-4">💥 Biggest upsets <span class="text-xs text-zinc-400 font-normal">last year</span></p>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->biggestUpsets as $entry)
                    <div class="flex items-center gap-2 py-2.5">
                        <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
                        <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}" class="hover:underline font-semibold text-green-400 truncate">{{ $entry->game->winner->name }}</a>
                        <span class="text-zinc-500 text-xs shrink-0">beat</span>
                        <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}" class="hover:underline text-zinc-400 truncate">{{ $entry->game->loser->name }}</a>
                        <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border-l-4 border-l-yellow-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-base font-bold text-yellow-500 mb-4">👑 Most dominant <span class="text-xs text-zinc-400 font-normal">last year</span></p>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->mostDominant as $row)
                    <div class="flex items-center gap-3 py-2.5">
                        <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm">
                        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}" class="hover:underline font-semibold text-zinc-800 dark:text-white flex-1">{{ $row->player->name }}</a>
                        <span class="font-mono font-bold {{ round(($row->wins / $row->total) * 100) >= 50 ? 'text-green-400' : 'text-red-400' }}">
                            {{ round(($row->wins / $row->total) * 100) }}%
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Row 4: Top rivalries --}}
        <div x-show="showMore" x-cloak class="rounded-xl border-l-4 border-l-zinc-500 border border-zinc-200 dark:border-zinc-700 p-5">
            <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400 mb-5">⚔️ Top rivalries</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($this->topRivalries as $row)
                @php
                    $playerAWins = $row['player_a_wins'];
                    $playerBWins = $row['player_b_wins'];
                @endphp
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <img src="{{ asset('images/country_flags/' . strtolower($row['p1_country']) . '.svg') }}"
                                 class="w-6 h-4 rounded-sm shrink-0">
                            <div class="min-w-0">
                                <a href="{{ route('players.show', ['id' => $row['player_a_id'], 'slug' => Str::slug($row['p1_name'])]) }}"
                                   class="hover:underline font-semibold text-zinc-800 dark:text-white block truncate">{{ $row['p1_name'] }}</a>
                                <span class="text-xs {{ $raceColors[$row['p1_race']] ?? 'text-zinc-400' }}">{{ $row['p1_race'] }}</span>
                            </div>
                        </div>
                        <div class="text-center shrink-0 px-3">
                            <p class="font-mono font-bold text-lg">
                                <span class="{{ $playerAWins > $playerBWins ? 'text-green-400' : 'text-red-400' }}">{{ $playerAWins }}</span>
                                <span class="text-zinc-500 mx-1">-</span>
                                <span class="{{ $playerBWins > $playerAWins ? 'text-green-400' : 'text-red-400' }}">{{ $playerBWins }}</span>
                            </p>
                            <p class="text-xs text-zinc-500">{{ $row['games_count'] }} games</p>
                        </div>
                        <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
                            <div class="text-right min-w-0">
                                <a href="{{ route('players.show', ['id' => $row['player_b_id'], 'slug' => Str::slug($row['p2_name'])]) }}"
                                   class="hover:underline font-semibold text-zinc-800 dark:text-white block truncate">{{ $row['p2_name'] }}</a>
                                <span class="text-xs {{ $raceColors[$row['p2_race']] ?? 'text-zinc-400' }}">{{ $row['p2_race'] }}</span>
                            </div>
                            <img src="{{ asset('images/country_flags/' . strtolower($row['p2_country']) . '.svg') }}"
                                 class="w-6 h-4 rounded-sm shrink-0">
                        </div>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full"
                             style="width: {{ $row['games_count'] > 0 ? round(($playerAWins / $row['games_count']) * 100) : 50 }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#a1a1aa' : '#71717a';
    const gridColor = isDark ? '#27272a' : '#e4e4e7';

    const baseOptions = {
        chart: { toolbar: { show: false }, fontFamily: 'DM Sans, inherit' },
        dataLabels: { enabled: false },
        grid: { borderColor: gridColor },
        xaxis: { labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        tooltip: { theme: isDark ? 'dark' : 'light' },
    };

    new ApexCharts(document.querySelector('#chart-games-year'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'bar', height: 224 },
        series: [{ name: 'Games', data: @json($this->gamesPerYear->pluck('total')) }],
        xaxis: { ...baseOptions.xaxis, categories: @json($this->gamesPerYear->pluck('year')) },
        colors: ['#6366f1'],
    }).render();

    new ApexCharts(document.querySelector('#chart-players-year'), {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: 'bar', height: 224 },
        series: [{ name: 'Players', data: @json($this->activePlayersPerYear->pluck('total')) }],
        xaxis: { ...baseOptions.xaxis, categories: @json($this->activePlayersPerYear->pluck('year')) },
        colors: ['#8b5cf6'],
    }).render();



});
</script>

</div>