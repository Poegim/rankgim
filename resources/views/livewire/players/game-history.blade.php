@use('Illuminate\Support\Str')

<div>
    <div class="rounded-xl overflow-hidden
                border border-travertine-300 dark:border-zinc-700/60">

        {{-- Header --}}
        <div class="grid grid-cols-12 gap-2 px-5 py-3 text-xs font-semibold uppercase tracking-widest
                    bg-travertine-100 text-travertine-600 border-b border-travertine-300
                    dark:bg-zinc-800/60 dark:text-zinc-600 dark:border-zinc-700/40">
            <div class="col-span-2">Date</div>
            <div class="col-span-1 text-center">Result</div>
            <div class="col-span-4">Opponent</div>
            <div class="col-span-3">Tournament</div>
            <div class="col-span-2 text-right">Rating</div>
        </div>

        {{-- Rows --}}
        @foreach($this->games as $entry)
        @php
            // Draws are detected from the game table (result == 3),
            // because EloService always writes 'win'/'loss' in rating_histories
            // even for draws — only the rating calculation uses 0.5 score.
            $isDraw = (int) $entry->game->result === 3;
            $isWin  = ! $isDraw && $entry->result === 'win';

            // For draws winner_id/loser_id are nominal; resolve opponent via player_id.
            // For regular games: win → opponent is loser, loss → opponent is winner.
            if ($isDraw) {
                $opponent = $entry->player_id === $entry->game->winner_id
                    ? $entry->game->loser
                    : $entry->game->winner;
            } else {
                $opponent = $isWin ? $entry->game->loser : $entry->game->winner;
            }

            // Race color via CSS var — auto theme-adjusts.
            $opponentRaceVar = match($opponent?->race) {
                'Terran'  => 'var(--color-race-terran-soft)',
                'Zerg'    => 'var(--color-race-zerg-soft)',
                'Protoss' => 'var(--color-race-protoss-soft)',
                'Random'  => 'var(--color-race-random-soft)',
                default   => 'var(--color-race-unknown-soft)',
            };

            // Left border color: green = win, red = loss, amber = draw.
            $borderColor = $isDraw ? '#d97706' : ($isWin ? '#16a34a' : '#dc2626');
        @endphp
        <div class="grid grid-cols-12 gap-2 items-center px-5 py-3 transition-colors duration-100
                    border-b border-travertine-300/60 hover:bg-oxblood/5
                    dark:border-zinc-700/30 dark:hover:bg-zinc-800/30"
             style="border-left: 3px solid {{ $borderColor }}">

            {{-- Date --}}
            <div class="col-span-2">
                <p class="text-xs tabular-nums
                          text-travertine-500 dark:text-zinc-500">
                    {{ \Carbon\Carbon::parse($entry->played_at)->format('d M y') }}
                </p>
            </div>

            {{-- Result badge --}}
            <div class="col-span-1 flex justify-center">
                <span @class([
                    'w-7 h-7 flex items-center justify-center rounded-lg text-xs font-black',
                    'bg-emerald-100 text-emerald-800 dark:bg-green-500/15 dark:text-green-400' => $isWin,
                    'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-400'     => $isDraw,
                    'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-400'             => ! $isWin && ! $isDraw,
                ])>
                    {{ $isWin ? 'W' : ($isDraw ? 'D' : 'L') }}
                </span>
            </div>

            {{-- Opponent --}}
            <div class="col-span-4 flex items-center gap-2.5 min-w-0">
                @if($opponent)
                <img
                    src="{{ asset('images/country_flags/' . strtolower($opponent->country_code) . '.svg') }}"
                    class="w-6 h-4 rounded-sm shrink-0"
                    title="{{ $opponent->country }}"
                    alt="{{ $opponent->country_code }}"
                >
                <div class="min-w-0">
                    <a href="{{ route('players.show', ['id' => $opponent->id, 'slug' => Str::slug($opponent->name)]) }}"
                       class="text-sm font-bold hover:underline truncate block transition-colors
                              text-travertine-800 hover:text-oxblood
                              dark:text-zinc-100 dark:hover:text-white">
                        {{ $opponent->name }}
                    </a>
                    <span class="text-xs" style="color: {{ $opponentRaceVar }};">{{ $opponent->race }}</span>
                </div>
                @else
                <span class="text-sm text-travertine-500 dark:text-zinc-600">—</span>
                @endif
            </div>

            {{-- Tournament --}}
            <div class="col-span-3 min-w-0">
                @if($entry->game->tournament)
                <a href="{{ route('tournaments.show', $entry->game->tournament->id) }}"
                   class="text-xs hover:underline truncate block transition-colors
                          text-travertine-600 hover:text-oxblood
                          dark:text-zinc-500 dark:hover:text-zinc-300">
                    {{ $entry->game->tournament->name }}
                </a>
                @else
                <span class="text-xs text-travertine-400 dark:text-zinc-700">—</span>
                @endif
            </div>

            {{-- Rating + change --}}
            <div class="col-span-2 text-right">
                <p class="text-sm font-black tabular-nums
                          text-travertine-900 dark:text-zinc-200">{{ $entry->rating_after }}</p>
                <p @class([
                    'text-xs font-semibold tabular-nums',
                    'text-emerald-700 dark:text-green-400' => $entry->rating_change > 0,
                    'text-amber-700 dark:text-amber-400'   => $entry->rating_change == 0,
                    'text-red-700 dark:text-red-400'       => $entry->rating_change < 0,
                ])>
                    {{ $entry->rating_change > 0 ? '+' : '' }}{{ $entry->rating_change }}
                </p>
            </div>

        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-3" wire:ignore.self>
        {{ $this->games->links(data: ['scrollTo' => false]) }}
    </div>
</div>