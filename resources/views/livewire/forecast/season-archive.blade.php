{{-- ══════════════════════════════════════════════════════════════════════
     Forecast — Season Archive.

     Lists all closed seasons. Each row expands to show the final
     standings (from forecast_season_snapshots).
     ══════════════════════════════════════════════════════════════════ --}}

<div>
    @if($this->seasons->isEmpty())
        <div class="text-center py-16 text-zinc-600">
            <p class="text-5xl mb-3">📦</p>
            <p class="text-sm">No past seasons yet.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($this->seasons as $season)
                <div class="rounded-xl border border-zinc-700/50 bg-zinc-900/40 overflow-hidden">

                    {{-- Season header row — clickable to expand --}}
                    <button
                        wire:click="toggleSeason({{ $season->id }})"
                        class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-zinc-800/40 transition-colors">

                        <div class="flex items-center gap-3">
                            <span class="text-lg">🏆</span>
                            <div>
                                <p class="text-sm font-semibold text-zinc-200">{{ $season->name }}</p>
                                <p class="text-xs text-zinc-500 mt-0.5">
                                    {{ $season->starts_at?->format('d M Y') }}
                                    –
                                    {{ $season->ends_at?->format('d M Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Snapshot count badge --}}
                            <span class="text-xs text-zinc-500">
                                {{ $season->snapshots->count() }} players
                            </span>

                            {{-- Chevron --}}
                            <svg class="w-4 h-4 text-zinc-500 transition-transform {{ $expandedSeasonId === $season->id ? 'rotate-180' : '' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Standings table — only renders when expanded --}}
                    @if($expandedSeasonId === $season->id)
                        <div class="border-t border-zinc-800">

                            @if($this->standings->isEmpty())
                                <p class="text-center py-8 text-sm text-zinc-600">
                                    No standings recorded for this season.
                                </p>
                            @else
                                {{-- Table header --}}
                                <div class="grid grid-cols-[40px_1fr_80px_80px_80px_60px] gap-3 px-5 py-2.5 bg-zinc-900/60 border-b border-zinc-800">
                                    <span class="text-[10px] uppercase tracking-wider text-zinc-500">#</span>
                                    <span class="text-[10px] uppercase tracking-wider text-zinc-500">Player</span>
                                    <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Profit</span>
                                    <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Balance</span>
                                    <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Accuracy</span>
                                    <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Picks</span>
                                </div>

                                @foreach($this->standings as $snap)
                                    @php
                                        $accuracy = $snap->total_predictions > 0
                                            ? round($snap->correct_predictions / $snap->total_predictions * 100, 1)
                                            : 0;

                                        $profitColor = $snap->final_profit >= 0 ? 'text-emerald-400' : 'text-red-400';

                                        // Top 3 podium medals
                                        $medal = match($snap->rank) {
                                            1 => '🥇',
                                            2 => '🥈',
                                            3 => '🥉',
                                            default => null,
                                        };
                                    @endphp

                                    <div class="grid grid-cols-[40px_1fr_80px_80px_80px_60px] gap-3 px-5 py-3 border-b border-zinc-800/60 last:border-0
                                        {{ $snap->rank <= 3 ? 'bg-zinc-800/20' : '' }}">

                                        {{-- Rank --}}
                                        <div class="flex items-center">
                                            @if($medal)
                                                <span class="text-base">{{ $medal }}</span>
                                            @else
                                                <span class="text-sm font-mono text-zinc-500">{{ $snap->rank }}</span>
                                            @endif
                                        </div>

                                        {{-- Username + currency --}}
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-zinc-200 truncate">
                                                {{ $snap->user?->name ?? 'Unknown' }}
                                            </p>
                                            <p class="text-[10px] text-zinc-600 uppercase tracking-wide mt-0.5">
                                                {{ $snap->currencyIcon() }} {{ $snap->currency }}
                                            </p>
                                        </div>

                                        {{-- Final profit --}}
                                        <div class="text-right">
                                            <p class="text-sm font-mono {{ $profitColor }}">
                                                {{ $snap->final_profit >= 0 ? '+' : '' }}{{ number_format($snap->final_profit, 0) }}
                                            </p>
                                        </div>

                                        {{-- Final balance --}}
                                        <div class="text-right">
                                            <p class="text-sm font-mono text-zinc-300">
                                                {{ number_format($snap->final_balance, 0) }}
                                            </p>
                                        </div>

                                        {{-- Accuracy --}}
                                        <div class="text-right">
                                            <p class="text-sm font-mono text-zinc-300">{{ $accuracy }}%</p>
                                            <p class="text-[10px] text-zinc-600">
                                                {{ $snap->correct_predictions }}/{{ $snap->total_predictions }}
                                            </p>
                                        </div>

                                        {{-- Total picks --}}
                                        <div class="text-right">
                                            <p class="text-sm font-mono text-zinc-400">{{ $snap->total_predictions }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif
</div>