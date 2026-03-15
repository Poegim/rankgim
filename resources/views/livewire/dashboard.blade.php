@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6" x-data="{ showMore: false }">
    {{-- Top 10 full width --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🏆 Top 10</p>
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-8">#</flux:table.column>
                <flux:table.column>Player</flux:table.column>
                <flux:table.column>Rating</flux:table.column>
                <flux:table.column class="hidden md:table-cell">Change</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">Games</flux:table.column>
                <flux:table.column>Win%</flux:table.column>
                <flux:table.column class="hidden md:table-cell">W / L</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->top10 as $index => $row)
                @php
                    $winRatio = $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0;
                    $change = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
                @endphp
                <flux:table.row :key="$row->id" class="[&>td]:py-2">
                    <flux:table.cell>
                        <span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                            <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}" class="hover:underline font-medium text-zinc-800 dark:text-white">{{ $row->player->name }}</a>
                            <span class="hidden sm:inline text-xs {{ match($row->player->race) { 'Terran' => 'text-blue-500', 'Zerg' => 'text-purple-500', 'Protoss' => 'text-yellow-500', default => 'text-zinc-400' } }}">{{ $row->player->race }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="font-bold text-zinc-800 dark:text-white">{{ $row->rating }}</span>
                    </flux:table.cell>
                    <flux:table.cell class="hidden md:table-cell">
                        @if($change !== null)
                            <span class="text-xs font-medium {{ $change > 0 ? 'text-green-500' : ($change < 0 ? 'text-red-500' : 'text-zinc-400') }}">
                                {{ $change > 0 ? '+' : '' }}{{ $change }}
                            </span>
                        @else
                            <span class="text-xs text-zinc-400">—</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="hidden lg:table-cell">
                        <span class="text-zinc-500 dark:text-zinc-400 text-sm">{{ $row->games_played }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <span class="text-sm {{ $winRatio >= 50 ? 'text-green-500' : 'text-red-500' }}">{{ $winRatio }}%</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="hidden md:table-cell">
                        <span class="text-xs text-green-500">{{ $row->wins }}W</span>
                        <span class="text-xs text-zinc-400 mx-0.5">/</span>
                        <span class="text-xs text-red-500">{{ $row->losses }}L</span>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
        <div class="mt-3 text-center">
            <a href="{{ route('rankings.index') }}" class="text-sm text-zinc-500 dark:text-zinc-400 hover:underline">View full rankings →</a>
        </div>
    </div>

    {{-- Race matchups full width --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">⚔️ Global race matchups</p>
        <div class="grid grid-cols-3 gap-3">
            @php
                $races = ['Terran', 'Zerg', 'Protoss'];
                $raceColors = [
                    'Terran'  => 'text-blue-500',
                    'Zerg'    => 'text-purple-500',
                    'Protoss' => 'text-yellow-500',
                ];
                $matchups = $this->raceMatchups->keyBy(fn($r) => $r->winner_race . '-' . $r->loser_race);
                $pairs = [['Terran','Zerg'], ['Terran','Protoss'], ['Zerg','Protoss']];
            @endphp
            @foreach($pairs as [$r1, $r2])
                @php
                    $r1wins = $matchups->get($r1 . '-' . $r2)?->games ?? 0;
                    $r2wins = $matchups->get($r2 . '-' . $r1)?->games ?? 0;
                    $total  = $r1wins + $r2wins;
                    $r1ratio = $total > 0 ? round(($r1wins / $total) * 100) : 50;
                    $r2ratio = 100 - $r1ratio;
                @endphp
                <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 p-3 text-center">
                    <p class="text-xs font-medium mb-2">
                        <span class="{{ $raceColors[$r1] }}">{{ substr($r1, 0, 1) }}</span>
                        <span class="text-zinc-400 mx-1">vs</span>
                        <span class="{{ $raceColors[$r2] }}">{{ substr($r2, 0, 1) }}</span>
                    </p>
                    <div class="flex justify-around items-center">
                        <div>
                            <p class="font-bold text-lg {{ $raceColors[$r1] }}">{{ $r1ratio }}%</p>
                            <p class="text-xs text-zinc-400">{{ $r1wins }}W</p>
                        </div>
                        <div class="text-zinc-300 dark:text-zinc-600 text-xs">—</div>
                        <div>
                            <p class="font-bold text-lg {{ $raceColors[$r2] }}">{{ $r2ratio }}%</p>
                            <p class="text-xs text-zinc-400">{{ $r2wins }}W</p>
                        </div>
                    </div>
                    <div class="mt-2 h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-500" style="width: {{ $r1ratio }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Games & Active Players per Year --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">📊 Games per year</p>
            <div id="chart-games-year" class="h-56"></div>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">👥 Active players per year</p>
            <div id="chart-players-year" class="h-56"></div>
        </div>
    </div>

    {{-- Recent games + Highest peaks --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🎮 Recent games</p>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Winner</flux:table.column>
                    <flux:table.column>Loser</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->recentGames as $entry)
                    <flux:table.row :key="$entry->id" class="[&>td]:py-2">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}" class="hover:underline font-medium text-green-500">{{ $entry->game->winner->name }}</a>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}" class="hover:underline text-zinc-500 dark:text-zinc-400">{{ $entry->game->loser->name }}</a>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell><span class="text-xs text-zinc-400">{{ \Carbon\Carbon::parse($entry->played_at)->format('Y-m-d') }}</span></flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
            <div class="mt-3 text-center">
                <a href="{{ route('games.index') }}" class="text-sm text-zinc-500 dark:text-zinc-400 hover:underline">View all games →</a>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🔝 Highest peaks</p>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8">#</flux:table.column>
                    <flux:table.column>Player</flux:table.column>
                    <flux:table.column>Peak</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->highestPeaks as $index => $row)
                    <flux:table.row :key="$row->player_id" class="[&>td]:py-2">
                        <flux:table.cell>
                            <span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}" class="hover:underline font-medium text-zinc-800 dark:text-white">{{ $row->player->name }}</a>
                                <span class="text-xs {{ match($row->player->race) { 'Terran' => 'text-blue-500', 'Zerg' => 'text-purple-500', 'Protoss' => 'text-yellow-500', default => 'text-zinc-400' } }}">{{ $row->player->race }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="font-bold text-yellow-500">{{ $row->peak_rating }}</span>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

    </div>

    {{-- Hidden stats --}}

    {{-- Button --}}
    <div x-cloak x-show="!showMore" class="flex justify-center my-4">
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
                <p class="text-sm font-medium text-green-500 mb-4">📈 Biggest risers <span class="text-xs text-zinc-400">last month</span></p>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Player</flux:table.column>
                        <flux:table.column>+/-</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->biggestRisers as $row)
                        <flux:table.row :key="$row->id" class="[&>td]:py-2">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                    <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}" class="hover:underline font-medium text-zinc-800 dark:text-white">{{ $row->name }}</a>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell><span class="text-green-500 font-bold">+{{ $row->rating_change }}</span></flux:table.cell>
                        </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="rounded-xl border-l-4 border-l-red-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-sm font-medium text-red-500 mb-4">📉 Biggest fallers <span class="text-xs text-zinc-400">last month</span></p>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Player</flux:table.column>
                        <flux:table.column>+/-</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->biggestFallers as $row)
                        <flux:table.row :key="$row->id" class="[&>td]:py-2">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                    <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}" class="hover:underline font-medium text-zinc-800 dark:text-white">{{ $row->name }}</a>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell><span class="text-red-500 font-bold">{{ $row->rating_change }}</span></flux:table.cell>
                        </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="rounded-xl border-l-4 border-l-orange-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-sm font-medium text-orange-500 mb-4">🔥 Hot streaks</p>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Player</flux:table.column>
                        <flux:table.column>Streak</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->longestStreaks as $row)
                        <flux:table.row :key="$row['player']->id" class="[&>td]:py-2">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($row['player']->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                    <a href="{{ route('players.show', ['id' => $row['player']->id, 'slug' => Str::slug($row['player']->name)]) }}" class="hover:underline font-medium text-zinc-800 dark:text-white">{{ $row['player']->name }}</a>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell><span class="text-orange-500 font-bold">{{ $row['streak'] }}W</span></flux:table.cell>
                        </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

        </div>



        {{-- Country stats --}}
        <div x-show="showMore" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🌍 Country stats <span class="text-xs">(active players with 15+ games · all-time stats)</span></p>            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8">#</flux:table.column>
                    <flux:table.column>Country</flux:table.column>
                    <flux:table.column>Players</flux:table.column>
                    <flux:table.column>Avg rating</flux:table.column>
                    <flux:table.column>Win%</flux:table.column>
                    <flux:table.column>W</flux:table.column>
                    <flux:table.column>L</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->topCountries as $index => $row)
                    <flux:table.row :key="$row->country_code" class="[&>td]:py-2">
                        <flux:table.cell>
                            <span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <a href="{{ route('rankings.index', ['filterCountryCode' => $row->country_code]) }}"
                               class="flex items-center gap-2 hover:underline">
                                <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                                     class="w-6 h-4 rounded-sm">
                                <span class="font-medium text-zinc-800 dark:text-white">{{ $row->country }}</span>
                            </a>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="text-zinc-500 dark:text-zinc-400">{{ $row->player_count }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="font-bold text-zinc-800 dark:text-white">{{ $row->avg_rating }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="{{ $row->win_ratio >= 50 ? 'text-green-500' : 'text-red-500' }} font-bold">
                                {{ $row->win_ratio }}%
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="text-green-500">{{ $row->total_wins }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="text-red-500">{{ $row->total_losses }}</span>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- Row 3: Most active + Biggest upsets + Most dominant --}}
        <div x-show="showMore" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="rounded-xl border-l-4 border-l-blue-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-sm font-medium text-blue-500 mb-4">⚡ Most active <span class="text-xs text-zinc-400">last year</span></p>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Player</flux:table.column>
                        <flux:table.column>Games</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->mostActives as $row)
                        <flux:table.row :key="$row->player_id" class="[&>td]:py-2">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                    <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}" class="hover:underline font-medium text-zinc-800 dark:text-white">{{ $row->player->name }}</a>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell><span class="font-bold text-blue-500">{{ $row->games_count }}</span></flux:table.cell>
                        </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="rounded-xl border-l-4 border-l-purple-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-sm font-medium text-purple-500 mb-4">💥 Biggest upsets <span class="text-xs text-zinc-400">last year</span></p>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Winner</flux:table.column>
                        <flux:table.column>Loser</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->biggestUpsets as $entry)
                        <flux:table.row :key="$entry->id" class="[&>td]:py-2">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                    <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}" class="hover:underline font-medium text-green-500">{{ $entry->game->winner->name }}</a>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                    <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}" class="hover:underline text-zinc-500 dark:text-zinc-400">{{ $entry->game->loser->name }}</a>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="rounded-xl border-l-4 border-l-yellow-500 border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-sm font-medium text-yellow-500 mb-4">👑 Most dominant <span class="text-xs text-zinc-400">last year</span></p>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Player</flux:table.column>
                        <flux:table.column>Win%</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->mostDominant as $row)
                        <flux:table.row :key="$row->player_id" class="[&>td]:py-2">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                    <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}" class="hover:underline font-medium text-zinc-800 dark:text-white">{{ $row->player->name }}</a>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-bold {{ round(($row->wins / $row->total) * 100) >= 50 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ round(($row->wins / $row->total) * 100) }}%
                                </span>
                            </flux:table.cell>
                        </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

        </div>

        {{-- Row 4: Top rivalries --}}
        <div x-show="showMore" x-cloak class="rounded-xl border-l-4 border-l-zinc-500 border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">⚔️ Top rivalries</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($this->topRivalries as $row)
                @php
                    $playerAWins = $row['player_a_wins'];
                    $playerBWins = $row['player_b_wins'];
                    $raceColors = [
                        'Terran'  => 'text-blue-500',
                        'Zerg'    => 'text-purple-500',
                        'Protoss' => 'text-yellow-500',
                    ];
                @endphp
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                    <div class="flex items-center justify-between gap-2">
                        {{-- Player A --}}
                        <div class="flex items-center gap-2 flex-1">
                            <img src="{{ asset('images/country_flags/' . strtolower($row['p1_country']) . '.svg') }}"
                                 class="w-5 h-3 rounded-sm shrink-0">
                            <div>
                                <a href="{{ route('players.show', ['id' => $row['player_a_id'], 'slug' => Str::slug($row['p1_name'])]) }}"
                                   class="hover:underline font-medium text-zinc-800 dark:text-white text-sm">{{ $row['p1_name'] }}</a>
                                <p class="text-xs {{ $raceColors[$row['p1_race']] ?? 'text-zinc-400' }}">{{ $row['p1_race'] }}</p>
                            </div>
                        </div>
                        {{-- Score --}}
                        <div class="text-center px-2 shrink-0">
                            <p class="font-bold text-zinc-800 dark:text-white">
                                <span class="{{ $playerAWins > $playerBWins ? 'text-green-500' : 'text-red-500' }}">{{ $playerAWins }}</span>
                                <span class="text-zinc-400 mx-1">-</span>
                                <span class="{{ $playerBWins > $playerAWins ? 'text-green-500' : 'text-red-500' }}">{{ $playerBWins }}</span>
                            </p>
                            <p class="text-xs text-zinc-400">{{ $row['games_count'] }} games</p>
                        </div>
                        {{-- Player B --}}
                        <div class="flex items-center gap-2 flex-1 justify-end">
                            <div class="text-right">
                                <a href="{{ route('players.show', ['id' => $row['player_b_id'], 'slug' => Str::slug($row['p2_name'])]) }}"
                                   class="hover:underline font-medium text-zinc-800 dark:text-white text-sm">{{ $row['p2_name'] }}</a>
                                <p class="text-xs {{ $raceColors[$row['p2_race']] ?? 'text-zinc-400' }}">{{ $row['p2_race'] }}</p>
                            </div>
                            <img src="{{ asset('images/country_flags/' . strtolower($row['p2_country']) . '.svg') }}"
                                 class="w-5 h-3 rounded-sm shrink-0">
                        </div>
                    </div>
                    {{-- Progress bar --}}
                    <div class="mt-2 h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full"
                             style="width: {{ $row['games_count'] > 0 ? round(($playerAWins / $row['games_count']) * 100) : 50 }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Country vs country accordion --}}
        <div  x-show="showMore" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 mt-4">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">🌍 Country vs Country</p>
                <span class="text-xs text-zinc-400">all games · top 10 countries by active player count</span>
            </div>
            <div class="flex flex-col gap-2">
                @php
                    $countries = $this->topCountries;
                    $matchups = $this->countryMatchups->keyBy(fn($r) => $r->winner_country . '-' . $r->loser_country);
                @endphp
                @foreach($countries as $index => $country)
                @php
                    $totalWins   = 0;
                    $totalGames  = 0;
                    foreach ($countries as $opponent) {
                        if ($country->country_code === $opponent->country_code) continue;
                        $w = $matchups->get($country->country_code . '-' . $opponent->country_code)?->games ?? 0;
                        $l = $matchups->get($opponent->country_code . '-' . $country->country_code)?->games ?? 0;
                        $totalWins  += $w;
                        $totalGames += $w + $l;
                    }
                    $overallRatio = $totalGames > 0 ? round(($totalWins / $totalGames) * 100) : null;
                @endphp
                <div x-data="{ open: false }" class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <button
                        x-on:click="open = !open"
                        class="w-full flex items-center justify-between p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-zinc-400 font-mono w-4">{{ $index + 1 }}</span>
                            <img src="{{ asset('images/country_flags/' . strtolower($country->country_code) . '.svg') }}"
                                 class="w-6 h-4 rounded-sm">
                            <span class="font-medium text-zinc-800 dark:text-white">{{ $country->country }}</span>
                            <span class="text-xs text-zinc-400">{{ $country->player_count }} players</span>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($overallRatio !== null)
                                <span class="text-sm font-bold {{ $overallRatio >= 50 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $overallRatio }}% vs others
                                </span>
                            @endif
                            <span class="text-xs text-zinc-400">{{ $totalGames }} games</span>
                            <svg x-bind:class="open ? 'rotate-180' : ''" class="w-4 h-4 text-zinc-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div x-show="open" class="px-3 pb-3">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2">
                            @foreach($countries as $opponent)
                                @if($country->country_code !== $opponent->country_code)
                                @php
                                    $wins   = $matchups->get($country->country_code . '-' . $opponent->country_code)?->games ?? 0;
                                    $losses = $matchups->get($opponent->country_code . '-' . $country->country_code)?->games ?? 0;
                                    $total  = $wins + $losses;
                                    $ratio  = $total > 0 ? round(($wins / $total) * 100) : null;
                                @endphp
                                <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-zinc-800 px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ asset('images/country_flags/' . strtolower($opponent->country_code) . '.svg') }}"
                                             class="w-5 h-3 rounded-sm">
                                        <div>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $opponent->country }}</p>
                                            <p class="text-xs text-zinc-400">{{ $wins }}W / {{ $losses }}L</p>
                                        </div>
                                    </div>
                                    @if($ratio !== null)
                                        <span class="font-bold text-sm {{ $ratio >= 50 ? 'text-green-500' : 'text-red-500' }}">{{ $ratio }}%</span>
                                    @else
                                        <span class="text-zinc-400 text-sm">—</span>
                                    @endif
                                </div>
                                @endif
                            @endforeach
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
        chart: { toolbar: { show: false }, fontFamily: 'inherit' },
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