@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6">
    {{-- Row 1: Top 10 + Recent games --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🏆 Top 10</p>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8">#</flux:table.column>
                    <flux:table.column>Player</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>Win%</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->top10 as $index => $row)
                    <flux:table.row :key="$row->id" class="[&>td]:py-2">
                        <flux:table.cell>
                            <span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}" class="hover:underline font-medium text-zinc-800 dark:text-white">{{ $row->player->name }}</a>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell><span class="font-bold text-zinc-800 dark:text-white">{{ $row->rating }}</span></flux:table.cell>
                        <flux:table.cell>
                            <span class="{{ $row->games_played > 0 && round(($row->wins / $row->games_played) * 100) >= 50 ? 'text-green-500' : 'text-red-500' }}">
                                {{ $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0 }}%
                            </span>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

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
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}" class="hover:underline font-medium text-green-500">{{ $entry->game->winner->name }}</a>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}" class="hover:underline text-zinc-500 dark:text-zinc-400">{{ $entry->game->loser->name }}</a>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell><span class="text-xs text-zinc-400">{{ \Carbon\Carbon::parse($entry->played_at)->format('Y-m-d') }}</span></flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

    </div>

    {{-- Race matchups --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">⚔️ Global race matchups</p>
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
            @php
                $races = ['Terran', 'Zerg', 'Protoss'];
                $raceColors = [
                    'Terran'  => 'text-blue-500',
                    'Zerg'    => 'text-purple-500',
                    'Protoss' => 'text-yellow-500',
                ];
                $matchups = $this->raceMatchups->keyBy(fn($r) => $r->winner_race . '-' . $r->loser_race);
            @endphp
            @foreach($races as $r1)
                @foreach($races as $r2)
                    @if($r1 !== $r2)
                    @php
                        $wins   = $matchups->get($r1 . '-' . $r2)?->games ?? 0;
                        $losses = $matchups->get($r2 . '-' . $r1)?->games ?? 0;
                        $total  = $wins + $losses;
                        $ratio  = $total > 0 ? round(($wins / $total) * 100) : 0;
                    @endphp
                    <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 p-3 text-center">
                        <p class="text-xs font-medium mb-1">
                            <span class="{{ $raceColors[$r1] }}">{{ substr($r1, 0, 1) }}</span>
                            <span class="text-zinc-400 mx-1">vs</span>
                            <span class="{{ $raceColors[$r2] }}">{{ substr($r2, 0, 1) }}</span>
                        </p>
                        <p class="font-bold text-lg {{ $ratio >= 50 ? 'text-green-500' : 'text-red-500' }}">{{ $ratio }}%</p>
                        <p class="text-xs text-zinc-400">{{ $wins }}W / {{ $losses }}L</p>
                    </div>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Row 2: Risers + Fallers + Streaks --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

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
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
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
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
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
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($row['player']->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
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
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🌍 Country stats <span class="text-xs">(min. 5 players with 15+ games)</span></p>
        <flux:table>
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
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('storage/images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                                 class="w-6 h-4 rounded-sm">
                            <span class="font-medium text-zinc-800 dark:text-white">{{ $row->country }}</span>
                        </div>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

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
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
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
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
                                <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}" class="hover:underline font-medium text-green-500">{{ $entry->game->winner->name }}</a>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
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
                                <img src="{{ asset('storage/images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-5 h-3 rounded-sm">
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
    <div class="rounded-xl border-l-4 border-l-zinc-500 border border-zinc-200 dark:border-zinc-700 p-4">
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
                        <img src="{{ asset('storage/images/country_flags/' . strtolower($row['p1_country']) . '.svg') }}"
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
                        <img src="{{ asset('storage/images/country_flags/' . strtolower($row['p2_country']) . '.svg') }}"
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
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 mt-4">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">🌍 Country vs Country</p>
            <span class="text-xs text-zinc-400">last 12 months · top 10 countries by player count</span>
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
                        <img src="{{ asset('storage/images/country_flags/' . strtolower($country->country_code) . '.svg') }}"
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
                                    <img src="{{ asset('storage/images/country_flags/' . strtolower($opponent->country_code) . '.svg') }}"
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