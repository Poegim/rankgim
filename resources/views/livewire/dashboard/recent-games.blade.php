@use('Illuminate\Support\Str')
<div class="rounded-xl border p-3 sm:p-5
    border-travertine-300 bg-travertine-50
    dark:border-zinc-700/60 dark:bg-zinc-800/40">
    <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
        🎮 Recent games
    </p>

    <div class="flex flex-col divide-y divide-travertine-350 dark:divide-zinc-700/50">
        @foreach($this->games as $entry)
        <div class="flex items-center justify-between py-2.5 gap-3">

            {{-- Winner --}}
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}"
                     class="w-5 h-3.5 rounded-sm shrink-0">
                <div class="min-w-0">
                    <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}"
                       class="hover:underline font-semibold text-sm truncate block
                           text-travertine-900 hover:text-oxblood
                           dark:text-zinc-100 dark:hover:text-white">
                        {{ $entry->game->winner->name }}
                    </a>
                    <p class="text-xs font-mono mt-0.5 text-travertine-400 dark:text-zinc-600">beat</p>
                </div>
            </div>

            {{-- Loser --}}
            <div class="flex items-center gap-2 min-w-0 flex-1 justify-end">
                <div class="min-w-0 text-right">
                    <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}"
                       class="hover:underline text-sm truncate block
                           text-travertine-600 hover:text-travertine-900
                           dark:text-zinc-400 dark:hover:text-zinc-200">
                        {{ $entry->game->loser->name }}
                    </a>
                    <p class="text-xs font-mono mt-0.5 text-travertine-400 dark:text-zinc-600">
                        {{ \Carbon\Carbon::parse($entry->played_at)->format('d M Y') }}
                    </p>
                </div>
                <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}"
                     class="w-5 h-3.5 rounded-sm shrink-0">
            </div>

        </div>
        @endforeach
    </div>
</div>