<div>
    @if($this->wallet)
        @php
            $currency = \App\Models\ForecastWallet::CURRENCIES[$this->wallet->currency];
            $rankInfo = $this->rankInfo;
            $profit   = (float) $this->wallet->profit();
        @endphp

        <div class="mb-6 rounded-2xl border border-zinc-700/40 bg-gradient-to-br from-zinc-900 via-zinc-900 to-zinc-800/60 overflow-hidden">
            <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-zinc-700/40">

                {{-- Faction --}}
                <div class="p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-zinc-800/80 border border-zinc-700/60 flex items-center justify-center text-2xl shrink-0">
                        {{ $currency['icon'] }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-zinc-500">Your Faction</p>
                        <p class="text-sm font-semibold text-white truncate">{{ $currency['label'] }}</p>
                    </div>
                </div>

                {{-- Energy (balance) --}}
                <div class="p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-xl shrink-0">
                        💠
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-zinc-500">Energy</p>
                        <p class="text-base font-mono font-bold text-white">{{ number_format($this->wallet->balance, 0) }}</p>
                    </div>
                </div>

                {{-- Rank --}}
                <div class="p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-xl shrink-0">
                        @if($rankInfo['rank'] === 1) 🥇
                        @elseif($rankInfo['rank'] === 2) 🥈
                        @elseif($rankInfo['rank'] === 3) 🥉
                        @else 🎖
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-zinc-500">Rank</p>
                        <p class="text-base font-mono font-bold text-white">
                            {{ $rankInfo['rank'] ? '#' . $rankInfo['rank'] : '—' }}
                            <span class="text-xs font-normal text-zinc-500">of {{ $rankInfo['total'] }}</span>
                        </p>
                    </div>
                </div>

                {{-- Net profit --}}
                <div class="p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl border flex items-center justify-center text-xl shrink-0
                        {{ $profit >= 0 ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-red-500/10 border-red-500/20' }}">
                        {{ $profit >= 0 ? '📈' : '📉' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Net</p>
                            @if($this->wallet->canReset())
                                <button wire:click="resetWallet"
                                    wire:confirm="Reset your balance back to 50 points?"
                                    class="text-[10px] text-zinc-500 hover:text-amber-400 transition-colors"
                                    title="Reset to 50">↺</button>
                            @endif
                        </div>
                        <p class="text-base font-mono font-bold {{ $profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 0) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-4 py-2 border-t border-zinc-700/40 bg-zinc-900/40">
                <p class="text-xs text-zinc-500">
                    <span class="text-zinc-400">Faction perk:</span> {{ $currency['bonus'] }}
                </p>
            </div>
        </div>
    @else
        {{-- User doesn't have a wallet yet --}}
        @auth
        <div class="mb-6 rounded-2xl border border-amber-500/20 bg-gradient-to-r from-amber-500/5 via-zinc-900 to-zinc-900 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-2xl shrink-0">
                ⚔️
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white">Pick your faction to enter the season</p>
                <p class="text-xs text-zinc-500 mt-0.5">Each faction has its own perk. 50 starting points.</p>
            </div>
            <button wire:click="openCurrencyModal"
                class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                Join Season →
            </button>
        </div>
        @endauth
    @endif
</div>