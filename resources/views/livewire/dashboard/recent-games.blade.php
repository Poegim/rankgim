@use('Illuminate\Support\Str')
<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-5">
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">🎮 Recent games</p>

    <div class="flex flex-col divide-y divide-zinc-700/50">
        @foreach($this->games as $entry)
        <div class="flex items-center justify-between py-2.5 gap-3">

            {{-- Winner --}}
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}"
                     class="w-5 h-3.5 rounded-sm shrink-0">
                <div class="min-w-0">
                    <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}"
                       class="hover:underline font-semibold text-sm text-zinc-100 truncate block">{{ $entry->game->winner->name }}</a>
                    <p class="text-xs text-zinc-600 font-mono mt-0.5">beat</p>
                </div>
            </div>

            {{-- Loser --}}
            <div class="flex items-center gap-2 min-w-0 flex-1 justify-end">
                <div class="min-w-0 text-right">
                    <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}"
                       class="hover:underline text-sm text-zinc-400 truncate block">{{ $entry->game->loser->name }}</a>
                    <p class="text-xs text-zinc-600 font-mono mt-0.5">{{ \Carbon\Carbon::parse($entry->played_at)->format('d M Y') }}</p>
                </div>
                <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}"
                     class="w-5 h-3.5 rounded-sm shrink-0">
            </div>

        </div>
        @endforeach
    </div>
</div>