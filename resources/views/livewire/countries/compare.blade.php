@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6">
    @php
        $raceColors = [
            'Terran'  => 'text-blue-500',
            'Zerg'    => 'text-purple-500',
            'Protoss' => 'text-yellow-500',
            'Random'  => 'text-orange-400',
            'Unknown' => 'text-zinc-400',
        ];
        $c1 = strtoupper($code1);
        $c2 = strtoupper($code2);
    @endphp

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('countries.index') }}" wire:navigate
           class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/country_flags/' . strtolower($code1) . '.svg') }}" class="w-8 h-5 rounded-sm">
            <flux:heading size="xl">{{ $this->country1 }}</flux:heading>
            <span class="text-zinc-400 font-bold text-xl">vs</span>
            <img src="{{ asset('images/country_flags/' . strtolower($code2) . '.svg') }}" class="w-8 h-5 rounded-sm">
            <flux:heading size="xl">{{ $this->country2 }}</flux:heading>
        </div>
    </div>

    {{-- H2H Score --}}
    @php
        $h2h = $this->h2h;
        $c1ratio = $h2h['total'] > 0 ? round(($h2h['c1_wins'] / $h2h['total']) * 100) : 50;
    @endphp
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4 text-center">🆚 Head to Head</p>
        <div class="flex items-center justify-center gap-8">
            <div class="text-center">
                <img src="{{ asset('images/country_flags/' . strtolower($code1) . '.svg') }}" class="w-10 h-6 rounded-sm mx-auto mb-2">
                <p class="text-3xl font-bold {{ $h2h['c1_wins'] > $h2h['c2_wins'] ? 'text-green-500' : 'text-red-500' }}">{{ $h2h['c1_wins'] }}</p>
                <p class="text-xs text-zinc-400">wins</p>
            </div>
            <div class="text-center">
                <p class="text-zinc-400 text-sm">{{ $h2h['total'] }} games</p>
            </div>
            <div class="text-center">
                <img src="{{ asset('images/country_flags/' . strtolower($code2) . '.svg') }}" class="w-10 h-6 rounded-sm mx-auto mb-2">
                <p class="text-3xl font-bold {{ $h2h['c2_wins'] > $h2h['c1_wins'] ? 'text-green-500' : 'text-red-500' }}">{{ $h2h['c2_wins'] }}</p>
                <p class="text-xs text-zinc-400">wins</p>
            </div>
        </div>
        <div class="mt-4 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden max-w-md mx-auto">
            <div class="h-full bg-green-500 rounded-full" style="width: {{ $c1ratio }}%"></div>
        </div>
    </div>

    {{-- Race matchups --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">⚔️ Race matchups</p>
        @php
            $rm = $this->raceMatchups;
            $races = ['Terran', 'Zerg', 'Protoss'];
            $matchupData = [];
            foreach ($races as $r1) {
                foreach ($races as $r2) {
                    $c1wins = $rm->where('winner_race', $r1)->where('winner_country', $c1)->where('loser_race', $r2)->sum('games');
                    $c2wins = $rm->where('winner_race', $r2)->where('winner_country', $c2)->where('loser_race', $r1)->sum('games');
                    $total = $c1wins + $c2wins;
                    if ($total > 0) {
                        $matchupData[] = compact('r1', 'r2', 'c1wins', 'c2wins', 'total');
                    }
                }
            }
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach($matchupData as $m)
                @php
                    $ratio = round(($m['c1wins'] / $m['total']) * 100);
                @endphp
                <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1">
                            <img src="{{ asset('images/country_flags/' . strtolower($code1) . '.svg') }}" class="w-4 h-3 rounded-sm">
                            <span class="text-xs font-bold {{ $raceColors[$m['r1']] }}">{{ substr($m['r1'], 0, 1) }}</span>
                        </div>
                        <span class="text-xs text-zinc-400">{{ $m['total'] }} games</span>
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-bold {{ $raceColors[$m['r2']] }}">{{ substr($m['r2'], 0, 1) }}</span>
                            <img src="{{ asset('images/country_flags/' . strtolower($code2) . '.svg') }}" class="w-4 h-3 rounded-sm">
                        </div>
                    </div>
                    <div class="flex justify-between text-sm font-bold">
                        <span class="{{ $m['c1wins'] >= $m['c2wins'] ? 'text-green-500' : 'text-red-500' }}">{{ $m['c1wins'] }}</span>
                        <span class="{{ $m['c2wins'] >= $m['c1wins'] ? 'text-green-500' : 'text-red-500' }}">{{ $m['c2wins'] }}</span>
                    </div>
                    <div class="mt-1 h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full" style="width: {{ $ratio }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Top players --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="flex items-center gap-2 mb-4">
                <img src="{{ asset('images/country_flags/' . strtolower($code1) . '.svg') }}" class="w-7 h-5 rounded-sm">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Top players · {{ $this->country1 }}</p>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8">#</flux:table.column>
                    <flux:table.column>Player</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>W/L</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->topPlayersCountry1 as $index => $p)
                    <flux:table.row :key="'c1-'.$p->id" class="[&>td]:py-2">
                        <flux:table.cell><span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span></flux:table.cell>
                        <flux:table.cell>
                            <a href="{{ route('players.show', ['id' => $p->id, 'slug' => Str::slug($p->name)]) }}" class="hover:underline font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">
                                {{ $p->name }}
                            </a>
                            <span class="text-xs {{ $raceColors[$p->race] ?? 'text-zinc-400' }} ml-1">{{ $p->race }}</span>
                        </flux:table.cell>
                        <flux:table.cell><span class="font-bold text-zinc-800 dark:text-white">{{ $p->rating }}</span></flux:table.cell>
                        <flux:table.cell>
                            <span class="text-xs text-green-500">{{ $p->wins }}W</span>
                            <span class="text-xs text-zinc-400">/</span>
                            <span class="text-xs text-red-500">{{ $p->losses }}L</span>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="flex items-center gap-2 mb-4">
                <img src="{{ asset('images/country_flags/' . strtolower($code2) . '.svg') }}" class="w-7 h-5 rounded-sm">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Top players · {{ $this->country2 }}</p>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8">#</flux:table.column>
                    <flux:table.column>Player</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>W/L</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->topPlayersCountry2 as $index => $p)
                    <flux:table.row :key="'c2-'.$p->id" class="[&>td]:py-2">
                        <flux:table.cell><span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span></flux:table.cell>
                        <flux:table.cell>
                            <a href="{{ route('players.show', ['id' => $p->id, 'slug' => Str::slug($p->name)]) }}" class="hover:underline font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">
                                {{ $p->name }}
                            </a>
                            <span class="text-xs {{ $raceColors[$p->race] ?? 'text-zinc-400' }} ml-1">{{ $p->race }}</span>
                        </flux:table.cell>
                        <flux:table.cell><span class="font-bold text-zinc-800 dark:text-white">{{ $p->rating }}</span></flux:table.cell>
                        <flux:table.cell>
                            <span class="text-xs text-green-500">{{ $p->wins }}W</span>
                            <span class="text-xs text-zinc-400">/</span>
                            <span class="text-xs text-red-500">{{ $p->losses }}L</span>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    {{-- Recent games --}}
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
                            <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}" class="w-7 h-5 rounded-sm">
                            <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}" class="hover:underline font-medium text-green-500">{{ $entry->game->winner->name }}</a>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}" class="w-7 h-5 rounded-sm">
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