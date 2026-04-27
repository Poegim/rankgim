@use('Illuminate\Support\Str')

@php
    // Race accent colors — resolved from CSS custom properties defined in app.css.
    // Use inline style with var() so Tailwind JIT does not strip unused classes.
    // "soft" variant is lighter, used for text; base variant for borders/glows.
    $raceVar = [
        'Terran'  => ['base' => 'var(--color-race-terran)',  'soft' => 'var(--color-race-terran-soft)'],
        'Zerg'    => ['base' => 'var(--color-race-zerg)',    'soft' => 'var(--color-race-zerg-soft)'],
        'Protoss' => ['base' => 'var(--color-race-protoss)', 'soft' => 'var(--color-race-protoss-soft)'],
        'Random'  => ['base' => 'var(--color-race-random)',  'soft' => 'var(--color-race-random-soft)'],
        'Unknown' => ['base' => 'var(--color-race-unknown)', 'soft' => 'var(--color-race-unknown-soft)'],
    ];

    // Podium row tint + rank badge style — top 3 get special treatment.
    $podiumStyle = [
        0 => [
            'row'   => 'bg-amber-500/10',
            'badge' => 'bg-amber-500/20 text-amber-300 ring-1 ring-amber-500/40',
        ],
        1 => [
            'row'   => 'bg-zinc-400/[0.06]',
            'badge' => 'bg-zinc-400/15 text-zinc-200 ring-1 ring-zinc-400/30',
        ],
        2 => [
            'row'   => 'bg-orange-700/[0.06]',
            'badge' => 'bg-orange-600/15 text-orange-300 ring-1 ring-orange-600/30',
        ],
    ];
@endphp

<div>
    {{-- Widget header — matches the dashboard widget contract --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">🏆 Top 10</p>
        <a href="{{ route('rankings.index') }}"
           class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors"
           wire:navigate>
            Full ranking →
        </a>
    </div>

    <div class="rounded-xl border border-zinc-700/60 bg-zinc-900 overflow-hidden divide-y divide-zinc-700/40">

        @foreach($this->players as $index => $row)
            @php
                $rank     = $index + 1;
                $isPodium = $index < 3;
                $podium   = $podiumStyle[$index] ?? null;

                $race     = $row->player->race ?? 'Unknown';
                $vars     = $raceVar[$race] ?? $raceVar['Unknown'];

                $winRatio = $row->games_played > 0
                    ? round(($row->wins / $row->games_played) * 100)
                    : 0;

                $change = $row->prev_rating !== null
                    ? $row->rating - $row->prev_rating
                    : null;

                // ICCup rank color based on rating thresholds
                $iccupColor = match(true) {
                    $row->rating >= 2000 => '#d4a832',
                    $row->rating >= 1900 => '#3cc431',
                    $row->rating >= 1800 => '#3ec133',
                    $row->rating >= 1700 => '#6a8dd1',
                    $row->rating >= 1600 => '#5c7cc5',
                    $row->rating >= 1500 => '#6988c0',
                    $row->rating >= 1400 => '#c1d43d',
                    $row->rating >= 1300 => '#bfd245',
                    $row->rating >= 1200 => '#c2d34e',
                    $row->rating >= 1100 => '#d11b1f',
                    $row->rating >= 1000 => '#b51c23',
                    default              => '#ce1e25',
                };
            @endphp

            <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
               class="group flex items-center gap-3 pr-4 transition-colors
                      {{ $isPodium ? 'py-3.5 ' . ($podium['row'] ?? '') : 'py-3 hover:bg-zinc-800/50' }}"
               wire:navigate>

                {{-- ICCup rank accent strip — flush to left edge, color reflects rating tier --}}
                <div class="w-1 self-stretch shrink-0"
                     style="background: {{ $iccupColor }};"></div>

                {{-- Rank number — plain mono, no circles --}}
                <div class="w-6 text-center shrink-0">
                    <span class="text-sm font-mono font-bold transition-colors
                                 {{ $isPodium ? 'text-zinc-200' : 'text-zinc-500 group-hover:text-zinc-400' }}">
                        {{ $rank }}
                    </span>
                </div>

                {{-- Flag — slightly larger for readability --}}
                @if($row->player->country_code)
                    <img src="https://flagcdn.com/{{ strtolower($row->player->country_code) }}.svg"
                         alt="{{ $row->player->country_code }}"
                         class="w-7 h-5 rounded-sm object-cover shrink-0 opacity-80 group-hover:opacity-100 transition-opacity">
                @else
                    <span class="w-7 h-5 rounded-sm bg-zinc-700 shrink-0"></span>
                @endif

                {{-- Race pill — fixed size square showing only the first letter, always consistent width --}}
                <span class="hidden sm:inline-flex items-center justify-center w-6 h-6 rounded text-xs font-bold uppercase shrink-0"
                      style="color: {{ $vars['soft'] }};
                             background: color-mix(in srgb, {{ $vars['base'] }} 12%, transparent);
                             border: 1px solid color-mix(in srgb, {{ $vars['base'] }} 25%, transparent);">
                    {{ substr($race, 0, 1) }}
                </span>

                {{-- Player name --}}
                <span class="flex-1 text-base font-medium text-zinc-200 truncate
                             group-hover:text-white transition-colors
                             {{ $isPodium ? 'font-semibold' : '' }}">
                    {{ $row->player->name }}
                </span>

                {{-- Rating delta badge --}}
                @if($change !== null)
                    @if($change > 0)
                        <span class="hidden sm:inline-flex items-center gap-0.5 text-[10px] font-mono font-semibold
                                     text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded shrink-0">
                            ▲ {{ $change }}
                        </span>
                    @elseif($change < 0)
                        <span class="hidden sm:inline-flex items-center gap-0.5 text-[10px] font-mono font-semibold
                                     text-red-400 bg-red-500/10 px-1.5 py-0.5 rounded shrink-0">
                            ▼ {{ abs($change) }}
                        </span>
                    @else
                        <span class="hidden sm:inline-flex text-[10px] font-mono text-zinc-600 shrink-0">—</span>
                    @endif
                @endif

                {{-- Win ratio bar (visible on wider screens) --}}
                <div class="hidden lg:flex items-center gap-2 w-24 shrink-0">
                    <div class="flex-1 h-1 rounded-full bg-zinc-800 overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width: {{ $winRatio }}%;
                                    background: color-mix(in srgb, {{ $vars['base'] }} 70%, transparent);"></div>
                    </div>
                    <span class="text-[10px] font-mono text-zinc-500 w-7 text-right">
                        {{ $winRatio }}%
                    </span>
                </div>

                {{-- Rating — monospaced, right-aligned --}}
                <span class="text-base font-mono font-bold shrink-0
                             {{ $isPodium ? 'text-zinc-100' : 'text-zinc-300' }}">
                    {{ number_format($row->rating) }}
                </span>

            </a>
        @endforeach

        {{-- Empty state --}}
        @if($this->players->isEmpty())
            <div class="py-10 text-center text-zinc-600 text-sm">
                No active players found.
            </div>
        @endif

    </div>
</div>