<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-3 sm:p-5">
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">🏟️ Recent tournaments</p>

    <div class="flex flex-col divide-y divide-zinc-700/50">
        @foreach($this->tournaments as $tournament)
        <div class="flex items-center justify-between py-2.5 gap-3">

            {{-- Name + date range --}}
            <div class="min-w-0">
                <p class="text-sm font-semibold text-zinc-100 truncate">{{ $tournament->name }}</p>
                <p class="text-xs text-zinc-600 font-mono mt-0.5">
                    {{ \Carbon\Carbon::parse($tournament->games_min_date_time)->format('d M Y') }}
                    @if($tournament->games_min_date_time !== $tournament->games_max_date_time)
                        → {{ \Carbon\Carbon::parse($tournament->games_max_date_time)->format('d M Y') }}
                    @endif
                </p>
            </div>

            {{-- Games count --}}
            <span class="text-xs text-zinc-500 font-mono shrink-0">
                {{ $tournament->games_count }} {{ $tournament->games_count === 1 ? 'game' : 'games' }}
            </span>

        </div>
        @endforeach
    </div>
</div>