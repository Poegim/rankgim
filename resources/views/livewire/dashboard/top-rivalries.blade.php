@use('Illuminate\Support\Str')
@php
    // Race colors via CSS vars — single source of truth in app.css (rule #2)
    $raceKey = [
        'Terran'  => 'terran',
        'Zerg'    => 'zerg',
        'Protoss' => 'protoss',
        'Random'  => 'random',
        'Unknown' => 'unknown',
    ];
@endphp

<div class="rounded-xl border p-3 sm:p-5
    border-travertine-300 bg-travertine-50
    dark:border-zinc-700/60 dark:bg-zinc-800/40">
    <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
        ⚔️ Top rivalries
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($this->rivalries as $row)
        @php
            $total    = $row['games_count'];
            $aWins    = $row['player_a_wins'];
            $bWins    = $row['player_b_wins'];
            $aRatio   = $total > 0 ? round(($aWins / $total) * 100) : 50;
            $bRatio   = 100 - $aRatio;
            $k1       = $raceKey[$row['p1_race']] ?? 'unknown';
            $k2       = $raceKey[$row['p2_race']] ?? 'unknown';
        @endphp

        <div class="rounded-xl border p-4
            bg-travertine-75 border-travertine-300
            dark:bg-zinc-800/60 dark:border-zinc-700/40">

            <div class="flex items-center gap-2">

                {{-- Player A --}}
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <img src="{{ asset('images/country_flags/' . strtolower($row['p1_country']) . '.svg') }}"
                         class="w-5 h-3.5 rounded-sm shrink-0">
                    <div class="min-w-0">
                        <a href="{{ route('players.show', ['id' => $row['player_a_id'], 'slug' => Str::slug($row['p1_name'])]) }}"
                           class="hover:underline font-semibold text-sm truncate block
                               text-travertine-900 hover:text-oxblood
                               dark:text-zinc-100 dark:hover:text-white">
                            {{ $row['p1_name'] }}
                        </a>
                        <span class="text-[11px] font-medium"
                              style="color: var(--color-race-{{ $k1 }})">{{ $row['p1_race'] }}</span>
                    </div>
                </div>

                {{-- Score — central, prominent --}}
                <div class="text-center shrink-0 px-3">
                    <p class="font-mono font-black text-lg leading-none">
                        <span class="{{ $aWins > $bWins ? 'text-emerald-700 dark:text-emerald-400' : ($aWins < $bWins ? 'text-travertine-400 dark:text-zinc-500' : 'text-travertine-600 dark:text-zinc-400') }}">{{ $aWins }}</span>
                        <span class="text-travertine-300 dark:text-zinc-700 mx-1 font-light">:</span>
                        <span class="{{ $bWins > $aWins ? 'text-emerald-700 dark:text-emerald-400' : ($bWins < $aWins ? 'text-travertine-400 dark:text-zinc-500' : 'text-travertine-600 dark:text-zinc-400') }}">{{ $bWins }}</span>
                    </p>
                    <p class="text-[10px] mt-0.5 text-travertine-400 dark:text-zinc-600">{{ $total }} games</p>
                </div>

                {{-- Player B --}}
                <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
                    <div class="text-right min-w-0">
                        <a href="{{ route('players.show', ['id' => $row['player_b_id'], 'slug' => Str::slug($row['p2_name'])]) }}"
                           class="hover:underline font-semibold text-sm truncate block
                               text-travertine-900 hover:text-oxblood
                               dark:text-zinc-100 dark:hover:text-white">
                            {{ $row['p2_name'] }}
                        </a>
                        <span class="text-[11px] font-medium"
                              style="color: var(--color-race-{{ $k2 }})">{{ $row['p2_race'] }}</span>
                    </div>
                    <img src="{{ asset('images/country_flags/' . strtolower($row['p2_country']) . '.svg') }}"
                         class="w-5 h-3.5 rounded-sm shrink-0">
                </div>
            </div>

            {{-- Two-segment win bar (same pattern as race-matchups widget) --}}
            <div class="mt-3 h-1.5 rounded-full overflow-hidden flex
                bg-travertine-200 dark:bg-zinc-700">
                <div class="h-full transition-all"
                     style="width: {{ $aRatio }}%; background-color: var(--color-race-{{ $k1 }})"></div>
                <div class="h-full transition-all"
                     style="width: {{ $bRatio }}%; background-color: var(--color-race-{{ $k2 }})"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>