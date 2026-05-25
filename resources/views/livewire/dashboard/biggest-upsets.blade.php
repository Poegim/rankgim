@use('Illuminate\Support\Str')
<div
    class="rounded-lg overflow-hidden
           border border-travertine-300 dark:border-zinc-700/60
           bg-travertine-50 dark:bg-zinc-800/40"
    style="border-left: 4px solid #9333ea;"
>
    {{-- Header strip — purple stays constant across themes (semantic = shock/upset) --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5
                border-b border-travertine-300 dark:border-zinc-700/50
                bg-travertine-75 dark:bg-zinc-900/30">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] flex items-center gap-1.5 min-w-0"
           style="color: #7e22ce;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #9333ea; color: #faf5ff;">💥</span>
            <span class="truncate">Biggest upsets</span>
        </p>
        <span class="text-xs font-mono shrink-0
                     text-travertine-500 dark:text-zinc-500">last year</span>
    </div>

    {{-- Versus rows — winner on the left (emerald), loser on the right (muted),
         "beat" between them. Chip on the far right shows ELO points the
         underdog won. For ELO this is symmetric: winner +N, loser −N. --}}
    <div class="flex flex-col">
        @foreach($this->upsets as $index => $entry)
            <div class="flex items-center gap-2 px-3 sm:px-4 py-2.5 transition-colors
                        border-b border-travertine-350 last:border-b-0
                        dark:border-zinc-700/40
                        hover:bg-oxblood/5 dark:hover:bg-zinc-700/20">

                {{-- Position number — top-3 brighter --}}
                <span @class([
                    'text-xs font-mono font-bold w-6 shrink-0',
                    'text-travertine-900 dark:text-zinc-300' => $index < 3,
                    'text-travertine-500 dark:text-zinc-600' => $index >= 3,
                ])>
                    #{{ $index + 1 }}
                </span>

                {{-- Winner side (left) — emerald for "underdog won" semantic --}}
                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                    <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}"
                         class="w-5 h-3.5 rounded-sm shrink-0"
                         alt="{{ $entry->game->winner->country_code }}">
                    <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}"
                       class="font-semibold text-sm hover:underline truncate
                              text-emerald-700 hover:text-emerald-800
                              dark:text-emerald-400 dark:hover:text-emerald-300">
                        {{ $entry->game->winner->name }}
                    </a>
                </div>

                {{-- Connector --}}
                <span class="text-[10px] uppercase tracking-wider font-semibold shrink-0
                             text-travertine-500 dark:text-zinc-600">beat</span>

                {{-- Loser side (right) — muted body text --}}
                <div class="flex items-center gap-1.5 min-w-0 flex-1 justify-end">
                    <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}"
                       class="text-sm hover:underline truncate
                              text-travertine-600 hover:text-travertine-900
                              dark:text-zinc-400 dark:hover:text-zinc-200">
                        {{ $entry->game->loser->name }}
                    </a>
                    <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}"
                         class="w-5 h-3.5 rounded-sm shrink-0"
                         alt="{{ $entry->game->loser->country_code }}">
                </div>

                {{-- ELO points won — purple variant of the delta chip.
                     $entry is a RatingHistory row for the winner side, so
                     rating_change is the ELO gained by the underdog. --}}
                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                             bg-purple-100 text-purple-800 border border-purple-300
                             dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20"
                      title="ELO points won by the underdog">
                    +{{ (int) $entry->rating_change }}
                </span>
            </div>
        @endforeach
    </div>
</div>