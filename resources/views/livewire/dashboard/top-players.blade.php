@use('Illuminate\Support\Str')

@php
    // Race accent colors — resolved from CSS custom properties defined in app.css.
    // Race CSS vars auto-adjust per theme via :root:not(.dark) overrides.
    $raceVar = [
        'Terran'  => ['base' => 'var(--color-race-terran)',  'soft' => 'var(--color-race-terran-soft)'],
        'Zerg'    => ['base' => 'var(--color-race-zerg)',    'soft' => 'var(--color-race-zerg-soft)'],
        'Protoss' => ['base' => 'var(--color-race-protoss)', 'soft' => 'var(--color-race-protoss-soft)'],
        'Random'  => ['base' => 'var(--color-race-random)',  'soft' => 'var(--color-race-random-soft)'],
        'Unknown' => ['base' => 'var(--color-race-unknown)', 'soft' => 'var(--color-race-unknown-soft)'],
    ];

    // Podium medal dot colors — gold/silver/bronze, identical in both themes.
    // No row backgrounds — distinction by medal dot + bold rank/name only.
    $podiumMedal = [
        0 => '#b8860b',   // dark goldenrod
        1 => '#787876',   // steel
        2 => '#92400e',   // sienna bronze
    ];
@endphp

<div>
    {{-- Widget header — Cinzel oxblood signature, modern almanac convention --}}
    <div class="flex items-center justify-between mb-3">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em]
                  text-oxblood dark:text-zinc-500">
            🏆 Top 10
        </p>
        <a href="{{ route('rankings.index') }}"
           class="text-xs transition-colors
                  text-travertine-600 hover:text-oxblood
                  dark:text-zinc-400 dark:hover:text-zinc-200"
           wire:navigate>
            Full ranking →
        </a>
    </div>

    {{-- Card container — parchment lift on body sand bg --}}
    <div class="rounded-lg overflow-hidden
                border border-travertine-300 dark:border-zinc-700/60
                bg-travertine-50 dark:bg-zinc-800/40">

        @foreach($this->players as $index => $row)
            @php
                $rank     = $index + 1;
                $isPodium = $index < 3;
                $medal    = $podiumMedal[$index] ?? null;

                $race     = $row->player->race ?? 'Unknown';
                $vars     = $raceVar[$race] ?? $raceVar['Unknown'];

                $winRatio = $row->games_played > 0
                    ? round(($row->wins / $row->games_played) * 100)
                    : 0;

                $change = $row->prev_rating !== null
                    ? $row->rating - $row->prev_rating
                    : null;

                // ICCup rank color — semantic, theme-invariant.
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
               class="group flex items-center gap-2.5 pr-4 py-2.5 transition-colors
                      border-b border-travertine-350 last:border-b-0
                      dark:border-zinc-700/40
                      hover:bg-oxblood/5 dark:hover:bg-zinc-800/50"
               wire:navigate>

                {{-- ICCup rank accent strip — flush left, 2px wide --}}
                <div class="w-0.5 self-stretch shrink-0"
                     style="background: {{ $iccupColor }};"></div>

                {{-- Medal dot — only for podium 1/2/3 --}}
                @if($medal)
                    <span class="inline-block w-1.5 h-1.5 rounded-full shrink-0 ml-1.5"
                          style="background: {{ $medal }};"></span>
                @else
                    <span class="inline-block w-1.5 h-1.5 shrink-0 ml-1.5"></span>
                @endif

                {{-- Rank number --}}
                <div class="w-5 text-center shrink-0">
                    <span class="text-xs font-mono transition-colors
                                 {{ $isPodium
                                    ? 'font-bold text-travertine-900 dark:text-zinc-200'
                                    : 'font-semibold text-travertine-500 group-hover:text-travertine-700 dark:text-zinc-500 dark:group-hover:text-zinc-400' }}">
                        {{ $rank }}
                    </span>
                </div>

                {{-- Flag --}}
                @if($row->player->country_code)
                    <img src="https://flagcdn.com/{{ strtolower($row->player->country_code) }}.svg"
                         alt="{{ $row->player->country_code }}"
                         class="w-6 h-4 rounded-sm object-cover shrink-0 opacity-90 group-hover:opacity-100 transition-opacity">
                @else
                    <span class="w-6 h-4 rounded-sm shrink-0
                                 bg-travertine-300 dark:bg-zinc-700"></span>
                @endif

                {{-- Race pill — race CSS vars auto-adjust per theme --}}
                <span class="hidden sm:inline-flex items-center justify-center w-5 h-5 rounded text-[10px] font-bold uppercase shrink-0"
                      style="color: {{ $vars['soft'] }};
                             background: color-mix(in srgb, {{ $vars['base'] }} 12%, transparent);">
                    {{ substr($race, 0, 1) }}
                </span>

                {{-- Player name --}}
                <span class="flex-1 text-sm truncate transition-colors
                             {{ $isPodium
                                ? 'font-semibold text-travertine-900 dark:text-zinc-100'
                                : 'font-medium text-travertine-800 dark:text-zinc-200' }}
                             group-hover:text-oxblood dark:group-hover:text-white">
                    {{ $row->player->name }}
                </span>

                {{-- Rating delta — minimal, just colored text + arrow --}}
                @if($change !== null)
                    @if($change > 0)
                        <span class="hidden sm:inline-flex text-[10px] font-mono font-semibold shrink-0
                                     text-emerald-700 dark:text-emerald-400">
                            ▲ {{ $change }}
                        </span>
                    @elseif($change < 0)
                        <span class="hidden sm:inline-flex text-[10px] font-mono font-semibold shrink-0
                                     text-red-700 dark:text-red-400">
                            ▼ {{ abs($change) }}
                        </span>
                    @else
                        <span class="hidden sm:inline-flex text-[10px] font-mono shrink-0
                                     text-travertine-400 dark:text-zinc-600">—</span>
                    @endif
                @endif

                {{-- Win ratio mini-bar (visible on wider screens) --}}
                <div class="hidden lg:flex items-center gap-1.5 w-20 shrink-0">
                    <div class="flex-1 h-0.5 rounded-full overflow-hidden
                                bg-travertine-300 dark:bg-zinc-800">
                        <div class="h-full rounded-full transition-all"
                             style="width: {{ $winRatio }}%;
                                    background: color-mix(in srgb, {{ $vars['base'] }} 60%, transparent);"></div>
                    </div>
                    <span class="text-[10px] font-mono w-7 text-right
                                 text-travertine-500 dark:text-zinc-500">
                        {{ $winRatio }}%
                    </span>
                </div>

                {{-- Rating — monospaced, right-aligned --}}
                <span class="text-sm font-mono font-bold shrink-0
                             {{ $isPodium
                                ? 'text-travertine-900 dark:text-zinc-100'
                                : 'text-travertine-700 dark:text-zinc-300' }}">
                    {{ number_format($row->rating) }}
                </span>

            </a>
        @endforeach

        {{-- Empty state --}}
        @if($this->players->isEmpty())
            <div class="py-10 text-center text-sm
                        text-travertine-500 dark:text-zinc-600">
                No active players found.
            </div>
        @endif

    </div>
</div>