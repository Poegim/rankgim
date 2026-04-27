@use('Illuminate\Support\Str')
<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 border-l-4 border-l-blue-500 p-3 sm:p-4">
    <p class="text-xs font-semibold uppercase tracking-widest text-blue-400 mb-3">
        ⚡ Most active
        <span class="text-zinc-500 font-normal normal-case ml-1">last year</span>
    </p>
    <div class="flex flex-col divide-y divide-zinc-700/50">
        @foreach($this->players as $row)
        <div class="flex items-center gap-2.5 py-2.5">
            <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
            <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
               class="hover:underline font-semibold text-sm text-zinc-100 flex-1 truncate">{{ $row->player->name }}</a>
            <span class="font-mono text-sm font-bold text-blue-400 shrink-0">{{ $row->total }}</span>
        </div>
        @endforeach
    </div>
</div>