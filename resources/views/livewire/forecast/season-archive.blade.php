{{-- ══════════════════════════════════════════════════════════════════════
     Forecast — Season Archive.

     Lists all closed seasons. Each row expands to show the final
     standings (from forecast_season_snapshots).
     ══════════════════════════════════════════════════════════════════ --}}

<div>
    @if($this->seasons->isEmpty())
        <div class="text-center py-16 text-travertine-500 dark:text-zinc-600">
            <p class="text-5xl mb-3">📦</p>
            <p class="text-sm">No past seasons yet.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($this->seasons as $season)
                <div class="rounded-xl border overflow-hidden
                    border-travertine-300 bg-travertine-50
                    dark:border-zinc-700/50 dark:bg-zinc-900/40">

                    {{-- Season header row — clickable to expand --}}
                    <button
                        wire:click="toggleSeason({{ $season->id }})"
                        class="w-full flex items-center justify-between px-5 py-4 text-left transition-colors
                            hover:bg-oxblood/5 dark:hover:bg-zinc-800/40">

                        <div class="flex items-center gap-3">
                            <span class="text-lg">🏆</span>
                            <div>
                                <p class="text-sm font-semibold text-travertine-900 dark:text-zinc-200">
                                    {{ $season->name }}
                                </p>
                                <p class="text-xs mt-0.5 text-travertine-500 dark:text-zinc-500">
                                    {{ $season->starts_at?->format('d M Y') }}
                                    –
                                    {{ $season->ends_at?->format('d M Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-xs text-travertine-400 dark:text-zinc-500">
                                {{ $season->snapshots->count() }} players
                            </span>
                            <svg class="w-4 h-4 transition-transform
                                text-travertine-400 dark:text-zinc-500
                                {{ $expandedSeasonId === $season->id ? 'rotate-180' : '' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Standings table — only renders when expanded --}}
                    @if($expandedSeasonId === $season->id)
                        <div class="border-t border-travertine-300 dark:border-zinc-800">

                            @if($this->standings->isEmpty())
                                <p class="text-center py-8 text-sm text-travertine-400 dark:text-zinc-600">
                                    No standings recorded for this season.
                                </p>
                            @else
                                {{-- Table header --}}
                                <div class="grid grid-cols-[40px_1fr_80px_80px_80px_60px] gap-3 px-5 py-2.5 border-b
                                    bg-travertine-100 border-travertine-300
                                    dark:bg-zinc-900/60 dark:border-zinc-800">
                                    <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500">#</span>
                                    <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500">Player</span>
                                    <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500 text-right">Profit</span>
                                    <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500 text-right">Balance</span>
                                    <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500 text-right">Accuracy</span>
                                    <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500 text-right">Picks</span>
                                </div>

                                @foreach($this->standings as $snap)
                                    @php
                                        $accuracy = $snap->total_predictions > 0
                                            ? round($snap->correct_predictions / $snap->total_predictions * 100, 1)
                                            : 0;

                                        // Top 3 podium medals
                                        $medal = match($snap->rank) {
                                            1 => '🥇',
                                            2 => '🥈',
                                            3 => '🥉',
                                            default => null,
                                        };
                                    @endphp

                                    <div class="grid grid-cols-[40px_1fr_80px_80px_80px_60px] gap-3 px-5 py-3 border-b last:border-0 transition-colors
                                        border-travertine-350 dark:border-zinc-800/60
                                        {{ $snap->rank <= 3
                                            ? 'bg-travertine-100/60 dark:bg-zinc-800/20'
                                            : 'hover:bg-oxblood/5 dark:hover:bg-zinc-800/10' }}">

                                        {{-- Rank --}}
                                        <div class="flex items-center">
                                            @if($medal)
                                                <span class="text-base">{{ $medal }}</span>
                                            @else
                                                <span class="text-sm font-mono text-travertine-400 dark:text-zinc-500">
                                                    {{ $snap->rank }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Username + currency --}}
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium truncate text-travertine-800 dark:text-zinc-200">
                                                {{ $snap->user?->name ?? 'Unknown' }}
                                            </p>
                                            <p class="text-[10px] uppercase tracking-wide mt-0.5 text-travertine-400 dark:text-zinc-600">
                                                {{ $snap->currencyIcon() }} {{ $snap->currency }}
                                            </p>
                                        </div>

                                        {{-- Final profit — semantic colors (rule #5) --}}
                                        <div class="text-right">
                                            <p class="text-sm font-mono
                                                {{ $snap->final_profit >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                                {{ $snap->final_profit >= 0 ? '+' : '' }}{{ number_format($snap->final_profit, 2) }}
                                            </p>
                                        </div>

                                        {{-- Final balance --}}
                                        <div class="text-right">
                                            <p class="text-sm font-mono text-travertine-700 dark:text-zinc-300">
                                                {{ number_format($snap->final_balance, 2) }}
                                            </p>
                                        </div>

                                        {{-- Accuracy --}}
                                        <div class="text-right">
                                            <p class="text-sm font-mono text-travertine-700 dark:text-zinc-300">
                                                {{ $accuracy }}%
                                            </p>
                                            <p class="text-[10px] text-travertine-400 dark:text-zinc-600">
                                                {{ $snap->correct_predictions }}/{{ $snap->total_predictions }}
                                            </p>
                                        </div>

                                        {{-- Total picks --}}
                                        <div class="text-right">
                                            <p class="text-sm font-mono text-travertine-600 dark:text-zinc-400">
                                                {{ $snap->total_predictions }}
                                            </p>
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