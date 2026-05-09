@use('Illuminate\Support\Str')
<div
    class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden"
    style="border-left: 4px solid #3b82f6;"
>
    {{-- Header strip — separated from body, same pattern as risers/fallers/streaks --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5 border-b border-zinc-700/50 bg-zinc-900/30">
        <p class="text-xs font-semibold uppercase tracking-widest flex items-center gap-1.5 min-w-0" style="color: #60a5fa;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #3b82f6; color: #0a0a0a;">⚡</span>
            <span class="truncate">Most active</span>
        </p>
        <span class="text-xs text-zinc-500 font-mono shrink-0">last year</span>
    </div>

    {{-- Player rows — full-row hover + clickable, mono position number on the
         left so it's clear this is a sorted list, not random. --}}
    <div class="flex flex-col divide-y divide-zinc-700/40">
        @foreach($this->players as $index => $row)
        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
           class="flex items-center gap-2.5 px-3 sm:px-4 py-2.5 hover:bg-zinc-700/20 transition-colors group">

            {{-- Position number — top-3 brighter --}}
            <span class="text-xs font-mono font-bold w-6 shrink-0 {{ $index < 3 ? 'text-zinc-300' : 'text-zinc-600' }}">
                #{{ $index + 1 }}
            </span>

            <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                 class="w-5 h-3.5 rounded-sm shrink-0">

            <span class="font-semibold text-sm text-zinc-100 group-hover:text-white flex-1 truncate">
                {{ $row->player->name }}
            </span>

            {{-- Games-played chip — blue variant of the delta chip --}}
            <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                         bg-blue-500/10 text-blue-400 border border-blue-500/20">
                {{ $row->total }}
            </span>
        </a>
        @endforeach
    </div>
</div>