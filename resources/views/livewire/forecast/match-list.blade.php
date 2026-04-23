<div>
    {{-- ══════════════════════════════════════════════════════════════════
         Match list — renders each match via the <x-forecast.match-card />
         blade component. Modals (bet/settle/add/edit/delete) live here
         because they drive actions on this component.
         ══════════════════════════════════════════════════════════════════ --}}

    @php
        $canManage = auth()->check() && auth()->user()->canManageGames();
    @endphp

    <div class="space-y-3">
        @forelse($this->matches as $match)
            @php
                $userPrediction = auth()->check()
                    ? $match->predictions->where('user_id', auth()->id())->first()
                    : null;
            @endphp

            <div wire:key="match-{{ $match->id }}">
                <x-forecast.match-card
                    :match="$match"
                    :user-prediction="$userPrediction"
                    :can-manage-games="$canManage" />
            </div>
        @empty
            <div class="text-center py-16 text-zinc-600">
                <p class="text-5xl mb-3">{{ $view === 'open' ? '🎮' : '📜' }}</p>
                <p class="text-sm">
                    No {{ $view === 'open' ? 'open matches right now' : 'settled matches yet' }}
                </p>
                @if($view === 'open' && $canManage)
                    <button wire:click="openAddMatchModal" class="mt-3 text-sm text-emerald-400 hover:text-emerald-300">
                        Add the first match
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Listen for parent-dispatched "open-add-match" so the header button
         in Index.blade.php can open this modal --}}
    <div x-data x-on:open-add-match.window="$wire.openAddMatchModal()"></div>

    {{-- ══════════════════════════════════════════════════════════════════
         Bet modal
         ══════════════════════════════════════════════════════════════════ --}}
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
                    <h2 class="text-lg font-semibold text-white">Make your call</h2>
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
                                <p class="text-xs font-mono text-zinc-500 mt-1">×{{ $bettingMatch->odds_a }}</p>
                            </button>

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
                                <p class="text-xs font-mono text-zinc-500 mt-1">×{{ $bettingMatch->odds_b }}</p>
                            </button>
                        </div>
                        @error('pickedPlayerId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        @error('pickedSide') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Points input --}}
                    <div>
                        <label class="block text-xs text-zinc-500 mb-2 uppercase tracking-wide">
                            How many points?
                            <span class="text-zinc-700 normal-case ml-1">{{ number_format($this->wallet->balance, 0) }} available</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="number" wire:model.live="stake" min="1" step="1"
                                class="flex-1 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                                placeholder="e.g. 10">
                            <button wire:click="$set('stake', {{ floor($this->wallet->balance) }})"
                                class="px-3 py-2 text-xs rounded-lg bg-zinc-700/80 text-zinc-300 hover:bg-zinc-700 transition-colors whitespace-nowrap">
                                Max
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
                            <span>If right →</span>
                            <span class="text-white font-mono font-bold">+{{ number_format($payout, 0) }} pts</span>
                            @if($bonus > 1)
                                <span class="text-amber-400 ml-auto">{{ $this->wallet->currencyIcon() }} perk active</span>
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
                        Confirm call
                    </button>
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         Settle modal
         ══════════════════════════════════════════════════════════════════ --}}
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

    {{-- ══════════════════════════════════════════════════════════════════
         Add / edit match modal (admin)
         ══════════════════════════════════════════════════════════════════ --}}
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

                {{-- FOREIGNER --}}
                @if($matchType === 'foreigner')
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
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Player A name</label>
                        <input type="text" wire:model="koreanAName"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-red-500/50"
                            placeholder="e.g. Flash">
                        @error('koreanAName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        <label class="block text-xs text-zinc-500 mt-2 mb-1 uppercase tracking-wide">Race A</label>
                        <select wire:model="koreanARace"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-red-500/50">
                            <option value="Unknown">Unknown</option>
                            <option value="Terran">Terran</option>
                            <option value="Zerg">Zerg</option>
                            <option value="Protoss">Protoss</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Player B name</label>
                        <input type="text" wire:model="koreanBName"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-red-500/50"
                            placeholder="e.g. Jaedong">
                        @error('koreanBName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        <label class="block text-xs text-zinc-500 mt-2 mb-1 uppercase tracking-wide">Race B</label>
                        <select wire:model="koreanBRace"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-red-500/50">
                            <option value="Unknown">Unknown</option>
                            <option value="Terran">Terran</option>
                            <option value="Zerg">Zerg</option>
                            <option value="Protoss">Protoss</option>
                        </select>
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

                {{-- Scheduled + locked --}}
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
                            Multiplier <span class="text-zinc-700 normal-case ml-1">applied to base odds</span>
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

    {{-- ══════════════════════════════════════════════════════════════════
         Delete confirmation modal
         ══════════════════════════════════════════════════════════════════ --}}
    @if($confirmingDeleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
        wire:click.self="$set('confirmingDeleteId', null)">
        <div class="bg-zinc-900 border border-zinc-700 rounded-xl p-6 w-full max-w-sm mx-4">
            <h3 class="text-base font-semibold text-white mb-1">Delete this match?</h3>
            <p class="text-sm text-zinc-500 mb-5">All pending forecasts will be automatically refunded.</p>
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