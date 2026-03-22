@use('Illuminate\Support\Str')
<div>
    @php
        $raceColors = [
            'Terran'  => 'text-blue-400',
            'Zerg'    => 'text-purple-400',
            'Protoss' => 'text-yellow-400',
            'Random'  => 'text-orange-400',
            'Unknown' => 'text-zinc-400',
        ];
        $raceBorderColors = [
            'Terran'  => 'border-l-blue-500',
            'Zerg'    => 'border-l-purple-500',
            'Protoss' => 'border-l-yellow-500',
            'Random'  => 'border-l-orange-500',
            'Unknown' => 'border-l-zinc-600',
        ];
        $raceDots = [
            'Terran'  => 'bg-blue-500',
            'Zerg'    => 'bg-purple-500',
            'Protoss' => 'bg-yellow-500',
            'Random'  => 'bg-orange-500',
            'Unknown' => 'bg-zinc-500',
        ];
        $raceBgSubtle = [
            'Terran'  => 'bg-blue-500/[0.03]',
            'Zerg'    => 'bg-purple-500/[0.03]',
            'Protoss' => 'bg-yellow-500/[0.03]',
            'Random'  => 'bg-orange-500/[0.03]',
            'Unknown' => '',
        ];

        function getTier($tierLetter) {
            return match($tierLetter) {
                'S' => ['icon' => '👑', 'ratingColor' => 'text-amber-300', 'rowClass' => 'bg-gradient-to-r from-amber-500/10 to-transparent ring-1 ring-amber-500/20', 'nameClass' => 'text-amber-200'],
                'A' => ['icon' => '💎', 'ratingColor' => 'text-red-400', 'rowClass' => 'bg-gradient-to-r from-red-500/8 to-transparent', 'nameClass' => ''],
                'B' => ['icon' => '⭐', 'ratingColor' => 'text-purple-400', 'rowClass' => 'bg-gradient-to-r from-purple-500/6 to-transparent', 'nameClass' => ''],
                default => ['icon' => '', 'ratingColor' => 'text-zinc-200', 'rowClass' => '', 'nameClass' => ''],
            };
        }
        
    @endphp

    {{-- Region filters --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        @foreach([
            'Europe' => '🌍',
            'Asia' => '🌏',
            'North America' => '🌎',
            'South America' => '🌎',
        ] as $region => $emoji)
            <button
                wire:click="filterByRegion('{{ $region }}')"
                class="flex flex-col items-center gap-1 px-4 py-3 rounded-xl font-medium transition-all duration-200
                    {{ $filterRegion === $region
                        ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30 scale-[1.03]'
                        : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700' }}"
            >
                <span class="text-3xl">{{ $emoji }}</span>
                <span class="text-xs">{{ $region }}</span>
            </button>
        @endforeach
    </div>

    {{-- Active filters --}}
    @if($filterCountryCode || $filterRace || $filterRegion)
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="text-zinc-500 dark:text-zinc-400 text-sm">Active filters:</span>

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
            <span class="w-2 h-2 rounded-full {{ $raceDots[$filterRace] ?? 'bg-zinc-500' }}"></span>
            {{ $filterRace }} <span class="ml-1">×</span>
        </flux:badge>
        @endif

        <flux:button size="sm" variant="ghost" wire:click="$set('filterCountryCode', null); $set('filterRace', null); $set('filterRegion', null)">
            Clear all
        </flux:button>
    </div>
    @endif

    {{-- Rank tier legend --}}
        <div class="flex flex-wrap items-center gap-4 mb-4 px-1">
            <span class="text-xs text-zinc-500">Ranks:</span>
            <span class="flex items-center gap-1 text-xs text-amber-300">👑 S-Rank · Top 1%</span>
            <span class="flex items-center gap-1 text-xs text-red-400">💎 A-Rank · Top 8%</span>
            <span class="flex items-center gap-1 text-xs text-purple-400">⭐ B-Rank · Top 29%</span>
        </div>

    {{-- Rankings --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">

        {{-- Header --}}
        <div class="grid grid-cols-12 gap-0 px-5 py-3 bg-zinc-100 dark:bg-zinc-800/80 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">
            <div class="col-span-1">#</div>
            <div class="col-span-4 md:col-span-3">Player</div>
            <div class="col-span-1 hidden sm:block">Race</div>
            <div class="col-span-2 cursor-pointer hover:text-zinc-200 transition-colors {{ $sortBy === 'rating' ? 'text-indigo-400' : '' }}" wire:click="sort('rating')">
                Rating {!! $sortBy === 'rating' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' !!}
            </div>
            <div class="col-span-1 hidden md:block">+/-</div>
            <div class="col-span-1 cursor-pointer hover:text-zinc-200 transition-colors {{ $sortBy === 'wins' ? 'text-indigo-400' : '' }}" wire:click="sort('wins')">
                W {!! $sortBy === 'wins' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' !!}
            </div>
            <div class="col-span-1 cursor-pointer hover:text-zinc-200 transition-colors {{ $sortBy === 'losses' ? 'text-indigo-400' : '' }}" wire:click="sort('losses')">
                L {!! $sortBy === 'losses' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' !!}
            </div>
            <div class="col-span-1 hidden lg:block cursor-pointer hover:text-zinc-200 transition-colors {{ $sortBy === 'games_played' ? 'text-indigo-400' : '' }}" wire:click="sort('games_played')">
                GP {!! $sortBy === 'games_played' ? ($sortDirection === 'desc' ? '↓' : '↑') : '' !!}
            </div>
            <div class="col-span-2 lg:col-span-1">Win%</div>
        </div>

        {{-- Rows --}}
        @foreach($this->rankings as $index => $row)
        @php
            $rank = $this->rankings->firstItem() + $index;
            $winRatio = $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0;
            $change = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
            $tierLetter = $this->playerTiers[$row->player_id] ?? null;
            $tier = getTier($tierLetter);
        @endphp
        <div
            wire:key="ranking-{{ $row->id }}"
            class="group grid grid-cols-12 gap-0 items-center px-5 py-3 border-b border-zinc-200/50 dark:border-zinc-700/50 border-l-4 {{ $raceBorderColors[$row->player->race] ?? 'border-l-zinc-600' }} {{ $raceBgSubtle[$row->player->race] ?? '' }} hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-all duration-150 {{ $tier['rowClass'] }}"
        >
            {{-- Rank --}}
            <div class="col-span-1">
                <span class="font-mono text-sm {{ $rank <= 10 ? 'text-zinc-200 font-bold' : 'text-zinc-500' }}">{{ $rank }}</span>
            </div>

            {{-- Player --}}
            <div class="col-span-4 md:col-span-3">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                        alt="{{ $row->player->country }}"
                        class="w-7 h-5 rounded-sm cursor-pointer hover:ring-2 hover:ring-indigo-500/50 transition-all {{ $filterCountryCode === $row->player->country_code ? 'ring-2 ring-indigo-500' : '' }}"
                        title="{{ $row->player->country }}"
                        wire:click="filterByCountry('{{ $row->player->country_code }}')"
                    >
                    <div class="min-w-0 flex items-center gap-1.5">
                        @if($tier['icon'])
                            <span class="text-sm">{{ $tier['icon'] }}</span>
                        @endif
                        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
                           class="hover:underline font-semibold {{ $tier['nameClass'] ?: 'text-zinc-800 dark:text-white' }} block truncate">
                            {{ $row->player->name }}
                        </a>
                    </div>
                    <span class="sm:hidden w-2 h-2 rounded-full {{ $raceDots[$row->player->race] ?? 'bg-zinc-500' }}" title="{{ $row->player->race }}"></span>
                </div>
            </div>

            {{-- Race --}}
            <div class="col-span-1 hidden sm:block">
                <span
                    class="cursor-pointer font-medium text-sm {{ $raceColors[$row->player->race] ?? 'text-zinc-400' }} hover:opacity-70 transition-opacity"
                    wire:click="filterByRace('{{ $row->player->race }}')"
                >{{ $row->player->race }}</span>
            </div>

            {{-- Rating --}}
            <div class="col-span-2">
                <span class="font-mono text-lg font-bold {{ $tier['ratingColor'] }}">{{ $row->rating }}</span>
            </div>

            {{-- Change --}}
            <div class="col-span-1 hidden md:block">
                @if($change !== null && $change != 0)
                    <span class="font-mono text-sm font-semibold {{ $change > 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $change > 0 ? '▲' : '▼' }}{{ abs($change) }}
                    </span>
                @else
                    <span class="text-zinc-600">—</span>
                @endif
            </div>

            {{-- Wins --}}
            <div class="col-span-1">
                <span class="font-mono font-semibold text-green-400">{{ $row->wins }}</span>
            </div>

            {{-- Losses --}}
            <div class="col-span-1">
                <span class="font-mono font-semibold text-red-400">{{ $row->losses }}</span>
            </div>

            {{-- Games played --}}
            <div class="col-span-1 hidden lg:block">
                <span class="font-mono text-zinc-500">{{ $row->games_played }}</span>
            </div>

            {{-- Win% with bar --}}
            <div class="col-span-2 lg:col-span-1">
                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-sm {{ $winRatio >= 60 ? 'text-green-300' : ($winRatio >= 50 ? 'text-green-400' : 'text-red-400') }}">{{ $winRatio }}%</span>
                    <div class="hidden md:block w-14 h-1.5 rounded-full bg-zinc-700/50 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $winRatio >= 60 ? 'bg-green-400' : ($winRatio >= 50 ? 'bg-green-500/70' : 'bg-red-500/70') }}"
                             style="width: {{ $winRatio }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $this->rankings->links() }}
    </div>
</div>