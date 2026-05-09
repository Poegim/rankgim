@use('Illuminate\Support\Str')
<div
    class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden"
    style="border-left: 4px solid #a855f7;"
>
    {{-- Header strip — separated from body, same pattern as risers/fallers --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5 border-b border-zinc-700/50 bg-zinc-900/30">
        <p class="text-xs font-semibold uppercase tracking-widest flex items-center gap-1.5 min-w-0" style="color: #c084fc;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #a855f7; color: #0a0a0a;">💥</span>
            <span class="truncate">Biggest upsets</span>
        </p>
        <span class="text-xs text-zinc-500 font-mono shrink-0">last year</span>
    </div>

    {{-- Versus rows — winner on the left (green), loser on the right (zinc),
         "beat" between them. Chip on the far right shows ELO points the
         underdog won in this game (rating_change). For ELO this is symmetric:
         winner +N, loser −N. --}}
    <div class="flex flex-col divide-y divide-zinc-700/40">
        @foreach($this->upsets as $index => $entry)
            <div class="flex items-center gap-2 px-3 sm:px-4 py-2.5 hover:bg-zinc-700/20 transition-colors">

                {{-- Position number — top-3 brighter --}}
                <span class="text-xs font-mono font-bold w-6 shrink-0 {{ $index < 3 ? 'text-zinc-300' : 'text-zinc-600' }}">
                    #{{ $index + 1 }}
                </span>

                {{-- Winner side (left) --}}
                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                    <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}"
                         class="w-5 h-3.5 rounded-sm shrink-0">
                    <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}"
                       class="font-semibold text-sm text-emerald-400 hover:text-emerald-300 hover:underline truncate">
                        {{ $entry->game->winner->name }}
                    </a>
                </div>

                {{-- Connector --}}
                <span class="text-[10px] uppercase tracking-wider text-zinc-600 font-semibold shrink-0">beat</span>

                {{-- Loser side (right) --}}
                <div class="flex items-center gap-1.5 min-w-0 flex-1 justify-end">
                    <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}"
                       class="text-sm text-zinc-400 hover:text-zinc-200 hover:underline truncate">
                        {{ $entry->game->loser->name }}
                    </a>
                    <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}"
                         class="w-5 h-3.5 rounded-sm shrink-0">
                </div>

                {{-- ELO points won — purple variant of the delta chip pattern.
                     $entry is a RatingHistory row for the winner side, so
                     rating_change is the ELO gained by the underdog. --}}
                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                             bg-purple-500/10 text-purple-400 border border-purple-500/20"
                      title="ELO points won by the underdog">
                    +{{ (int) $entry->rating_change }}
                </span>
            </div>
        @endforeach
    </div>
</div>