@use('Illuminate\Support\Str')
<div
    class="rounded-lg overflow-hidden
           border border-travertine-300 dark:border-zinc-700/60
           bg-travertine-50 dark:bg-zinc-800/40"
    style="border-left: 4px solid #2563eb;"
>
    {{-- Header strip — separated from body, same pattern as risers/fallers/streaks.
         Blue stays constant across themes (semantic = activity = lightning). --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5
                border-b border-travertine-300 dark:border-zinc-700/50
                bg-travertine-75 dark:bg-zinc-900/30">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] flex items-center gap-1.5 min-w-0"
           style="color: #1d4ed8;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #2563eb; color: #eff6ff;">⚡</span>
            <span class="truncate">Most active</span>
        </p>
        <span class="text-xs font-mono shrink-0
                     text-travertine-500 dark:text-zinc-500">last year</span>
    </div>

    {{-- Player rows --}}
    <div class="flex flex-col">
        @foreach($this->players as $index => $row)
        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
           class="flex items-center gap-2.5 px-3 sm:px-4 py-2.5 transition-colors group
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

            <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                 class="w-5 h-3.5 rounded-sm shrink-0"
                 alt="{{ $row->player->country_code }}">

            <span class="font-semibold text-sm flex-1 truncate transition-colors
                         text-travertine-800 group-hover:text-oxblood
                         dark:text-zinc-100 dark:group-hover:text-white">
                {{ $row->player->name }}
            </span>

            {{-- Games-played chip — blue variant of the delta chip --}}
            <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                         bg-blue-100 text-blue-800 border border-blue-300
                         dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20">
                {{ $row->total }}
            </span>
        </a>
        @endforeach
    </div>
</div>