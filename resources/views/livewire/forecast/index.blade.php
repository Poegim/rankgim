<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">⬡ Koprulu Forecast</flux:heading>
            <flux:subheading>Pick your winners. Spend your points. Prove you know the scene.</flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            @auth
                @if(auth()->user()->canManageGames())
                    @if($this->season)
                        <button wire:click="openAddMatchModal"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Match
                        </button>
                        <button wire:click="closeSeason"
                            wire:confirm="Close this season and lock the final ranking?"
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
    @if(! $this->season)
        <div class="text-center py-16 text-zinc-500">
            <p class="text-lg">No active season</p>
            @if(auth()->user()?->canManageGames())
                <button wire:click="openSeasonModal" class="mt-3 text-sm text-amber-400 hover:text-amber-300">
                    Start the first season
                </button>
            @endif
        </div>
    @else

    {{-- User wallet bar --}}
    @auth
        <div class="mb-6 rounded-xl border border-zinc-700/50 bg-zinc-900/60 px-4 py-3 flex items-center gap-4 flex-wrap">
            @if($this->wallet)
                @php $currency = \App\Models\ForecastWallet::CURRENCIES[$this->wallet->currency]; @endphp
                <div class="flex items-center gap-2">
                    <span class="text-xl">{{ $currency['icon'] }}</span>
                    <div>
                        <p class="text-xs text-zinc-500">{{ $currency['label'] }}</p>
                        <p class="text-sm font-mono font-bold text-white">{{ number_format($this->wallet->balance, 2) }} pts</p>
                    </div>
                </div>
                <p class="text-xs text-zinc-600 hidden sm:block">{{ $currency['bonus'] }}</p>
                @if($this->wallet->canReset())
                    <button wire:click="resetWallet"
                        wire:confirm="Reset your balance back to 50 points?"
                        class="ml-auto text-xs text-zinc-500 hover:text-amber-400 transition-colors">
                        ↺ Reset to 50
                    </button>
                @endif
            @else
                <p class="text-sm text-zinc-400">Pick your currency and join the season</p>
                <button wire:click="openCurrencyModal"
                    class="ml-auto px-3 py-1.5 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                    Join Season
                </button>
            @endif
        </div>
    @endauth

    {{-- View toggle --}}
    <div class="flex rounded-lg overflow-hidden border border-zinc-700 w-fit mb-6">
        <button wire:click="setView('open')"
            class="px-3 py-1.5 text-sm transition-colors {{ $view === 'open' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:text-white' }}">
            Open
        </button>
        <button wire:click="setView('settled')"
            class="px-3 py-1.5 text-sm transition-colors {{ $view === 'settled' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:text-white' }}">
            Settled
        </button>
    </div>

    {{-- Two-column layout: matches + ranking --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-6">

        {{-- Matches list --}}
        <div class="space-y-3">
            @forelse($this->matches as $match)
                @php
                    $userPrediction = auth()->check()
                        ? $match->predictions->where('user_id', auth()->id())->first()
                        : null;
                    $isLocked  = $match->isLocked();
                    $isSettled = $match->isSettled();
                    $isForeigner = $match->match_type === 'foreigner';

                    // Resolve display names for both sides
                    $nameA = $isForeigner ? ($match->playerA?->name ?? '?') : ($match->player_a_name ?? '?');
                    $nameB = $isForeigner ? ($match->playerB?->name ?? '?') : ($match->player_b_name ?? '?');

                    $raceA = $match->player_a_race;
                    $raceB = $match->player_b_race;

                    $raceColor = fn($race) => match($race) {
                        'Terran'  => 'text-blue-400',
                        'Zerg'    => 'text-purple-400',
                        'Protoss' => 'text-yellow-400',
                        default   => 'text-zinc-500',
                    };

                    $countryA = $isForeigner ? ($match->playerA?->country_code ?? null) : ($match->player_a_country ?? null);
                    $countryB = $isForeigner ? ($match->playerB?->country_code ?? null) : ($match->player_b_country ?? null);

                    // Resolve winner name for settled matches
                    $winnerName = null;
                    if ($isSettled) {
                        if ($match->winner_id) {
                            $winnerName = $match->winner?->name;
                        } elseif ($match->winner_side === 'a') {
                            $winnerName = $nameA;
                        } elseif ($match->winner_side === 'b') {
                            $winnerName = $nameB;
                        }
                    }

                    // Resolve user picked name
                    $pickedName = null;
                    if ($userPrediction) {
                        if ($userPrediction->pick_player_id) {
                            $pickedName = $userPrediction->pickedPlayer?->name;
                        } elseif ($userPrediction->pick_side === 'a') {
                            $pickedName = $nameA;
                        } elseif ($userPrediction->pick_side === 'b') {
                            $pickedName = $nameB;
                        }
                    }
                @endphp

                <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 p-4" wire:key="match-{{ $match->id }}">

                    {{-- Match header --}}
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs px-2 py-0.5 rounded-full border font-medium
                            {{ $match->match_type === 'foreigner' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' :
                               ($match->match_type === 'korean'   ? 'bg-red-500/10 text-red-400 border-red-500/20' :
                               ($match->match_type === 'national' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' :
                                                                    'bg-zinc-700/80 text-zinc-400 border-zinc-600')) }}">
                            {{ ucfirst($match->match_type) }}
                        </span>
                        @if($match->event)
                            <span class="text-xs text-zinc-500 truncate">{{ $match->event->name }}</span>
                        @endif
                        <span class="ml-auto text-xs font-mono text-zinc-600 shrink-0">
                            {{ $match->scheduled_at->format('d M H:i') }} CET
                        </span>
                    </div>

                    {{-- Players vs --}}
                    <div class="flex items-stretch gap-2 mb-3">

                        {{-- Player A --}}
                        <div class="flex-1 rounded-lg bg-zinc-800/50 px-3 py-2.5 text-center">
                            <div class="flex items-center justify-center gap-1.5 mb-1">
                                @if($countryA)
                                    <img src="{{ asset('images/country_flags/' . strtolower($countryA) . '.svg') }}"
                                        class="w-4 h-3 rounded-sm shrink-0">
                                @endif
                                <p class="font-bold text-white text-sm leading-tight">{{ $nameA }}</p>
                            </div>
                            @if($raceA !== 'Unknown')
                                <p class="text-xs {{ $raceColor($raceA) }}">{{ $raceA }}</p>
                            @endif
                            <p class="text-xs font-mono text-zinc-500 mt-1">{{ $match->odds_a }}x</p>
                        </div>

                        <div class="flex items-center text-zinc-700 font-bold text-xs self-center">VS</div>

                        {{-- Player B --}}
                        <div class="flex-1 rounded-lg bg-zinc-800/50 px-3 py-2.5 text-center">
                            <div class="flex items-center justify-center gap-1.5 mb-1">
                                @if($countryB)
                                    <img src="{{ asset('images/country_flags/' . strtolower($countryB) . '.svg') }}"
                                        class="w-4 h-3 rounded-sm shrink-0">
                                @endif
                                <p class="font-bold text-white text-sm leading-tight">{{ $nameB }}</p>
                            </div>
                            @if($raceB !== 'Unknown')
                                <p class="text-xs {{ $raceColor($raceB) }}">{{ $raceB }}</p>
                            @endif
                            <p class="text-xs font-mono text-zinc-500 mt-1">{{ $match->odds_b }}x</p>
                        </div>
                    </div>

                    {{-- Settled result --}}
                    @if($isSettled && $winnerName)
                        <div class="text-center mb-3">
                            <span class="text-xs px-3 py-1 rounded-full bg-zinc-800 text-zinc-300 border border-zinc-700">
                                🏆 {{ $winnerName }} won
                            </span>
                        </div>
                    @endif




                    {{-- User prediction --}}
                    @if($userPrediction && $pickedName)
                        <div class="mb-3 rounded-lg px-3 py-2 text-xs flex items-center gap-2 flex-wrap
                            {{ $userPrediction->result === 'won'      ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' :
                               ($userPrediction->result === 'lost'    ? 'bg-red-500/10 border border-red-500/20 text-red-400' :
                               ($userPrediction->result === 'refunded'? 'bg-zinc-800 border border-zinc-700 text-zinc-500' :
                                                                        'bg-zinc-800/50 border border-zinc-700/50 text-zinc-400')) }}">
                            <span>
                                @if($userPrediction->result === 'won') ✅
                                @elseif($userPrediction->result === 'lost') ❌
                                @elseif($userPrediction->result === 'refunded') ↩
                                @else ⏳
                                @endif
                            </span>
                            <span>
                                <strong>{{ $pickedName }}</strong>
                                · {{ number_format($userPrediction->stake, 2) }} pts
                                @if($userPrediction->bonus_multiplier > 1)
                                    <span class="opacity-70">(bonus {{ $userPrediction->bonus_multiplier }}x)</span>
                                @endif
                                · if right: <strong>{{ number_format($userPrediction->potential_payout, 2) }}</strong>
                            </span>
                            @if($userPrediction->result === 'won')
                                <span class="ml-auto font-bold font-mono">+{{ number_format($userPrediction->actual_payout, 2) }} pts</span>
                            @endif
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        @auth
                            @if(! $isSettled && ! $isLocked && ! $userPrediction)
                                <button wire:click="openBetModal({{ $match->id }})"
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                                    Make your pick →
                                </button>
                            @elseif(! $isSettled && $isLocked && ! $userPrediction)
                                <span class="text-xs text-zinc-600 italic">Picks locked</span>
                            @endif
                        @else
                            @if(! $isSettled && ! $isLocked)
                                <a href="{{ route('login') }}" class="text-xs text-amber-400 hover:text-amber-300">
                                    Log in to pick →
                                </a>
                            @endif
                        @endauth

                        @auth
                            @if(auth()->user()->canManageGames())
                                @if(! $isSettled && $isLocked)
                                    <button wire:click="openSettleModal({{ $match->id }})"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                                        Settle result
                                    </button>
                                @endif
                                @if(! $isSettled)
                                    <button wire:click="openEditMatchModal({{ $match->id }})"
                                        class="text-xs text-zinc-600 hover:text-zinc-300 transition-colors">Edit</button>
                                    <span class="text-zinc-800">·</span>
                                    <button wire:click="$set('confirmingDeleteId', {{ $match->id }})"
                                        class="text-xs text-zinc-600 hover:text-red-400 transition-colors">Delete</button>
                                @endif
                            @endif
                        @endauth

                        @if(! $isSettled && ! $isLocked)
                            <span class="ml-auto text-xs text-zinc-600 font-mono">
                                {{ $match->locked_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-zinc-600">
                    <p>No {{ $view === 'open' ? 'open' : 'settled' }} matches yet</p>
                    @if($view === 'open' && auth()->user()?->canManageGames())
                        <button wire:click="openAddMatchModal" class="mt-2 text-sm text-emerald-400 hover:text-emerald-300">
                            Add the first match
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Ranking sidebar --}}
        <div>
            <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 p-4 sticky top-4">
                <div class="flex items-baseline gap-2 mb-3">
                    <h3 class="text-sm font-semibold text-zinc-300">Leaderboard</h3>
                    <span class="text-xs text-zinc-600">{{ $this->season->name }}</span>
                </div>

                @forelse($this->ranking as $i => $wallet)
                    <div class="flex items-center gap-2 py-1.5 {{ $i > 0 ? 'border-t border-zinc-800/80' : '' }}">
                        <span class="text-xs font-mono text-zinc-700 w-4 shrink-0">{{ $i + 1 }}</span>
                        <span class="text-xs text-zinc-300 truncate flex-1">{{ $wallet->user->name }}</span>
                        <span class="text-xs font-mono shrink-0 {{ $wallet->profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $wallet->profit >= 0 ? '+' : '' }}{{ number_format($wallet->profit, 2) }}
                        </span>
                        <span class="text-sm shrink-0" title="{{ \App\Models\ForecastWallet::CURRENCIES[$wallet->currency]['label'] }}">
                            {{ \App\Models\ForecastWallet::CURRENCIES[$wallet->currency]['icon'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-zinc-600 text-center py-6">No picks yet — be the first!</p>
                @endforelse
            </div>
        </div>
    </div>

    @endif {{-- end season check --}}

    {{-- ── Modals ──────────────────────────────────────── --}}

    {{-- Currency selection modal --}}
    @if($showCurrencyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        wire:click.self="$set('showCurrencyModal', false)">
        <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-md">
            <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-700/50">
                <h2 class="text-lg font-semibold text-white">Pick your currency</h2>
                <button wire:click="$set('showCurrencyModal', false)" class="text-zinc-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-2">
                <p class="text-xs text-zinc-500 mb-4">Each currency gives you a different edge. You can switch once per season.</p>
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

    {{-- Bet / pick modal --}}
    @if($showBetModal && $bettingMatchId)
        @php $bettingMatch = $this->matches->find($bettingMatchId); @endphp
        @if($bettingMatch)
        @php
            $isForeigherBet = $bettingMatch->match_type === 'foreigner';
            $bNameA = $isForeigherBet ? ($bettingMatch->playerA?->name ?? '?') : ($bettingMatch->player_a_name ?? '?');
            $bNameB = $isForeigherBet ? ($bettingMatch->playerB?->name ?? '?') : ($bettingMatch->player_b_name ?? '?');
            $bRaceA = $bettingMatch->player_a_race;
            $bRaceB = $bettingMatch->player_b_race;
            $bCountryA = $isForeigherBet ? ($bettingMatch->playerA?->country_code ?? null) : ($bettingMatch->player_a_country ?? null);
            $bCountryB = $isForeigherBet ? ($bettingMatch->playerB?->country_code ?? null) : ($bettingMatch->player_b_country ?? null);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
            wire:click.self="$set('showBetModal', false)">
            <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-sm">
                <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-700/50">
                    <h2 class="text-lg font-semibold text-white">Make your pick</h2>
                    <button wire:click="$set('showBetModal', false)" class="text-zinc-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-5 space-y-4">

                    {{-- Pick winner --}}
                    <div>
                        <label class="block text-xs text-zinc-500 mb-2 uppercase tracking-wide">Who wins?</label>
                        <div class="grid grid-cols-2 gap-2">
                            {{-- Side A --}}
                            @php
                                $isPickedA = $isForeigherBet
                                    ? ($pickedPlayerId === $bettingMatch->player_a_id)
                                    : ($pickedSide === 'a');
                            @endphp
                            <button
                                @if($isForeigherBet)
                                    wire:click="$set('pickedPlayerId', {{ $bettingMatch->player_a_id }})"
                                @else
                                    wire:click="$set('pickedSide', 'a')"
                                @endif
                                class="p-3 rounded-lg border text-center transition-colors
                                    {{ $isPickedA
                                        ? 'bg-amber-500/10 border-amber-500/40 text-white'
                                        : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-300 hover:border-zinc-500' }}">
                                <div class="flex items-center justify-center gap-1.5 mb-1">
                                    @if($bCountryA)
                                        <img src="{{ asset('images/country_flags/' . strtolower($bCountryA) . '.svg') }}"
                                            class="w-4 h-3 rounded-sm">
                                    @endif
                                    <p class="font-bold text-sm">{{ $bNameA }}</p>
                                </div>
                                @if($bRaceA !== 'Unknown')
                                    <p class="text-xs {{ match($bRaceA) { 'Terran' => 'text-blue-400', 'Zerg' => 'text-purple-400', 'Protoss' => 'text-yellow-400', default => 'text-zinc-500' } }}">
                                        {{ $bRaceA }}
                                    </p>
                                @endif
                                <p class="text-xs font-mono text-zinc-500 mt-1">{{ $bettingMatch->odds_a }}x</p>
                            </button>

                            {{-- Side B --}}
                            @php
                                $isPickedB = $isForeigherBet
                                    ? ($pickedPlayerId === $bettingMatch->player_b_id)
                                    : ($pickedSide === 'b');
                            @endphp
                            <button
                                @if($isForeigherBet)
                                    wire:click="$set('pickedPlayerId', {{ $bettingMatch->player_b_id }})"
                                @else
                                    wire:click="$set('pickedSide', 'b')"
                                @endif
                                class="p-3 rounded-lg border text-center transition-colors
                                    {{ $isPickedB
                                        ? 'bg-amber-500/10 border-amber-500/40 text-white'
                                        : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-300 hover:border-zinc-500' }}">
                                <div class="flex items-center justify-center gap-1.5 mb-1">
                                    @if($bCountryB)
                                        <img src="{{ asset('images/country_flags/' . strtolower($bCountryB) . '.svg') }}"
                                            class="w-4 h-3 rounded-sm">
                                    @endif
                                    <p class="font-bold text-sm">{{ $bNameB }}</p>
                                </div>
                                @if($bRaceB !== 'Unknown')
                                    <p class="text-xs {{ match($bRaceB) { 'Terran' => 'text-blue-400', 'Zerg' => 'text-purple-400', 'Protoss' => 'text-yellow-400', default => 'text-zinc-500' } }}">
                                        {{ $bRaceB }}
                                    </p>
                                @endif
                                <p class="text-xs font-mono text-zinc-500 mt-1">{{ $bettingMatch->odds_b }}x</p>
                            </button>
                        </div>
                        @error('pickedPlayerId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        @error('pickedSide') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Points input --}}
                    <div>
                        <label class="block text-xs text-zinc-500 mb-2 uppercase tracking-wide">
                            How many points?
                            <span class="text-zinc-700 normal-case ml-1">{{ number_format($this->wallet->balance, 2) }} available</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="number" wire:model.live="stake" min="1" step="1"
                                class="flex-1 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                                placeholder="e.g. 10">
                            <button wire:click="$set('stake', {{ floor($this->wallet->balance) }})"
                                class="px-3 py-2 text-xs rounded-lg bg-zinc-700/80 text-zinc-300 hover:bg-zinc-700 transition-colors whitespace-nowrap">
                                All in
                            </button>
                        </div>
                        @error('stake') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Payout preview --}}
                    @php
                        $hasPick = $isForeigherBet ? (bool) $pickedPlayerId : (bool) $pickedSide;
                        $previewOdds = null;
                        $previewRace = null;
                        $previewOther = null;
                        if ($hasPick) {
                            $sideA = $isForeigherBet
                                ? ($pickedPlayerId === $bettingMatch->player_a_id)
                                : ($pickedSide === 'a');
                            $previewOdds  = ($sideA ? (float)$bettingMatch->odds_a : (float)$bettingMatch->odds_b) * (float)$bettingMatch->multiplier;
                            $previewOther = ($sideA ? (float)$bettingMatch->odds_b : (float)$bettingMatch->odds_a) * (float)$bettingMatch->multiplier;
                            $previewRace  = $sideA ? $bettingMatch->player_a_race : $bettingMatch->player_b_race;
                        }
                    @endphp
                    @if($stake && $hasPick && is_numeric($stake) && $previewOdds)
                        @php
                            $bonus = \App\Models\ForecastPrediction::resolveBonusMultiplier(
                                $this->wallet->currency, $previewRace, $previewOdds, $previewOther
                            );
                            $payout = round((float)$stake * $previewOdds * $bonus, 2);
                        @endphp
                        <div class="rounded-lg bg-zinc-800/40 border border-zinc-700/40 px-3 py-2 text-xs text-zinc-400 flex items-center gap-2">
                            <span>If correct →</span>
                            <span class="text-white font-mono font-bold">+{{ number_format($payout, 2) }} pts</span>
                            @if($bonus > 1)
                                <span class="text-amber-400 ml-auto">{{ $this->wallet->currencyIcon() }} bonus active</span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-3 px-5 py-3 border-t border-zinc-700/50">
                    <button wire:click="$set('showBetModal', false)" class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button wire:click="placeBet"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                        Confirm pick
                    </button>
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- Settle modal --}}
    @if($settlingMatchId)
        @php $settlingMatch = $this->matches->find($settlingMatchId); @endphp
        @if($settlingMatch)
        @php
            $sIsForeigner = $settlingMatch->match_type === 'foreigner';
            $sNameA = $sIsForeigner ? ($settlingMatch->playerA?->name ?? '?') : ($settlingMatch->player_a_name ?? '?');
            $sNameB = $sIsForeigner ? ($settlingMatch->playerB?->name ?? '?') : ($settlingMatch->player_b_name ?? '?');
            $sCountryA = $sIsForeigner ? ($settlingMatch->playerA?->country_code ?? null) : ($settlingMatch->player_a_country ?? null);
            $sCountryB = $sIsForeigner ? ($settlingMatch->playerB?->country_code ?? null) : ($settlingMatch->player_b_country ?? null);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
            wire:click.self="$set('settlingMatchId', null)">
            <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-sm">
                <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-700/50">
                    <h2 class="text-lg font-semibold text-white">Who won?</h2>
                    <button wire:click="$set('settlingMatchId', null)" class="text-zinc-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="$set('winnerSide', 'a')"
                            class="p-3 rounded-lg border text-center transition-colors
                                {{ $winnerSide === 'a'
                                    ? 'bg-emerald-500/10 border-emerald-500/30 text-white'
                                    : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-300 hover:border-zinc-500' }}">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($sCountryA)
                                    <img src="{{ asset('images/country_flags/' . strtolower($sCountryA) . '.svg') }}"
                                        class="w-4 h-3 rounded-sm">
                                @endif
                                <p class="font-bold text-sm">{{ $sNameA }}</p>
                            </div>
                        </button>
                        <button wire:click="$set('winnerSide', 'b')"
                            class="p-3 rounded-lg border text-center transition-colors
                                {{ $winnerSide === 'b'
                                    ? 'bg-emerald-500/10 border-emerald-500/30 text-white'
                                    : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-300 hover:border-zinc-500' }}">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($sCountryB)
                                    <img src="{{ asset('images/country_flags/' . strtolower($sCountryB) . '.svg') }}"
                                        class="w-4 h-3 rounded-sm">
                                @endif
                                <p class="font-bold text-sm">{{ $sNameB }}</p>
                            </div>
                        </button>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-5 py-3 border-t border-zinc-700/50">
                    <button wire:click="$set('settlingMatchId', null)" class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button wire:click="settleMatch" @if(! $winnerSide) disabled @endif
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- Add / Edit match modal --}}
    @if($showMatchModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        wire:click.self="$set('showMatchModal', false)">
        <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-lg overflow-y-auto max-h-[90vh]">
            <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-700/50">
                <h2 class="text-lg font-semibold text-white">{{ $editingMatchId ? 'Edit Match' : 'Add Match' }}</h2>
                <button wire:click="$set('showMatchModal', false)" class="text-zinc-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">

                {{-- Match type --}}
                <div>
                    <label class="block text-xs text-zinc-500 mb-1.5 uppercase tracking-wide">Match type</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach(\App\Models\ForecastMatch::MATCH_TYPES as $type)
                            <button type="button" wire:click="$set('matchType', '{{ $type }}')"
                                class="px-2 py-1.5 rounded-lg border text-xs font-medium transition-colors
                                    {{ $matchType === $type
                                        ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
                                        : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-400 hover:border-zinc-600' }}">
                                {{ ucfirst($type) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Player fields — dynamic per match type --}}

                {{-- FOREIGNER --}}
                @if($matchType === 'foreigner')
                    {{-- Player A --}}
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Player A</label>
                        @if($playerAName)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-zinc-800/50 border border-zinc-700/50">
                                <span class="text-sm text-white font-medium">{{ $playerAName }}</span>
                                <button wire:click="$set('playerAId', null); $set('playerAName', '')"
                                    class="ml-auto text-xs text-zinc-600 hover:text-red-400">change</button>
                            </div>
                        @else
                            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                                <input type="text" wire:model.live.debounce.200ms="playerASearch"
                                    x-on:focus="open = true" x-on:input="open = true"
                                    autocomplete="off" placeholder="Search player…"
                                    class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
                                @if(strlen($playerASearch) >= 2)
                                    <div x-show="open" class="absolute z-50 w-full mt-1 rounded-lg bg-zinc-900 border border-zinc-700 shadow-lg overflow-hidden max-h-40 overflow-y-auto">
                                        @forelse($this->playerAResults as $player)
                                            <button type="button"
                                                wire:click="selectPlayerA({{ $player->id }}, '{{ addslashes($player->name) }}')"
                                                x-on:click="open = false"
                                                class="flex items-center gap-2 w-full px-3 py-1.5 text-left hover:bg-zinc-800 transition-colors">
                                                <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                                    class="w-4 h-3 rounded-sm shrink-0">
                                                <span class="text-sm text-white">{{ $player->name }}</span>
                                                <span class="text-xs ml-auto
                                                    {{ $player->race === 'Terran' ? 'text-blue-400' : ($player->race === 'Zerg' ? 'text-purple-400' : 'text-yellow-400') }}">
                                                    {{ $player->race }}
                                                </span>
                                            </button>
                                        @empty
                                            <div class="px-3 py-2 text-sm text-zinc-500">No players found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @endif
                        @error('playerAId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Player B --}}
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Player B</label>
                        @if($playerBName)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-zinc-800/50 border border-zinc-700/50">
                                <span class="text-sm text-white font-medium">{{ $playerBName }}</span>
                                <button wire:click="$set('playerBId', null); $set('playerBName', '')"
                                    class="ml-auto text-xs text-zinc-600 hover:text-red-400">change</button>
                            </div>
                        @else
                            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                                <input type="text" wire:model.live.debounce.200ms="playerBSearch"
                                    x-on:focus="open = true" x-on:input="open = true"
                                    autocomplete="off" placeholder="Search player…"
                                    class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
                                @if(strlen($playerBSearch) >= 2)
                                    <div x-show="open" class="absolute z-50 w-full mt-1 rounded-lg bg-zinc-900 border border-zinc-700 shadow-lg overflow-hidden max-h-40 overflow-y-auto">
                                        @forelse($this->playerBResults as $player)
                                            <button type="button"
                                                wire:click="selectPlayerB({{ $player->id }}, '{{ addslashes($player->name) }}')"
                                                x-on:click="open = false"
                                                class="flex items-center gap-2 w-full px-3 py-1.5 text-left hover:bg-zinc-800 transition-colors">
                                                <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                                    class="w-4 h-3 rounded-sm shrink-0">
                                                <span class="text-sm text-white">{{ $player->name }}</span>
                                                <span class="text-xs ml-auto
                                                    {{ $player->race === 'Terran' ? 'text-blue-400' : ($player->race === 'Zerg' ? 'text-purple-400' : 'text-yellow-400') }}">
                                                    {{ $player->race }}
                                                </span>
                                            </button>
                                        @empty
                                            <div class="px-3 py-2 text-sm text-zinc-500">No players found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @endif
                        @error('playerBId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- KOREAN --}}
                @if($matchType === 'korean')
                    {{-- Korean A --}}
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Player A</label>
                        @if($koreanAName)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-zinc-800/50 border border-zinc-700/50">
                                <span class="text-sm text-white font-medium">{{ $koreanAName }}</span>
                                <span class="text-xs text-red-400 ml-1">{{ $koreanARace }}</span>
                                <button wire:click="$set('koreanAName', ''); $set('koreanARace', 'Unknown')"
                                    class="ml-auto text-xs text-zinc-600 hover:text-red-400">change</button>
                            </div>
                        @else
                            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                                <input type="text" wire:model.live="koreanASearch"
                                    x-on:focus="open = true" x-on:input="open = true"
                                    autocomplete="off" placeholder="Search Korean player…"
                                    class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-red-500/50">
                                @if(strlen($koreanASearch) >= 2)
                                    <div x-show="open" class="absolute z-50 w-full mt-1 rounded-lg bg-zinc-900 border border-zinc-700 shadow-lg overflow-hidden max-h-40 overflow-y-auto">
                                        @forelse($this->koreanAResults as $guest)
                                            <button type="button"
                                                wire:click="selectKoreanA('{{ addslashes($guest['name']) }}', '{{ $guest['race'] }}')"
                                                x-on:click="open = false"
                                                class="flex items-center gap-2 w-full px-3 py-1.5 text-left hover:bg-zinc-800 transition-colors">
                                                <img src="{{ asset('images/country_flags/' . strtolower($guest['country_code']) . '.svg') }}"
                                                    class="w-4 h-3 rounded-sm shrink-0">
                                                <span class="text-sm text-white">{{ $guest['name'] }}</span>
                                                <span class="text-xs ml-auto
                                                    {{ $guest['race'] === 'Terran' ? 'text-blue-400' : ($guest['race'] === 'Zerg' ? 'text-purple-400' : 'text-yellow-400') }}">
                                                    {{ $guest['race'] }}
                                                </span>
                                            </button>
                                        @empty
                                            <div class="px-3 py-2 text-sm text-zinc-500">No players found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @endif
                        @error('koreanAName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Korean B --}}
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Player B</label>
                        @if($koreanBName)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-zinc-800/50 border border-zinc-700/50">
                                <span class="text-sm text-white font-medium">{{ $koreanBName }}</span>
                                <span class="text-xs text-red-400 ml-1">{{ $koreanBRace }}</span>
                                <button wire:click="$set('koreanBName', ''); $set('koreanBRace', 'Unknown')"
                                    class="ml-auto text-xs text-zinc-600 hover:text-red-400">change</button>
                            </div>
                        @else
                            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                                <input type="text" wire:model.live="koreanBSearch"
                                    x-on:focus="open = true" x-on:input="open = true"
                                    autocomplete="off" placeholder="Search Korean player…"
                                    class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-red-500/50">
                                @if(strlen($koreanBSearch) >= 2)
                                    <div x-show="open" class="absolute z-50 w-full mt-1 rounded-lg bg-zinc-900 border border-zinc-700 shadow-lg overflow-hidden max-h-40 overflow-y-auto">
                                        @forelse($this->koreanBResults as $guest)
                                            <button type="button"
                                                wire:click="selectKoreanB('{{ addslashes($guest['name']) }}', '{{ $guest['race'] }}')"
                                                x-on:click="open = false"
                                                class="flex items-center gap-2 w-full px-3 py-1.5 text-left hover:bg-zinc-800 transition-colors">
                                                <img src="{{ asset('images/country_flags/' . strtolower($guest['country_code']) . '.svg') }}"
                                                    class="w-4 h-3 rounded-sm shrink-0">
                                                <span class="text-sm text-white">{{ $guest['name'] }}</span>
                                                <span class="text-xs ml-auto
                                                    {{ $guest['race'] === 'Terran' ? 'text-blue-400' : ($guest['race'] === 'Zerg' ? 'text-purple-400' : 'text-yellow-400') }}">
                                                    {{ $guest['race'] }}
                                                </span>
                                            </button>
                                        @empty
                                            <div class="px-3 py-2 text-sm text-zinc-500">No players found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @endif
                        @error('koreanBName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- CLAN --}}
                @if($matchType === 'clan')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Clan A</label>
                            <input type="text" wire:model="clanAName"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50"
                                placeholder="e.g. PogChamp">
                            @error('clanAName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Clan B</label>
                            <input type="text" wire:model="clanBName"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50"
                                placeholder="e.g. Grubby">
                            @error('clanBName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                {{-- NATIONAL --}}
                @if($matchType === 'national')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Country A</label>
                            <select wire:model="nationalACode"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
                                <option value="">Select…</option>
                                @foreach($this->countries as $country)
                                    <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                                @endforeach
                            </select>
                            @error('nationalACode') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Country B</label>
                            <select wire:model="nationalBCode"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
                                <option value="">Select…</option>
                                @foreach($this->countries as $country)
                                    <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                                @endforeach
                            </select>
                            @error('nationalBCode') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                {{-- Scheduled at + Locked at --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Match time</label>
                        <input type="datetime-local" wire:model="scheduledAt"
                            x-on:change="
                                const d = new Date($event.target.value);
                                d.setHours(d.getHours() - 1);
                                const pad = n => String(n).padStart(2, '0');
                                const locked = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
                                $wire.set('lockedAt', locked);
                            "
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
                        @error('scheduledAt') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Picks close</label>
                        <input type="datetime-local" wire:model="lockedAt"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
                        @error('lockedAt') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Multiplier (non-foreigner only) --}}
                @if($matchType !== 'foreigner')
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">
                            Multiplier
                            <span class="text-zinc-700 normal-case ml-1">applied to base odds</span>
                        </label>
                        <input type="number" wire:model="multiplier" min="0.1" step="0.1"
                            class="w-28 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
                        @error('multiplier') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

            </div>
            <div class="flex justify-end gap-3 px-5 py-3 border-t border-zinc-700/50">
                <button wire:click="$set('showMatchModal', false)" class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                    Cancel
                </button>
                <button wire:click="saveMatch"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                    {{ $editingMatchId ? 'Save changes' : 'Add match' }}
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

    {{-- Delete confirmation --}}
    @if($confirmingDeleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
        wire:click.self="$set('confirmingDeleteId', null)">
        <div class="bg-zinc-900 border border-zinc-700 rounded-xl p-6 w-full max-w-sm mx-4">
            <h3 class="text-base font-semibold text-white mb-1">Delete this match?</h3>
            <p class="text-sm text-zinc-500 mb-5">All pending picks will be automatically refunded.</p>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('confirmingDeleteId', null)" class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                    Cancel
                </button>
                <button wire:click="deleteMatch"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>
    @endif

</div>