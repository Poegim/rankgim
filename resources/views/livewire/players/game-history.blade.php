@use('Illuminate\Support\Str')

<div>
    <div class="rounded-xl border border-zinc-700/60 overflow-hidden">

        {{-- Header --}}
        <div class="grid grid-cols-12 gap-2 px-5 py-3 bg-zinc-800/60 border-b border-zinc-700/40 text-xs font-semibold uppercase tracking-widest text-zinc-600">
            <div class="col-span-2">Date</div>
            <div class="col-span-1 text-center">W/L</div>
            <div class="col-span-4">Opponent</div>
            <div class="col-span-3">Tournament</div>
            <div class="col-span-2 text-right">Rating</div>
        </div>

        {{-- Rows --}}
        @foreach($this->games as $entry)
        @php
            $opponent = $entry->result === 'win' ? $entry->game->loser : $entry->game->winner;
            $isWin    = $entry->result === 'win';
            $opponentRaceText = match($opponent?->race) {
                'Terran'  => 'text-blue-400',
                'Zerg'    => 'text-purple-400',
                'Protoss' => 'text-yellow-400',
                default   => 'text-zinc-600',
            };
        @endphp
        <div class="grid grid-cols-12 gap-2 items-center px-5 py-3 border-b border-zinc-700/30 hover:bg-zinc-800/30 transition-colors duration-100"
             style="border-left: 3px solid {{ $isWin ? '#22c55e' : '#ef4444' }}">

            {{-- Date --}}
            <div class="col-span-2">
                <p class="text-xs text-zinc-500 tabular-nums">{{ \Carbon\Carbon::parse($entry->played_at)->format('d M y') }}</p>
            </div>

            {{-- W/L --}}
            <div class="col-span-1 flex justify-center">
                <span class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-black
                    {{ $isWin ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400' }}">
                    {{ $isWin ? 'W' : 'L' }}
                </span>
            </div>

            {{-- Opponent --}}
            <div class="col-span-4 flex items-center gap-2.5 min-w-0">
                @if($opponent)
                <img
                    src="{{ asset('images/country_flags/' . strtolower($opponent->country_code) . '.svg') }}"
                    class="w-6 h-4 rounded-sm shrink-0"
                    title="{{ $opponent->country }}"
                >
                <div class="min-w-0">
                    <a href="{{ route('players.show', ['id' => $opponent->id, 'slug' => Str::slug($opponent->name)]) }}"
                       class="text-sm font-bold text-zinc-100 hover:text-white hover:underline truncate block transition-colors">
                        {{ $opponent->name }}
                    </a>
                    <span class="text-xs {{ $opponentRaceText }}">{{ $opponent->race }}</span>
                </div>
                @else
                <span class="text-zinc-600 text-sm">—</span>
                @endif
            </div>

            {{-- Tournament --}}
            <div class="col-span-3 min-w-0">
                @if($entry->game->tournament)
                <a href="{{ route('tournaments.show', $entry->game->tournament->id) }}"
                   class="text-xs text-zinc-500 hover:text-zinc-300 hover:underline truncate block transition-colors">
                    {{ $entry->game->tournament->name }}
                </a>
                @else
                <span class="text-zinc-700 text-xs">—</span>
                @endif
            </div>

            {{-- Rating + change --}}
            <div class="col-span-2 text-right">
                <p class="text-sm font-black tabular-nums text-zinc-200">{{ $entry->rating_after }}</p>
                <p class="text-xs font-semibold tabular-nums {{ $entry->rating_change >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $entry->rating_change > 0 ? '+' : '' }}{{ $entry->rating_change }}
                </p>
            </div>

        </div>
        @endforeach
    </div>

    {{-- Pagination - wire:navigate prevents scroll to top --}}
    <div class="mt-3" wire:ignore.self>
        {{ $this->games->links(data: ['scrollTo' => false]) }}
    </div>
</div>