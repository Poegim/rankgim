<div>
    @if($this->season && $this->ranking->isNotEmpty())

        {{-- Widget header --}}
        <div class="flex items-center justify-between mb-1 mt-1 px-1">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
                🎯 Forecast standings
            </p>
            <a href="{{ route('forecast.index', ['tab' => 'standings']) }}"
               wire:navigate
               class="text-xs transition-colors text-travertine-500 hover:text-travertine-800 dark:text-zinc-400 dark:hover:text-zinc-200">
                Full standings →
            </a>
        </div>

        <div class="rounded-xl border overflow-hidden divide-y
            border-travertine-300 bg-travertine-50 divide-travertine-350
            dark:border-zinc-700/60 dark:bg-zinc-800/40 dark:divide-zinc-700/40">

            @foreach($this->ranking as $i => $wallet)
                @php
                    $position = $i + 1;
                    $isMe     = auth()->check() && $wallet->user_id === auth()->id();
                    $cur      = \App\Models\ForecastWallet::CURRENCIES[$wallet->currency];
                    $profit   = (float) $wallet->computed_profit;
                    $accuracy = $wallet->settled_count > 0
                        ? round($wallet->won_count / $wallet->settled_count * 100)
                        : 0;

                    $medal = match($position) {
                        1 => '🥇',
                        2 => '🥈',
                        3 => '🥉',
                        default => null,
                    };
                @endphp

                <div class="flex items-center gap-3 px-4 py-[7.5px] transition-colors
                    {{ $isMe ? 'bg-amber-500/5' : 'hover:bg-oxblood/5 dark:hover:bg-zinc-900/60' }}">

                    {{-- Rank --}}
                    <div class="w-7 shrink-0 flex items-center justify-center">
                        @if($medal)
                            <span class="text-base leading-none">{{ $medal }}</span>
                        @else
                            <span class="text-xs font-mono text-travertine-400 dark:text-zinc-600">
                                #{{ $position }}
                            </span>
                        @endif
                    </div>

                    {{-- Faction icon --}}
                    <span class="text-base shrink-0" title="{{ $cur['label'] }}">{{ $cur['icon'] }}</span>

                    {{-- Name --}}
                    <span class="text-sm flex-1 min-w-0 truncate font-medium
                        {{ $isMe ? 'text-amber-700 dark:text-amber-400' : 'text-travertine-800 dark:text-zinc-200' }}">
                        {{ $wallet->user->name }}
                        @if($isMe)
                            <span class="text-[10px] font-normal ml-1 text-travertine-400 dark:text-zinc-500">(you)</span>
                        @endif
                    </span>

                    {{-- Accuracy --}}
                    <span class="text-xs font-mono shrink-0 text-travertine-500 dark:text-zinc-500">
                        {{ $accuracy }}%
                    </span>

                    {{-- Net profit --}}
                    <span class="text-sm font-mono font-bold shrink-0 w-16 text-right
                        {{ $profit >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                        {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 2) }}
                    </span>
                </div>
            @endforeach
        </div>

    @elseif($this->season)
        {{-- Season active but no one has joined yet --}}
        <div class="flex items-center justify-between mb-3 px-1">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
                🎯 Forecast standings
            </p>
        </div>
        <p class="text-sm italic text-travertine-500 dark:text-zinc-500">
            No one has joined the season yet.
        </p>
    @endif
</div>