<div class="rounded-xl border p-3 sm:p-5
    border-travertine-300 bg-travertine-50
    dark:border-zinc-700/60 dark:bg-zinc-800/40">
    <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
        🏟️ Recent tournaments
    </p>

    <div class="flex flex-col divide-y divide-travertine-350 dark:divide-zinc-700/50">
        @foreach($this->tournaments as $tournament)
        <div class="flex items-center justify-between py-2.5 gap-3">

            {{-- Name + date range --}}
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate text-travertine-900 dark:text-zinc-100">
                    {{ $tournament->name }}
                </p>
                <p class="text-xs font-mono mt-0.5 text-travertine-400 dark:text-zinc-600">
                    {{ \Carbon\Carbon::parse($tournament->games_min_date_time)->format('d M Y') }}
                    @if($tournament->games_min_date_time !== $tournament->games_max_date_time)
                        → {{ \Carbon\Carbon::parse($tournament->games_max_date_time)->format('d M Y') }}
                    @endif
                </p>
            </div>

            {{-- Games count --}}
            <span class="text-xs font-mono shrink-0 text-travertine-500 dark:text-zinc-500">
                {{ $tournament->games_count }} {{ $tournament->games_count === 1 ? 'game' : 'games' }}
            </span>

        </div>
        @endforeach
    </div>
</div>