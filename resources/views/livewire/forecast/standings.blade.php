<div>
    @if($this->ranking->isEmpty())
        <div class="text-center py-16 text-zinc-600">
            <p class="text-5xl mb-3">🏜️</p>
            <p class="text-sm">No challengers yet — be the first to make a forecast.</p>
        </div>
    @else
        <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 overflow-hidden">

            {{-- Table header --}}
            <div class="hidden md:grid grid-cols-[70px_1fr_70px_90px_90px_100px] gap-3 px-4 py-2.5 border-b border-zinc-800 bg-zinc-900/80">
                <span class="text-[10px] uppercase tracking-wider text-zinc-500">#</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500">Player</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-center">Faction</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Accuracy</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Balance</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Net</span>
            </div>

            {{-- Table rows --}}
            @foreach($this->ranking as $i => $wallet)
                @php
                    $position = $i + 1;
                    $isMe   = auth()->check() && $wallet->user_id === auth()->id();
                    $cur    = \App\Models\ForecastWallet::CURRENCIES[$wallet->currency];
                    $accuracy = $wallet->settled_count > 0
                        ? round($wallet->won_count / $wallet->settled_count * 100)
                        : 0;

                    // Medal for top 3 — appears inside the # cell
                    $medal = match($position) {
                        1 => '🥇',
                        2 => '🥈',
                        3 => '🥉',
                        default => null,
                    };
                @endphp

                {{-- Mobile layout (stacked) --}}
                <div class="md:hidden flex items-center gap-3 px-4 py-3 border-b border-zinc-800/60 last:border-b-0
                    {{ $isMe ? 'bg-amber-500/5' : '' }}">
                    <div class="flex items-center gap-1 w-16 shrink-0">
                        @if($medal)
                            <span class="text-lg">{{ $medal }}</span>
                        @endif
                        <span class="text-xs font-mono {{ $medal ? 'text-white' : 'text-zinc-600' }}">#{{ $position }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-zinc-200 truncate">
                            {{ $wallet->user->name }}
                            @if($isMe) <span class="text-xs text-amber-400 ml-1">(you)</span> @endif
                        </p>
                        <p class="text-[11px] text-zinc-500 font-mono">
                            {{ $cur['icon'] }} · {{ $accuracy }}% · bal {{ number_format($wallet->balance, 0) }}
                        </p>
                    </div>
                    <span class="text-sm font-mono font-bold shrink-0 w-20 text-right
                        {{ $wallet->computed_profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $wallet->computed_profit >= 0 ? '+' : '' }}{{ number_format($wallet->computed_profit, 0) }}
                    </span>
                </div>

                {{-- Desktop layout (full grid) --}}
                <div class="hidden md:grid grid-cols-[70px_1fr_70px_90px_90px_100px] gap-3 px-4 py-3 items-center border-b border-zinc-800/60 last:border-b-0 transition-colors
                    {{ $isMe ? 'bg-amber-500/5 hover:bg-amber-500/10' : 'hover:bg-zinc-900/70' }}">

                    {{-- # column (medal for top 3, plain number otherwise) --}}
                    <div class="flex items-center gap-1.5">
                        @if($medal)
                            <span class="text-xl">{{ $medal }}</span>
                            <span class="text-sm font-mono font-bold text-white">#{{ $position }}</span>
                        @else
                            <span class="text-sm font-mono text-zinc-600">#{{ $position }}</span>
                        @endif
                    </div>

                    {{-- Player --}}
                    <div class="min-w-0">
                        <p class="text-sm text-zinc-200 truncate font-medium">
                            {{ $wallet->user->name }}
                            @if($isMe) <span class="text-xs text-amber-400 ml-1">(you)</span> @endif
                        </p>
                        <p class="text-[11px] text-zinc-600">{{ $wallet->settled_count }} {{ Str::plural('forecast', $wallet->settled_count) }}</p>
                    </div>

                    {{-- Faction icon --}}
                    <div class="text-center" title="{{ $cur['label'] }}">
                        <span class="text-xl">{{ $cur['icon'] }}</span>
                    </div>

                    {{-- Accuracy --}}
                    <div class="text-right">
                        <p class="text-sm font-mono text-zinc-300">{{ $accuracy }}%</p>
                        <p class="text-[10px] text-zinc-600">{{ $wallet->won_count }}/{{ $wallet->settled_count }}</p>
                    </div>

                    {{-- Balance --}}
                    <div class="text-right">
                        <p class="text-sm font-mono text-zinc-400">{{ number_format($wallet->balance, 0) }}</p>
                    </div>

                    {{-- Net --}}
                    <div class="text-right">
                        <p class="text-base font-mono font-bold
                            {{ $wallet->computed_profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $wallet->computed_profit >= 0 ? '+' : '' }}{{ number_format($wallet->computed_profit, 0) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>