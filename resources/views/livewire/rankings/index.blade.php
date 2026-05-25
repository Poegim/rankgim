@use('Illuminate\Support\Str')
<div>
    @php
        // Race text colors — resolved from CSS custom properties in app.css.
        // Vars auto-adjust per theme via :root:not(.dark) overrides.
        $raceColors = [
            'Terran'  => 'var(--color-race-terran-soft)',
            'Zerg'    => 'var(--color-race-zerg-soft)',
            'Protoss' => 'var(--color-race-protoss-soft)',
            'Random'  => 'var(--color-race-random-soft)',
            'Unknown' => 'var(--color-race-unknown-soft)',
        ];

        // Race dot colors — used in filter badges. Base variant (more saturated).
        $raceDots = [
            'Terran'  => 'var(--color-race-terran)',
            'Zerg'    => 'var(--color-race-zerg)',
            'Protoss' => 'var(--color-race-protoss)',
            'Random'  => 'var(--color-race-random)',
            'Unknown' => 'var(--color-race-unknown)',
        ];

        // Podium styling — top 3 get row gradient tints. Light variants use
        // higher opacity since color-mix on cream needs stronger contrast.
        function getTierByRank($rank) {
            return match(true) {
                $rank === 1 => [
                    'icon'      => '&#x1F451;',
                    'rowClass'  => 'bg-gradient-to-r from-amber-200/40 to-transparent ring-1 ring-amber-300/60 dark:from-amber-500/10 dark:to-transparent dark:ring-amber-500/20',
                    'nameClass' => 'text-amber-800 dark:text-amber-200',
                ],
                $rank === 2 => [
                    'icon'      => '&#x1F948;',
                    'rowClass'  => 'bg-gradient-to-r from-travertine-300/40 to-transparent dark:from-zinc-500/8 dark:to-transparent',
                    'nameClass' => '',
                ],
                $rank === 3 => [
                    'icon'      => '&#x1F949;',
                    'rowClass'  => 'bg-gradient-to-r from-orange-200/40 to-transparent dark:from-orange-500/6 dark:to-transparent',
                    'nameClass' => '',
                ],
                default => [
                    'icon'      => '',
                    'rowClass'  => '',
                    'nameClass' => '',
                ],
            };
        }
    @endphp

    {{-- ─── Region filters ──────────────────────────────────────────────── --}}
    {{-- Active state uses oxblood (brand CTA) instead of indigo for         --}}
    {{-- consistency. Inactive uses travertine surface.                       --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        @foreach([
            'Europe'        => '🌍',
            'North America' => '🌎',
            'South America' => '🌎',
            'Asia'          => '🌏',
        ] as $region => $emoji)
            <button
                wire:click="filterByRegion('{{ $region }}')"
                class="flex flex-col items-center gap-1 px-4 py-3 rounded-xl font-medium transition-all duration-200
                    {{ $filterRegion === $region
                        ? 'bg-oxblood !text-oxblood-content shadow-lg shadow-oxblood/30 scale-[1.03] dark:bg-indigo-500 dark:!text-white dark:shadow-indigo-500/30'
                        : 'bg-travertine-100 text-travertine-700 hover:bg-travertine-200 border border-travertine-300 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:border-zinc-700' }}"
            >
                <span class="text-3xl">{{ $emoji }}</span>
                <span class="text-xs">{{ $region }}</span>
            </button>
        @endforeach
    </div>

    {{-- ─── Active filter badges ────────────────────────────────────────── --}}
    @if($filterCountryCode || $filterRace || $filterRegion)
        <div class="flex flex-wrap items-center gap-2 mb-5">
            <span class="text-sm text-travertine-600 dark:text-zinc-400">Active filters:</span>

            @if($filterRegion)
                <flux:badge wire:click="filterByRegion('{{ $filterRegion }}')" class="cursor-pointer">
                    {{ $filterRegion }} <span class="ml-1">×</span>
                </flux:badge>
            @endif

            @if($filterCountryCode)
                <flux:badge wire:click="filterByCountry('{{ $filterCountryCode }}')" class="cursor-pointer gap-1">
                    <img src="{{ asset('images/country_flags/' . strtolower($filterCountryCode) . '.svg') }}" class="w-5 h-3.5 rounded-sm">
                    {{ $filterCountryCode }}
                    <span class="ml-1">×</span>
                </flux:badge>
            @endif

            @if($filterRace)
                <flux:badge wire:click="filterByRace('{{ $filterRace }}')" class="cursor-pointer gap-1">
                    <span class="w-2 h-2 rounded-full"
                          style="background: {{ $raceDots[$filterRace] ?? 'var(--color-race-unknown)' }};"></span>
                    {{ $filterRace }} <span class="ml-1">×</span>
                </flux:badge>
            @endif

            <flux:button size="sm" variant="ghost"
                         wire:click="$set('filterCountryCode', null); $set('filterRace', null); $set('filterRegion', null)">
                Clear all
            </flux:button>
        </div>
    @endif

    {{-- ─── Rank tier legend ────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-4 mb-4 px-1">
        <span class="text-xs text-travertine-500 dark:text-zinc-500">Ranks:</span>
        <span class="text-xs text-amber-700 dark:text-amber-300">👑 · #1</span>
        <span class="text-xs text-travertine-700 dark:text-zinc-300">🥈🥉 · #2–3</span>
    </div>

    {{-- ─── Rankings table ──────────────────────────────────────────────── --}}
    <div class="rounded-xl overflow-hidden
                border border-travertine-300 dark:border-zinc-700">

        {{-- Table header --}}
        <div class="flex items-center gap-0 px-5 py-3 text-xs font-semibold uppercase tracking-wider
                    bg-travertine-100 text-travertine-600 border-b border-travertine-300
                    dark:bg-zinc-800/80 dark:text-zinc-400 dark:border-zinc-700">
            <div class="w-8 shrink-0">#</div>
            <div class="flex-1 min-w-0">Player</div>
            <div class="w-20 hidden sm:block">Race</div>

            {{-- Sortable columns: active sort uses oxblood (brand accent), light hover travertine-900 --}}
            <div class="w-24 cursor-pointer transition-colors
                        hover:text-travertine-900 dark:hover:text-zinc-200
                        {{ $sortBy === 'rating' ? 'text-oxblood dark:text-indigo-400' : '' }}"
                 wire:click="sort('rating')">
                Rating {!! $sortBy === 'rating' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' !!}
            </div>

            <div class="w-16 hidden md:block">+/-</div>

            <div class="w-14 hidden md:block cursor-pointer transition-colors
                        hover:text-travertine-900 dark:hover:text-zinc-200
                        {{ $sortBy === 'wins' ? 'text-oxblood dark:text-indigo-400' : '' }}"
                 wire:click="sort('wins')">
                W {!! $sortBy === 'wins' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' !!}
            </div>

            <div class="w-14 hidden md:block cursor-pointer transition-colors
                        hover:text-travertine-900 dark:hover:text-zinc-200
                        {{ $sortBy === 'losses' ? 'text-oxblood dark:text-indigo-400' : '' }}"
                 wire:click="sort('losses')">
                L {!! $sortBy === 'losses' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' !!}
            </div>

            <div class="w-14 hidden lg:block cursor-pointer transition-colors
                        hover:text-travertine-900 dark:hover:text-zinc-200
                        {{ $sortBy === 'games_played' ? 'text-oxblood dark:text-indigo-400' : '' }}"
                 wire:click="sort('games_played')">
                GP {!! $sortBy === 'games_played' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' !!}
            </div>

            <div class="w-20 text-right">Win%</div>
        </div>

        {{-- Table rows --}}
        @foreach($this->rankings as $index => $row)
            @php
                $rank     = $this->rankings->firstItem() + $index;
                $winRatio = $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0;
                $change   = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
                $tier     = getTierByRank($rank);

                // ICCup rank color — semantic, kept identical in both themes.
                // Drives both the left border accent and the rating number color.
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

            <div
                wire:key="ranking-{{ $row->id }}"
                class="group flex items-center gap-0 px-5 py-3 transition-all duration-150
                       border-b border-travertine-300/50 dark:border-zinc-700/50
                       hover:bg-oxblood/5 dark:hover:bg-zinc-800/40
                       {{ $tier['rowClass'] }}"
                style="border-left: 4px solid {{ $iccupColor }};"
            >
                {{-- Rank number --}}
                <div class="w-8 shrink-0">
                    <span @class([
                        'font-mono text-sm',
                        'font-bold text-travertine-900 dark:text-zinc-200' => $rank <= 3,
                        'text-travertine-500 dark:text-zinc-500' => $rank > 3,
                    ])>
                        {{ $rank }}
                    </span>
                </div>

                {{-- Player name + flag --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <img
                            src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                            alt="{{ $row->player->country }}"
                            class="w-6 h-4 sm:w-7 sm:h-5 rounded-sm cursor-pointer shrink-0 transition-all
                                   hover:ring-2 hover:ring-oxblood/50 dark:hover:ring-indigo-500/50
                                   {{ $filterCountryCode === $row->player->country_code ? 'ring-2 ring-oxblood dark:ring-indigo-500' : '' }}"
                            title="{{ $row->player->country }}"
                            wire:click="filterByCountry('{{ $row->player->country_code }}')"
                        >
                        <div class="min-w-0 flex items-center gap-1.5">
                            @if($tier['icon'])
                                <span class="text-sm hidden sm:inline shrink-0">{!! $tier['icon'] !!}</span>
                            @endif
                            <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
                               class="hover:underline font-semibold truncate
                                      {{ $tier['nameClass'] ?: 'text-travertine-900 dark:text-white' }}">
                                {{ $row->player->name }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Race — clickable filter, color from CSS var --}}
                <div class="w-20 hidden sm:block">
                    <span
                        class="cursor-pointer font-medium text-sm hover:opacity-70 transition-opacity"
                        style="color: {{ $raceColors[$row->player->race] ?? 'var(--color-race-unknown-soft)' }};"
                        wire:click="filterByRace('{{ $row->player->race }}')">
                        {{ $row->player->race }}
                    </span>
                </div>

                {{-- Rating — ICCup color in both themes --}}
                <div class="w-24">
                    <span class="font-mono text-lg font-bold"
                          style="color: {{ $iccupColor }};">
                        {{ $row->rating }}
                    </span>
                </div>

                {{-- Rating change delta --}}
                <div class="w-16 hidden md:block">
                    @if($change !== null && $change != 0)
                        <span @class([
                            'font-mono text-sm font-semibold',
                            'text-emerald-700 dark:text-emerald-400' => $change > 0,
                            'text-red-700 dark:text-red-400' => $change < 0,
                        ])>
                            {{ $change > 0 ? '▲' : '▼' }}{{ abs($change) }}
                        </span>
                    @else
                        <span class="text-travertine-500 dark:text-zinc-600">—</span>
                    @endif
                </div>

                {{-- Wins --}}
                <div class="w-14 hidden md:block">
                    <span class="font-mono font-semibold
                                 text-emerald-700 dark:text-emerald-400">{{ $row->wins }}</span>
                </div>

                {{-- Losses --}}
                <div class="w-14 hidden md:block">
                    <span class="font-mono font-semibold
                                 text-red-700 dark:text-red-400">{{ $row->losses }}</span>
                </div>

                {{-- Games played --}}
                <div class="w-14 hidden lg:block">
                    <span class="font-mono text-travertine-500 dark:text-zinc-500">{{ $row->games_played }}</span>
                </div>

                {{-- Win% with mini bar --}}
                <div class="w-20">
                    <div class="flex items-center gap-2 justify-end">
                        <span @class([
                            'font-mono font-bold text-sm',
                            'text-emerald-700 dark:text-emerald-300' => $winRatio >= 60,
                            'text-emerald-700 dark:text-emerald-400' => $winRatio >= 50 && $winRatio < 60,
                            'text-red-700 dark:text-red-400' => $winRatio < 50,
                        ])>
                            {{ $winRatio }}%
                        </span>
                        <div class="hidden md:block w-10 h-1.5 rounded-full overflow-hidden
                                    bg-travertine-300 dark:bg-zinc-700/50">
                            <div @class([
                                    'h-full rounded-full transition-all duration-500',
                                    'bg-emerald-500 dark:bg-emerald-400' => $winRatio >= 60,
                                    'bg-emerald-600 dark:bg-emerald-500/70' => $winRatio >= 50 && $winRatio < 60,
                                    'bg-red-500 dark:bg-red-500/70' => $winRatio < 50,
                                 ])
                                 style="width: {{ $winRatio }}%"></div>
                        </div>
                    </div>
                </div>

            </div>
        @endforeach

    </div>

    {{-- Pagination — Flux handles its own theme --}}
    <div class="mt-4">
        {{ $this->rankings->links() }}
    </div>

</div>