<div>
    @if($this->wallet)
        @php
            $currency = \App\Models\ForecastWallet::CURRENCIES[$this->wallet->currency];
            $rankInfo = $this->rankInfo;
            $profit   = (float) $this->wallet->profit();
        @endphp

        <div class="mb-6 rounded-2xl overflow-hidden
            border border-travertine-300 bg-gradient-to-br from-travertine-75 via-travertine-50 to-travertine-50
            dark:border-zinc-700/40 dark:bg-gradient-to-br dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-800/60">
            <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-travertine-300 dark:divide-zinc-700/40">

                {{-- Faction --}}
                <div class="p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl shrink-0
                        bg-travertine-100 border border-travertine-300
                        dark:bg-zinc-800/80 dark:border-zinc-700/60">
                        {{ $currency['icon'] }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500">Your Faction</p>
                        <p class="text-sm font-semibold text-travertine-900 dark:text-white truncate">{{ $currency['label'] }}</p>
                    </div>
                </div>

                {{-- Energy (balance) --}}
                <div class="p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0
                        bg-amber-50 border border-amber-200
                        dark:bg-amber-500/10 dark:border-amber-500/20">
                        💠
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500">Energy</p>
                        <p class="text-base font-mono font-bold text-travertine-900 dark:text-white">
                            {{ number_format($this->wallet->balance, 2) }}
                        </p>
                    </div>
                </div>

                {{-- Rank --}}
                <div class="p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0
                        bg-purple-50 border border-purple-200
                        dark:bg-purple-500/10 dark:border-purple-500/20">
                        @if($rankInfo['rank'] === 1) 🥇
                        @elseif($rankInfo['rank'] === 2) 🥈
                        @elseif($rankInfo['rank'] === 3) 🥉
                        @else 🎖
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500">Rank</p>
                        <p class="text-base font-mono font-bold text-travertine-900 dark:text-white">
                            {{ $rankInfo['rank'] ? '#' . $rankInfo['rank'] : '—' }}
                            <span class="text-xs font-normal text-travertine-500 dark:text-zinc-500">
                                of {{ $rankInfo['total'] }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Net profit — semantic colors stay fixed per theme rule #5,
                     but shifted to lighter variants on light backgrounds --}}
                <div class="p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl border flex items-center justify-center text-xl shrink-0
                        {{ $profit >= 0
                            ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/20'
                            : 'bg-red-50 border-red-200 dark:bg-red-500/10 dark:border-red-500/20' }}">
                        {{ $profit >= 0 ? '📈' : '📉' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500">Net</p>
                            @if($this->wallet->canReset())
                                <button wire:click="resetWallet"
                                    wire:confirm="Reset your balance back to 50 points?"
                                    class="text-[10px] transition-colors
                                        text-travertine-400 hover:text-amber-700
                                        dark:text-zinc-500 dark:hover:text-amber-400"
                                    title="Reset to 50">↺</button>
                            @endif
                        </div>
                        <p class="text-base font-mono font-bold
                            {{ $profit >= 0
                                ? 'text-emerald-700 dark:text-emerald-400'
                                : 'text-red-700 dark:text-red-400' }}">
                            {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Faction perk footer strip --}}
            <div class="px-4 py-2 border-t border-travertine-300/70 bg-travertine-100/60 dark:border-zinc-700/40 dark:bg-zinc-900/40">
                <p class="text-xs text-travertine-500 dark:text-zinc-500">
                    <span class="text-travertine-700 dark:text-zinc-400">Faction perk:</span> {{ $currency['bonus'] }}
                </p>
            </div>
        </div>
    @else
        {{-- User doesn't have a wallet yet — prompt to join the season --}}
        @auth
        <div class="mb-6 rounded-2xl p-5 flex items-center gap-4
            border border-amber-200 bg-gradient-to-r from-amber-50 via-travertine-50 to-travertine-50
            dark:border-amber-500/20 dark:bg-gradient-to-r dark:from-amber-500/5 dark:via-zinc-900 dark:to-zinc-900">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl shrink-0
                bg-amber-100 border border-amber-200
                dark:bg-amber-500/10 dark:border-amber-500/30">
                ⚔️
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-travertine-900 dark:text-white">
                    Pick your faction to enter the season
                </p>
                <p class="text-xs text-travertine-500 dark:text-zinc-500 mt-0.5">
                    Each faction has its own perk. 50 starting points.
                </p>
            </div>
            <button wire:click="openCurrencyModal"
                class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg transition-colors
                    bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100
                    dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 dark:hover:bg-amber-500/20">
                Join Season →
            </button>
        </div>
        @endauth
    @endif
</div>