@use('Illuminate\Support\Str')

@php
    $raceColors = [
        'Terran'  => 'text-blue-400',
        'Zerg'    => 'text-purple-400',
        'Protoss' => 'text-yellow-400',
        'Random'  => 'text-orange-400',
        'Unknown' => 'text-zinc-400',
    ];

    // Top 3 styling: tło wiersza + tło kółka z numerem
    $podiumStyle = [
        0 => ['row' => 'bg-amber-500/10',  'badge' => 'bg-amber-500/30 text-amber-200 ring-1 ring-amber-500/60'],
        1 => ['row' => 'bg-zinc-400/10',   'badge' => 'bg-zinc-400/30 text-zinc-100 ring-1 ring-zinc-400/60'],
        2 => ['row' => 'bg-orange-700/10', 'badge' => 'bg-orange-600/30 text-orange-200 ring-1 ring-orange-600/60'],
    ];
@endphp

<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">🏆 Top 10</p>
        <a href="{{ route('rankings.index') }}"
           class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors">
            View full →
        </a>
    </div>

    {{-- Lista: jedna gramatyka wizualna, różne rozmiary top 3 vs 4-10 --}}
        <div class="flex flex-col rounded-xl border border-zinc-700/60 bg-zinc-800/40 divide-y divide-zinc-700/40 overflow-hidden">        @foreach($this->players as $index => $row)
            @php
                $rank = $index + 1;
                $isPodium = $index < 3;
                $winRatio = $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0;
                $change = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
                $podium = $podiumStyle[$index] ?? null;
            @endphp

            <a href="..."
               class="flex items-center gap-3 px-3 transition-colors group
                      bg-zinc-900
                      {{ $isPodium ? 'py-3 ' . $podium['row'] : 'py-2.5' }}
                      hover:bg-zinc-800">

                {{-- Numer / medalik --}}
                @if($isPodium)
                    <span class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold font-mono {{ $podium['badge'] }}">
                        {{ $rank }}
                    </span>
                @else
                    <span class="shrink-0 w-7 text-center font-mono text-xs text-zinc-500">
                        {{ $rank }}
                    </span>
                @endif

                {{-- Flaga + nick + rasa --}}
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                         class="w-5 h-3.5 rounded-sm shrink-0"
                         alt="{{ $row->player->country_code }}">
                    <span class="font-semibold truncate group-hover:underline
                                 {{ $isPodium ? 'text-sm text-white' : 'text-sm text-zinc-100' }}">
                        {{ $row->player->name }}
                    </span>
                    <span class="hidden sm:inline text-xs shrink-0 {{ $raceColors[$row->player->race] ?? 'text-zinc-400' }}">
                        {{ Str::substr($row->player->race, 0, 1) }}
                    </span>
                </div>

                {{-- MMR + trend (trend ma zawsze zarezerwowane miejsce) --}}
                <div class="flex items-baseline gap-1.5 shrink-0 tabular-nums">
                    <span class="font-mono text-white w-12 text-right
                                 {{ $isPodium ? 'text-base font-black' : 'text-sm font-bold' }}">
                        {{ $row->rating }}
                    </span>
                    <span class="font-mono text-[10px] font-semibold w-10 text-left tabular-nums
                                 {{ $change === null || $change == 0 ? 'text-transparent' : ($change > 0 ? 'text-green-400' : 'text-red-400') }}">
                        @if($change !== null && $change != 0)
                            {{ $change > 0 ? '▲' : '▼' }}{{ abs($change) }}
                        @else
                            ▲0
                        @endif
                    </span>
                </div>

                {{-- Winrate --}}
                <span class="shrink-0 w-10 text-right text-xs font-semibold tabular-nums
                             {{ $winRatio >= 50 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $winRatio }}%
                </span>
            </a>
        @endforeach
    </div>
</div>