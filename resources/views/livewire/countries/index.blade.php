@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Countries</flux:heading>
        <flux:text>Country statistics · active players with 15+ games · top 10 countries by player count</flux:text>
    </div>

    {{-- Country compare picker --}}
    <div class="rounded-xl border p-4
        border-travertine-300 bg-travertine-50
        dark:border-zinc-700 dark:bg-transparent">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
            🆚 Compare countries
        </p>
        <div class="flex items-end gap-4">
            <div class="flex-1">
                <flux:select wire:model="compareCountry1" label="Country 1">
                    <option value="">Select country...</option>
                    @foreach($this->allCountries as $c)
                        <option value="{{ $c->country_code }}">{{ $c->country }}</option>
                    @endforeach
                </flux:select>
            </div>
            <span class="font-bold pb-2 text-travertine-400 dark:text-zinc-400">vs</span>
            <div class="flex-1">
                <flux:select wire:model="compareCountry2" label="Country 2">
                    <option value="">Select country...</option>
                    @foreach($this->allCountries as $c)
                        <option value="{{ $c->country_code }}">{{ $c->country }}</option>
                    @endforeach
                </flux:select>
            </div>
            <flux:button variant="primary" wire:click="goCompare">Compare</flux:button>
        </div>
    </div>

    {{-- Country stats table --}}
    <div class="rounded-xl border p-4
        border-travertine-300 bg-travertine-50
        dark:border-zinc-700 dark:bg-transparent">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
            🌍 Top countries
            <span class="font-sans normal-case tracking-normal text-[10px] text-travertine-400 dark:text-zinc-600 ml-1">
                (average calculated from top 5 active players · last 12 months · games of all players)
            </span>
        </p>
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
                        <span class="text-sm font-mono text-travertine-400 dark:text-zinc-400">{{ $index + 1 }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <a href="{{ route('rankings.index', ['filterCountryCode' => $row->country_code]) }}"
                           class="flex items-center gap-2 hover:underline">
                            <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                                 class="w-7 h-5 rounded-sm">
                            <span class="font-semibold text-[0.9375rem] text-travertine-900 dark:text-white">
                                {{ $row->country }}
                            </span>
                        </a>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-travertine-500 dark:text-zinc-400">{{ $row->player_count }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="font-bold text-travertine-900 dark:text-white">{{ $row->avg_rating }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{-- Semantic win/loss colors — fixed across themes (rule #5) --}}
                        <span class="font-bold {{ $row->win_ratio >= 50 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                            {{ $row->win_ratio }}%
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-emerald-700 dark:text-emerald-400">{{ $row->total_wins }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-red-700 dark:text-red-400">{{ $row->total_losses }}</span>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Games by country: All-time + Yearly --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- All-time (child component) --}}
        <livewire:countries.games-all-time-by-country />

        {{-- Yearly with year filter --}}
        <div class="rounded-xl border p-4
            border-travertine-300 bg-travertine-50
            dark:border-zinc-700 dark:bg-transparent">
            <div class="flex flex-col gap-3 mb-4">
                <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
                    🗓️ Games by country
                    <span class="font-sans normal-case tracking-normal text-travertine-400 dark:text-zinc-600">
                        ({{ $yearFilter === 'last12' ? 'last 12 months' : $yearFilter }})
                    </span>
                </p>
                {{-- Year filter buttons --}}
                <div class="flex items-center flex-wrap gap-1">
                    @foreach($this->availableYears as $year)
                    <button
                        wire:click="setYearFilter('{{ $year }}')"
                        class="px-2 py-1 text-xs rounded-md transition-colors
                            {{ $yearFilter === $year
                                ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                : 'text-travertine-600 hover:text-travertine-900 hover:bg-travertine-200 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700' }}">
                        {{ $year === 'last12' ? '12M' : $year }}
                    </button>
                    @endforeach
                </div>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8">#</flux:table.column>
                    <flux:table.column>Country</flux:table.column>
                    <flux:table.column>Games</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->gamesYearlyByCountry as $index => $row)
                    <flux:table.row :key="'yearly-'.$row->country_code" class="[&>td]:py-2">
                        <flux:table.cell>
                            <span class="text-sm font-mono text-travertine-400 dark:text-zinc-400">{{ $index + 1 }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                                     class="w-7 h-5 rounded-sm">
                                <span class="font-semibold text-[0.9375rem] text-travertine-900 dark:text-white">
                                    {{ $row->country }}
                                </span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{-- Purple is a brand/accent color for game counts — kept fixed (rule #5) --}}
                            <span class="font-bold text-purple-700 dark:text-purple-400">
                                {{ number_format($row->games_count) }}
                            </span>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

    </div>

    {{-- Country vs Country accordion --}}
    <div class="rounded-xl border p-4
        border-travertine-300 bg-travertine-50
        dark:border-zinc-700 dark:bg-transparent">
        <div class="flex items-center justify-between mb-4">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
                🌍 Country vs Country
            </p>
            <span class="text-xs text-travertine-400 dark:text-zinc-400">
                all games · top 10 countries by active player count
            </span>
        </div>

        <div class="flex flex-col gap-2">
            @php
                $countries = $this->topCountries;
                $matchups  = $this->countryMatchups->keyBy(fn($r) => $r->winner_country . '-' . $r->loser_country);
            @endphp
            @foreach($countries as $index => $country)
            @php
                $totalWins  = 0;
                $totalGames = 0;
                foreach ($countries as $opponent) {
                    if ($country->country_code === $opponent->country_code) continue;
                    $w = $matchups->get($country->country_code . '-' . $opponent->country_code)?->games ?? 0;
                    $l = $matchups->get($opponent->country_code . '-' . $country->country_code)?->games ?? 0;
                    $totalWins  += $w;
                    $totalGames += $w + $l;
                }
                $overallRatio = $totalGames > 0 ? round(($totalWins / $totalGames) * 100) : null;
            @endphp
            <div x-data="{ open: false }" class="rounded-lg border
                border-travertine-300 dark:border-zinc-700">

                {{-- Accordion trigger --}}
                <button
                    x-on:click="open = !open"
                    class="w-full flex items-center justify-between p-3 rounded-lg transition-colors
                        hover:bg-oxblood/5 dark:hover:bg-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono w-4 text-travertine-400 dark:text-zinc-400">{{ $index + 1 }}</span>
                        <img src="{{ asset('images/country_flags/' . strtolower($country->country_code) . '.svg') }}"
                             class="w-7 h-5 rounded-sm">
                        <span class="font-semibold text-[0.9375rem] text-travertine-900 dark:text-white">
                            {{ $country->country }}
                        </span>
                        <span class="text-xs text-travertine-400 dark:text-zinc-400">
                            {{ $country->player_count }} players
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($overallRatio !== null)
                            <span class="text-sm font-bold
                                {{ $overallRatio >= 50 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                {{ $overallRatio }}% vs others
                            </span>
                        @endif
                        <span class="text-xs text-travertine-400 dark:text-zinc-400">{{ $totalGames }} games</span>
                        <svg x-bind:class="open ? 'rotate-180' : ''"
                             class="w-4 h-4 transition-transform text-travertine-400 dark:text-zinc-400"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- Accordion body — matchup cards --}}
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
                            <div class="flex items-center justify-between rounded-lg px-3 py-2
                                bg-travertine-100 dark:bg-zinc-800">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($opponent->country_code) . '.svg') }}"
                                         class="w-7 h-5 rounded-sm">
                                    <div>
                                        <p class="text-sm text-travertine-600 dark:text-zinc-400">{{ $opponent->country }}</p>
                                        <p class="text-xs text-travertine-400 dark:text-zinc-500">{{ $wins }}W / {{ $losses }}L</p>
                                    </div>
                                </div>
                                @if($ratio !== null)
                                    <span class="font-bold text-sm
                                        {{ $ratio >= 50 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                        {{ $ratio }}%
                                    </span>
                                @else
                                    <span class="text-travertine-300 dark:text-zinc-400 text-sm">—</span>
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