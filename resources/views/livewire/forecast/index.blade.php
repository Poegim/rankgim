<div>
    {{-- ══════════════════════════════════════════════════════════════════
         Koprulu Forecast — top-level orchestrator view.
         Only renders: header, main tabs, child components, top-level modals.
         Every actual feature lives in its own Livewire component.
         ══════════════════════════════════════════════════════════════════ --}}



    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">🎯 Koprulu Forecast</flux:heading>
            <flux:subheading>Call the outcome. Earn the glory. Climb the standings.</flux:subheading>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @auth
                @if(auth()->user()->canManageGames())
                    @if($this->season)
                        {{-- Add Match button --}}
                        <button wire:click="$dispatch('open-add-match')"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                                bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100
                                dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 dark:hover:bg-emerald-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Match
                        </button>

                        {{-- Close Season button --}}
                        <button wire:click="closeSeason"
                            wire:confirm="Close this season and lock the final standings?"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                                bg-travertine-100 text-travertine-700 border border-travertine-300 hover:bg-travertine-200
                                dark:bg-zinc-700 dark:text-zinc-300 dark:border-zinc-600 dark:hover:bg-zinc-600">
                            Close Season
                        </button>
                    @else
                        {{-- Start New Season button --}}
                        <button wire:click="openSeasonModal"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                                bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100
                                dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 dark:hover:bg-amber-500/20">
                            Start New Season
                        </button>
                    @endif
                @endif
            @endauth
        </div>
    </div>

    {{-- No active season --}}
    @if(!$this->season)
        {{-- Show archive if past seasons exist, otherwise show "start season" --}}
        @php $hasPastSeasons = \App\Models\ForecastSeason::where('is_active', false)->exists(); @endphp

        @if($hasPastSeasons)
            <livewire:forecast.season-archive />
        @else
            <div class="text-center py-16 text-travertine-500 dark:text-zinc-500">
                <p class="text-lg">No active season</p>
                @if(auth()->user()?->canManageGames())
                    <button wire:click="openSeasonModal"
                        class="mt-3 text-sm text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                        Start the first season
                    </button>
                @endif
            </div>
        @endif
    @else

    {{-- ══════════════════════════════════════════════════════════════════
         MAIN TABS — Forecasts / Standings / History
         ══════════════════════════════════════════════════════════════════ --}}
    <div class="flex items-center gap-2 mb-6 flex-wrap">

        {{-- Forecasts tab --}}
        <button wire:click="switchTab('forecasts')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all border
                {{ $tab === 'forecasts'
                    ? 'bg-amber-50 border-amber-300 text-amber-800 shadow-sm dark:bg-amber-500/15 dark:border-amber-500/40 dark:text-amber-200 dark:shadow-lg dark:shadow-amber-500/10'
                    : 'bg-travertine-50 border-travertine-300 text-travertine-600 hover:border-travertine-400 hover:text-travertine-800 dark:bg-zinc-900/40 dark:border-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' }}">
            <span class="text-lg">🎯</span>
            <span>Forecasts</span>
        </button>

        {{-- Standings tab --}}
        <button wire:click="switchTab('standings')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all border
                {{ $tab === 'standings'
                    ? 'bg-purple-50 border-purple-300 text-purple-800 shadow-sm dark:bg-purple-500/15 dark:border-purple-500/40 dark:text-purple-200 dark:shadow-lg dark:shadow-purple-500/10'
                    : 'bg-travertine-50 border-travertine-300 text-travertine-600 hover:border-travertine-400 hover:text-travertine-800 dark:bg-zinc-900/40 dark:border-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' }}">
            <span class="text-lg">🏆</span>
            <span>Standings</span>
        </button>

        {{-- My History tab — auth only --}}
        @auth
        <button wire:click="switchTab('history')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all border
                {{ $tab === 'history'
                    ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-sm dark:bg-emerald-500/15 dark:border-emerald-500/40 dark:text-emerald-200 dark:shadow-lg dark:shadow-emerald-500/10'
                    : 'bg-travertine-50 border-travertine-300 text-travertine-600 hover:border-travertine-400 hover:text-travertine-800 dark:bg-zinc-900/40 dark:border-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' }}">
            <span class="text-lg">📜</span>
            <span>My History</span>
        </button>
        @endauth

        {{-- Archive tab — always visible if past seasons exist --}}
        @if(\App\Models\ForecastSeason::where('is_active', false)->exists())
        <button wire:click="switchTab('archive')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all border
                {{ $tab === 'archive'
                    ? 'bg-travertine-100 border-travertine-350 text-travertine-800 shadow-sm dark:bg-zinc-500/15 dark:border-zinc-500/40 dark:text-zinc-200 dark:shadow-lg dark:shadow-zinc-500/10'
                    : 'bg-travertine-50 border-travertine-300 text-travertine-600 hover:border-travertine-400 hover:text-travertine-800 dark:bg-zinc-900/40 dark:border-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' }}">
            <span class="text-lg">📦</span>
            <span>Archive</span>
        </button>
        @endif

        {{-- Season name label --}}
        <span class="ml-auto text-xs text-travertine-500 dark:text-zinc-600 hidden sm:block">
            Season: {{ $this->season->name }}
        </span>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         TAB CONTENT — one child Livewire component per tab.
         Each child is keyed so Livewire knows to tear down & replace cleanly
         when you switch between them.
         ══════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'forecasts')
        {{-- Stats bar only shows on Forecasts tab --}}
        <livewire:forecast.stats-bar :key="'stats-bar'" />

        {{-- Sub-toggle Open/Settled --}}
        <div class="flex items-center gap-1 border-b border-travertine-350 dark:border-zinc-800 mb-6 overflow-x-auto">

            {{-- Open sub-tab --}}
            <button wire:click="setView('open')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap
                    {{ $view === 'open'
                        ? 'text-travertine-900 dark:text-white'
                        : 'text-travertine-500 hover:text-travertine-700 dark:text-zinc-500 dark:hover:text-zinc-300' }}">
                <span class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ $view === 'open' ? 'bg-amber-500 animate-pulse dark:bg-amber-400' : 'bg-travertine-300 dark:bg-zinc-700' }}"></span>
                    Open
                </span>
                @if($view === 'open')
                    <span class="absolute -bottom-px left-0 right-0 h-0.5 bg-amber-500 dark:bg-amber-400"></span>
                @endif
            </button>

            {{-- Settled sub-tab --}}
            <button wire:click="setView('settled')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap
                    {{ $view === 'settled'
                        ? 'text-travertine-900 dark:text-white'
                        : 'text-travertine-500 hover:text-travertine-700 dark:text-zinc-500 dark:hover:text-zinc-300' }}">
                <span class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ $view === 'settled' ? 'bg-travertine-500 dark:bg-zinc-400' : 'bg-travertine-300 dark:bg-zinc-700' }}"></span>
                    Settled
                </span>
                @if($view === 'settled')
                    <span class="absolute -bottom-px left-0 right-0 h-0.5 bg-travertine-500 dark:bg-zinc-400"></span>
                @endif
            </button>
        </div>

        <livewire:forecast.match-list :view="$view" :key="'match-list-' . $view" />

    @elseif($tab === 'standings')
        <livewire:forecast.standings :key="'standings'" />

    @elseif($tab === 'history')
        @auth
            <livewire:forecast.history :key="'history'" />
        @else
            <div class="text-center py-16 text-travertine-500 dark:text-zinc-500">
                <p>Log in to see your forecast history.</p>
            </div>
        @endauth

    @elseif($tab === 'archive')
        <livewire:forecast.season-archive :key="'archive'" />
    @endif

    

    @endif {{-- end season check --}}

    {{-- ══════════════════════════════════════════════════════════════════
         TOP-LEVEL MODALS
         Kept here because they belong to Index's state (currency, season).
         ══════════════════════════════════════════════════════════════════ --}}

    {{-- Currency selection modal --}}
    @if($showCurrencyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        wire:click.self="$set('showCurrencyModal', false)">
        <div class="bg-travertine-50 border border-travertine-300 rounded-xl w-full max-w-md
                    dark:bg-zinc-900 dark:border-zinc-700">

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-travertine-300/70 dark:border-zinc-700/50">
                <h2 class="text-lg font-semibold text-travertine-900 dark:text-white">Pick your faction</h2>
                <button wire:click="$set('showCurrencyModal', false)"
                    class="text-travertine-500 hover:text-travertine-800 dark:text-zinc-400 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Currency options --}}
            <div class="p-5 space-y-2">
                <p class="text-xs text-travertine-500 dark:text-zinc-500 mb-4">
                    Each faction gives you a different edge. You can switch once per season.
                </p>
                @foreach(\App\Models\ForecastWallet::CURRENCIES as $key => $currency)
                    <button wire:click="$set('selectedCurrency', '{{ $key }}')"
                        class="w-full flex items-center gap-3 p-3 rounded-lg border transition-colors text-left
                            {{ $selectedCurrency === $key
                                ? 'bg-travertine-100 border-travertine-400 text-travertine-900 dark:bg-zinc-700 dark:border-zinc-500 dark:text-white'
                                : 'bg-travertine-75 border-travertine-200 text-travertine-700 hover:border-travertine-350 dark:bg-zinc-800/50 dark:border-zinc-700/50 dark:text-zinc-300 dark:hover:border-zinc-600' }}">
                        <span class="text-2xl">{{ $currency['icon'] }}</span>
                        <div>
                            <p class="text-sm font-semibold">{{ $currency['label'] }}</p>
                            <p class="text-xs text-travertine-500 dark:text-zinc-500 mt-0.5">{{ $currency['bonus'] }}</p>
                        </div>
                        @if($selectedCurrency === $key)
                            <span class="ml-auto text-emerald-700 text-xs dark:text-emerald-400">✓</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Modal footer --}}
            <div class="flex justify-end gap-3 px-5 py-3 border-t border-travertine-300/70 dark:border-zinc-700/50">
                <button wire:click="$set('showCurrencyModal', false)"
                    class="px-4 py-2 text-sm text-travertine-500 hover:text-travertine-800 transition-colors dark:text-zinc-400 dark:hover:text-white">
                    Cancel
                </button>
                <button wire:click="createWallet"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                        bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100
                        dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 dark:hover:bg-amber-500/20">
                    Lock it in
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- New season modal --}}
    @if($showSeasonModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        wire:click.self="$set('showSeasonModal', false)">
        <div class="bg-travertine-50 border border-travertine-300 rounded-xl w-full max-w-sm
                    dark:bg-zinc-900 dark:border-zinc-700">

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-travertine-300/70 dark:border-zinc-700/50">
                <h2 class="text-lg font-semibold text-travertine-900 dark:text-white">New Season</h2>
                <button wire:click="$set('showSeasonModal', false)"
                    class="text-travertine-500 hover:text-travertine-800 dark:text-zinc-400 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Season form fields --}}
            <div class="p-5 space-y-3">
                <div>
                    <label class="block text-xs text-travertine-500 dark:text-zinc-500 mb-1 uppercase tracking-wide">
                        Season name
                    </label>
                    <input type="text" wire:model="seasonName"
                        class="w-full rounded-lg px-3 py-2 text-sm focus:outline-none
                            bg-travertine-100 border border-travertine-300 text-travertine-900 focus:border-amber-600
                            dark:bg-zinc-800 dark:border-zinc-700 dark:text-white dark:focus:border-amber-500/50"
                        placeholder="e.g. Season 1 — 2026">
                    @error('seasonName')
                        <p class="text-xs text-red-700 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs text-travertine-500 dark:text-zinc-500 mb-1 uppercase tracking-wide">
                        Starts at
                    </label>
                    <input type="datetime-local" wire:model="seasonStartsAt"
                        class="w-full rounded-lg px-3 py-2 text-sm focus:outline-none
                            bg-travertine-100 border border-travertine-300 text-travertine-900 focus:border-amber-600
                            dark:bg-zinc-800 dark:border-zinc-700 dark:text-white dark:focus:border-amber-500/50">
                    @error('seasonStartsAt')
                        <p class="text-xs text-red-700 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Modal footer --}}
            <div class="flex justify-end gap-3 px-5 py-3 border-t border-travertine-300/70 dark:border-zinc-700/50">
                <button wire:click="$set('showSeasonModal', false)"
                    class="px-4 py-2 text-sm text-travertine-500 hover:text-travertine-800 transition-colors dark:text-zinc-400 dark:hover:text-white">
                    Cancel
                </button>
                <button wire:click="createSeason"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                        bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100
                        dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 dark:hover:bg-amber-500/20">
                    Start Season
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Listen for child-dispatched events asking us to open the currency modal
         (e.g. when user clicks "Make your call" without a wallet yet) --}}
    <div x-data x-on:request-currency-modal.window="$wire.openCurrencyModal()"
                x-on:reset-wallet.window="$wire.resetWallet()"
                x-on:open-currency-modal.window="$wire.openCurrencyModal()"></div>

</div>