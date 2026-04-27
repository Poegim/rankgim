@use('Illuminate\Support\Str')
<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 border-l-4 border-l-purple-500 p-3 sm:p-4">
    <p class="text-xs font-semibold uppercase tracking-widest text-purple-400 mb-3">
        💥 Biggest upsets
        <span class="text-zinc-500 font-normal normal-case ml-1">last year</span>
    </p>
    <div class="flex flex-col divide-y divide-zinc-700/50">
        @foreach($this->upsets as $entry)
        <div class="flex items-center gap-2 py-2.5">
            <img src="{{ asset('images/country_flags/' . strtolower($entry->game->winner->country_code) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
            <a href="{{ route('players.show', ['id' => $entry->game->winner->id, 'slug' => Str::slug($entry->game->winner->name)]) }}"
               class="hover:underline font-semibold text-green-400 truncate text-sm flex-1">{{ $entry->game->winner->name }}</a>
            <span class="text-zinc-600 text-xs shrink-0">beat</span>
            <a href="{{ route('players.show', ['id' => $entry->game->loser->id, 'slug' => Str::slug($entry->game->loser->name)]) }}"
               class="hover:underline text-zinc-400 truncate text-sm flex-1 text-right">{{ $entry->game->loser->name }}</a>
            <img src="{{ asset('images/country_flags/' . strtolower($entry->game->loser->country_code) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
        </div>
        @endforeach
    </div>
</div>