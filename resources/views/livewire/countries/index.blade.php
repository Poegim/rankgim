@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Countries</flux:heading>
        <flux:text>Country statistics · active players with 15+ games · top 10 countries by player count</flux:text>
    </div>

    {{-- Country compare picker --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🆚 Compare countries</p>
        <div class="flex items-end gap-4">
            <div class="flex-1">
                <flux:select wire:model="compareCountry1" label="Country 1">
                    <option value="">Select country...</option>
                    @foreach($this->allCountries as $c)
                        <option value="{{ $c->country_code }}">{{ $c->country }}</option>
                    @endforeach
                </flux:select>
            </div>
            <span class="text-zinc-400 font-bold pb-2">vs</span>
            <div class="flex-1">
                <flux:select wire:model="compareCountry2" label="Country 2">
                    <option value="">Select country...</option>
                    @foreach($this->allCountries as $c)
                        <option value="{{ $c->country_code }}">{{ $c->country }}</option>
                    @endforeach
                </flux:select>
            </div>
                <flux:button 
                    variant="primary" 
                    wire:click="goCompare"
                >
                    Compare
                </flux:button>
        </div>
    </div>



    {{-- Country stats table --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🌍 Country stats <span class="text-xs">(active players with 15+ games · all-time stats)</span></p>
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
                        <span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <a href="{{ route('rankings.index', ['filterCountryCode' => $row->country_code]) }}"
                           class="flex items-center gap-2 hover:underline">
                            <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                                 class="w-7 h-5 rounded-sm">
                            <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">{{ $row->country }}</span>
                        </a>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-zinc-500 dark:text-zinc-400">{{ $row->player_count }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="font-bold text-zinc-800 dark:text-white">{{ $row->avg_rating }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="{{ $row->win_ratio >= 50 ? 'text-green-500' : 'text-red-500' }} font-bold">
                            {{ $row->win_ratio }}%
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-green-500">{{ $row->total_wins }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-red-500">{{ $row->total_losses }}</span>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Games by country: All-time + Yearly --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- All-time --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🎮 Games by country <span class="text-xs">(all-time)</span></p>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8">#</flux:table.column>
                    <flux:table.column>Country</flux:table.column>
                    <flux:table.column>Games</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->gamesAllTimeByCountry as $index => $row)
                    <flux:table.row :key="'alltime-'.$row->country_code" class="[&>td]:py-2">
                        <flux:table.cell>
                            <span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                                     class="w-7 h-5 rounded-sm">
                                <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">{{ $row->country }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="font-bold text-indigo-500">{{ number_format($row->games_count) }}</span>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- Yearly with navigation --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">🗓️ Games by country
                    <span class="text-xs">({{ $yearFilter === 'last12' ? 'last 12 months' : $yearFilter }})</span>
                </p>
                <div class="flex items-center gap-1">
                    @foreach($this->availableYears as $year)
                        <button
                            wire:click="setYearFilter('{{ $year }}')"
                            class="px-2 py-1 text-xs rounded-md transition-colors
                                {{ $yearFilter === $year
                                    ? 'bg-indigo-500 text-white'
                                    : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700' }}"
                        >
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
                            <span class="text-zinc-400 font-mono text-sm">{{ $index + 1 }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                                     class="w-7 h-5 rounded-sm">
                                <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">{{ $row->country }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="font-bold text-purple-500">{{ number_format($row->games_count) }}</span>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

    </div>

    {{-- Country vs country accordion --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">🌍 Country vs Country</p>
            <span class="text-xs text-zinc-400">all games · top 10 countries by active player count</span>
        </div>
        <div class="flex flex-col gap-2">
            @php
                $countries = $this->topCountries;
                $matchups = $this->countryMatchups->keyBy(fn($r) => $r->winner_country . '-' . $r->loser_country);
            @endphp
            @foreach($countries as $index => $country)
            @php
                $totalWins   = 0;
                $totalGames  = 0;
                foreach ($countries as $opponent) {
                    if ($country->country_code === $opponent->country_code) continue;
                    $w = $matchups->get($country->country_code . '-' . $opponent->country_code)?->games ?? 0;
                    $l = $matchups->get($opponent->country_code . '-' . $country->country_code)?->games ?? 0;
                    $totalWins  += $w;
                    $totalGames += $w + $l;
                }
                $overallRatio = $totalGames > 0 ? round(($totalWins / $totalGames) * 100) : null;
            @endphp
            <div x-data="{ open: false }" class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <button
                    x-on:click="open = !open"
                    class="w-full flex items-center justify-between p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-zinc-400 font-mono w-4">{{ $index + 1 }}</span>
                        <img src="{{ asset('images/country_flags/' . strtolower($country->country_code) . '.svg') }}"
                             class="w-7 h-5 rounded-sm">
                        <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">{{ $country->country }}</span>
                        <span class="text-xs text-zinc-400">{{ $country->player_count }} players</span>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($overallRatio !== null)
                            <span class="text-sm font-bold {{ $overallRatio >= 50 ? 'text-green-500' : 'text-red-500' }}">
                                {{ $overallRatio }}% vs others
                            </span>
                        @endif
                        <span class="text-xs text-zinc-400">{{ $totalGames }} games</span>
                        <svg x-bind:class="open ? 'rotate-180' : ''" class="w-4 h-4 text-zinc-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>
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
                            <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-zinc-800 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('images/country_flags/' . strtolower($opponent->country_code) . '.svg') }}"
                                         class="w-7 h-5 rounded-sm">
                                    <div>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $opponent->country }}</p>
                                        <p class="text-xs text-zinc-400">{{ $wins }}W / {{ $losses }}L</p>
                                    </div>
                                </div>
                                @if($ratio !== null)
                                    <span class="font-bold text-sm {{ $ratio >= 50 ? 'text-green-500' : 'text-red-500' }}">{{ $ratio }}%</span>
                                @else
                                    <span class="text-zinc-400 text-sm">—</span>
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