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
                        <button wire:click="$dispatch('open-add-match')"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Match
                        </button>
                        <button wire:click="closeSeason"
                            wire:confirm="Close this season and lock the final standings?"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg bg-zinc-700 text-zinc-300 border border-zinc-600 hover:bg-zinc-600 transition-colors">
                            Close Season
                        </button>
                    @else
                        <button wire:click="openSeasonModal"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
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
            <div class="text-center py-16 text-zinc-500">
                <p class="text-lg">No active season</p>
                @if(auth()->user()?->canManageGames())
                    <button wire:click="openSeasonModal" class="mt-3 text-sm text-amber-400 hover:text-amber-300">
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
        <button wire:click="switchTab('forecasts')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all border
                {{ $tab === 'forecasts'
                    ? 'bg-amber-500/15 border-amber-500/40 text-amber-200 shadow-lg shadow-amber-500/10'
                    : 'bg-zinc-900/40 border-zinc-800 text-zinc-400 hover:border-zinc-600 hover:text-zinc-200' }}">
            <span class="text-lg">🎯</span>
            <span>Forecasts</span>
        </button>

        <button wire:click="switchTab('standings')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all border
                {{ $tab === 'standings'
                    ? 'bg-purple-500/15 border-purple-500/40 text-purple-200 shadow-lg shadow-purple-500/10'
                    : 'bg-zinc-900/40 border-zinc-800 text-zinc-400 hover:border-zinc-600 hover:text-zinc-200' }}">
            <span class="text-lg">🏆</span>
            <span>Standings</span>
        </button>

        @auth
        <button wire:click="switchTab('history')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all border
                {{ $tab === 'history'
                    ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-200 shadow-lg shadow-emerald-500/10'
                    : 'bg-zinc-900/40 border-zinc-800 text-zinc-400 hover:border-zinc-600 hover:text-zinc-200' }}">
            <span class="text-lg">📜</span>
            <span>My History</span>
        </button>
        @endauth

        {{-- Archive tab — always visible if past seasons exist --}}
        @if(\App\Models\ForecastSeason::where('is_active', false)->exists())
        <button wire:click="switchTab('archive')"
            class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all border
                {{ $tab === 'archive'
                    ? 'bg-zinc-500/15 border-zinc-500/40 text-zinc-200 shadow-lg shadow-zinc-500/10'
                    : 'bg-zinc-900/40 border-zinc-800 text-zinc-400 hover:border-zinc-600 hover:text-zinc-200' }}">
            <span class="text-lg">📦</span>
            <span>Archive</span>
        </button>
        @endif

        <span class="ml-auto text-xs text-zinc-600 hidden sm:block">
            {{ $this->season->name }}
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

        {{-- Sub-toggle Open/Settled — lives in Index because it switches $view
             which gets passed into MatchList as a prop --}}
        <div class="flex items-center gap-1 border-b border-zinc-800 mb-6 overflow-x-auto">
            <button wire:click="setView('open')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap
                    {{ $view === 'open' ? 'text-white' : 'text-zinc-500 hover:text-zinc-300' }}">
                <span class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ $view === 'open' ? 'bg-amber-400 animate-pulse' : 'bg-zinc-700' }}"></span>
                    Open
                </span>
                @if($view === 'open')
                    <span class="absolute -bottom-px left-0 right-0 h-0.5 bg-amber-400"></span>
                @endif
            </button>

            <button wire:click="setView('settled')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap
                    {{ $view === 'settled' ? 'text-white' : 'text-zinc-500 hover:text-zinc-300' }}">
                <span class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ $view === 'settled' ? 'bg-zinc-400' : 'bg-zinc-700' }}"></span>
                    Settled
                </span>
                @if($view === 'settled')
                    <span class="absolute -bottom-px left-0 right-0 h-0.5 bg-zinc-400"></span>
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
            <div class="text-center py-16 text-zinc-500">
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
        <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-md">
            <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-700/50">
                <h2 class="text-lg font-semibold text-white">Pick your faction</h2>
                <button wire:click="$set('showCurrencyModal', false)" class="text-zinc-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-2">
                <p class="text-xs text-zinc-500 mb-4">Each faction gives you a different edge. You can switch once per season.</p>
                @foreach(\App\Models\ForecastWallet::CURRENCIES as $key => $currency)
                    <button wire:click="$set('selectedCurrency', '{{ $key }}')"
                        class="w-full flex items-center gap-3 p-3 rounded-lg border transition-colors text-left
                            {{ $selectedCurrency === $key
                                ? 'bg-zinc-700 border-zinc-500 text-white'
                                : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-300 hover:border-zinc-600' }}">
                        <span class="text-2xl">{{ $currency['icon'] }}</span>
                        <div>
                            <p class="text-sm font-semibold">{{ $currency['label'] }}</p>
                            <p class="text-xs text-zinc-500 mt-0.5">{{ $currency['bonus'] }}</p>
                        </div>
                        @if($selectedCurrency === $key)
                            <span class="ml-auto text-emerald-400 text-xs">✓</span>
                        @endif
                    </button>
                @endforeach
            </div>
            <div class="flex justify-end gap-3 px-5 py-3 border-t border-zinc-700/50">
                <button wire:click="$set('showCurrencyModal', false)" class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                    Cancel
                </button>
                <button wire:click="createWallet"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
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
        <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-700/50">
                <h2 class="text-lg font-semibold text-white">New Season</h2>
                <button wire:click="$set('showSeasonModal', false)" class="text-zinc-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-3">
                <div>
                    <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Season name</label>
                    <input type="text" wire:model="seasonName"
                        class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                        placeholder="e.g. Season 1 — 2026">
                    @error('seasonName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Starts at</label>
                    <input type="datetime-local" wire:model="seasonStartsAt"
                        class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50">
                    @error('seasonStartsAt') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-3 border-t border-zinc-700/50">
                <button wire:click="$set('showSeasonModal', false)" class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                    Cancel
                </button>
                <button wire:click="createSeason"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
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