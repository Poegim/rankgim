@use('Illuminate\Support\Str')

@php
    // Race hex colors — inline style required, dynamic Tailwind classes break with JIT
    $raceHex = [
        'Terran'  => '#3b82f6',
        'Zerg'    => '#a855f7',
        'Protoss' => '#eab308',
        'Random'  => '#f97316',
        'Unknown' => '#71717a',
    ];

    $raceLabel = [
        'Terran'  => 'Terran',
        'Zerg'    => 'Zerg',
        'Protoss' => 'Protoss',
        'Random'  => 'Random',
        'Unknown' => 'Unknown',
    ];

    // Podium row tint + rank badge style
    $podiumStyle = [
        0 => ['row' => 'bg-amber-500/10',  'badge' => 'bg-amber-500/25 text-amber-300 ring-1 ring-amber-500/50'],
        1 => ['row' => 'bg-zinc-400/8',    'badge' => 'bg-zinc-400/20 text-zinc-200 ring-1 ring-zinc-400/40'],
        2 => ['row' => 'bg-orange-700/8',  'badge' => 'bg-orange-600/20 text-orange-300 ring-1 ring-orange-600/40'],
    ];
@endphp

<div>
    {{-- Widget header --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">🏆 Top 10</p>
        <a href="{{ route('rankings.index') }}"
           class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors">
            Full ranking →
        </a>
    </div>

    <div class="rounded-xl border border-zinc-700/60 bg-zinc-900 overflow-hidden divide-y divide-zinc-700/40">

        @foreach($this->players as $index => $row)
            @php
                $rank      = $index + 1;
                $isPodium  = $index < 3;
                $winRatio  = $row->games_played > 0
                    ? round(($row->wins / $row->games_played) * 100)
                    : 0;
                $change    = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
                $podium    = $podiumStyle[$index] ?? null;
                $hex       = $raceHex[$row->player->race] ?? '#71717a';
                $rLabel    = $raceLabel[$row->player->race] ?? '?';
            @endphp

            <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
               class="group flex items-center gap-3 pr-4 transition-colors
                      {{ $isPodium ? 'py-3.5 ' . ($podium['row'] ?? '') : 'py-2.5' }}
                      hover:bg-zinc-700/30"
               style="padding-left: 0;">

                {{-- Race accent bar + rank --}}
                <div class="flex items-center gap-3 shrink-0">
                    <div class="self-stretch w-1 rounded-r shrink-0"
                         style="background-color: {{ $hex }}; opacity: 0.65;"></div>

                    @if($isPodium)
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold font-mono {{ $podium['badge'] }}">
                            {{ $rank }}
                        </span>
                    @else
                        <span class="w-7 text-center font-mono text-xs text-zinc-600">
                            {{ $rank }}
                        </span>
                    @endif
                </div>

                {{-- Flag --}}
                <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                     class="{{ $isPodium ? 'w-8 h-6' : 'w-7 h-5' }} rounded-sm shrink-0 opacity-90"
                     alt="{{ $row->player->country_code }}">

                {{-- Name + race letter --}}
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="font-semibold truncate group-hover:underline
                                 {{ $isPodium ? 'text-base text-white' : 'text-sm text-zinc-200' }}">
                        {{ $row->player->name }}
                    </span>
                    <span class="hidden sm:inline shrink-0 text-xs font-bold font-mono"
                          style="color: {{ $hex }};">
                        {{ $rLabel }}
                    </span>
                </div>

                {{-- W / L --}}
                <div class="hidden md:flex items-center gap-1 shrink-0 text-xs font-mono tabular-nums">
                    <span class="text-emerald-400">{{ $row->wins }}W</span>
                    <span class="text-zinc-600">/</span>
                    <span class="text-red-400">{{ $row->losses }}L</span>
                </div>

                {{-- Win rate bar + % --}}
                <div class="hidden sm:flex items-center gap-2 shrink-0 w-24">
                    <div class="flex-1 h-1.5 rounded-full bg-zinc-700/60 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                                    {{ $winRatio >= 60 ? 'bg-emerald-400' : ($winRatio >= 50 ? 'bg-emerald-500/70' : 'bg-red-500/70') }}"
                             style="width: {{ $winRatio }}%"></div>
                    </div>
                    <span class="text-[11px] font-semibold tabular-nums w-8 text-right
                                 {{ $winRatio >= 50 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $winRatio }}%
                    </span>
                </div>

                {{-- MMR --}}
                <span class="font-mono text-right shrink-0
                             {{ $isPodium ? 'text-base font-black text-white w-14' : 'text-sm font-bold text-zinc-100 w-12' }}">
                    {{ $row->rating }}
                </span>

                {{-- Rating change --}}
                <span class="font-mono text-[11px] font-semibold w-9 text-left tabular-nums shrink-0
                             {{ $change === null || $change == 0
                                 ? 'text-transparent select-none'
                                 : ($change > 0 ? 'text-emerald-400' : 'text-red-400') }}">
                    @if($change !== null && $change != 0)
                        {{ $change > 0 ? '▲' : '▼' }}{{ abs($change) }}
                    @else
                        ▲0
                    @endif
                </span>

            </a>
        @endforeach

    </div>
</div>