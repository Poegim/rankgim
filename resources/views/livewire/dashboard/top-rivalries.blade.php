@use('Illuminate\Support\Str')
@php
    $raceColors = [
        'Terran'  => 'text-blue-400',
        'Zerg'    => 'text-purple-400',
        'Protoss' => 'text-yellow-400',
        'Random'  => 'text-orange-400',
        'Unknown' => 'text-zinc-400',
    ];
@endphp

<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-3 sm:p-5">
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">⚔️ Top rivalries</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($this->rivalries as $row)
        <div class="rounded-xl border border-zinc-700/40 bg-zinc-800/60 p-4">
            <div class="flex items-center justify-between gap-3">
                {{-- Player A --}}
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <img src="{{ asset('images/country_flags/' . strtolower($row['p1_country']) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
                    <div class="min-w-0">
                        <a href="{{ route('players.show', ['id' => $row['player_a_id'], 'slug' => Str::slug($row['p1_name'])]) }}"
                           class="hover:underline font-semibold text-sm text-zinc-100 block truncate">{{ $row['p1_name'] }}</a>
                        <span class="text-xs {{ $raceColors[$row['p1_race']] ?? 'text-zinc-400' }}">{{ $row['p1_race'] }}</span>
                    </div>
                </div>
                {{-- Score --}}
                <div class="text-center shrink-0 px-2">
                    <p class="font-mono font-bold text-base">
                        <span class="{{ $row['player_a_wins'] > $row['player_b_wins'] ? 'text-green-400' : 'text-red-400' }}">{{ $row['player_a_wins'] }}</span>
                        <span class="text-zinc-600 mx-1">—</span>
                        <span class="{{ $row['player_b_wins'] > $row['player_a_wins'] ? 'text-green-400' : 'text-red-400' }}">{{ $row['player_b_wins'] }}</span>
                    </p>
                    <p class="text-xs text-zinc-600">{{ $row['games_count'] }} games</p>
                </div>
                {{-- Player B --}}
                <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
                    <div class="text-right min-w-0">
                        <a href="{{ route('players.show', ['id' => $row['player_b_id'], 'slug' => Str::slug($row['p2_name'])]) }}"
                           class="hover:underline font-semibold text-sm text-zinc-100 block truncate">{{ $row['p2_name'] }}</a>
                        <span class="text-xs {{ $raceColors[$row['p2_race']] ?? 'text-zinc-400' }}">{{ $row['p2_race'] }}</span>
                    </div>
                    <img src="{{ asset('images/country_flags/' . strtolower($row['p2_country']) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
                </div>
            </div>
            {{-- Win bar --}}
            <div class="mt-3 h-1.5 rounded-full bg-zinc-700 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-green-500 to-green-400 rounded-full"
                     style="width: {{ $row['games_count'] > 0 ? round(($row['player_a_wins'] / $row['games_count']) * 100) : 50 }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>