@use('Illuminate\Support\Str')
<div
    class="rounded-lg overflow-hidden
           border border-travertine-300 dark:border-zinc-700/60
           bg-travertine-50 dark:bg-zinc-800/40"
    style="border-left: 4px solid #ea580c;"
>
    {{-- Header strip — separated from body, same pattern as risers/fallers.
         Orange stays constant across themes (semantic = fire = streak). --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5
                border-b border-travertine-300 dark:border-zinc-700/50
                bg-travertine-75 dark:bg-zinc-900/30">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] flex items-center gap-1.5 min-w-0"
           style="color: #c2410c;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #ea580c; color: #fff7ed;">🔥</span>
            <span class="truncate">Hot streaks</span>
        </p>
        <span class="text-xs font-mono shrink-0
                     text-travertine-500 dark:text-zinc-500">active</span>
    </div>

    {{-- Player rows — full-row hover + clickable, mono position number on the
         left so it's clear this is a sorted list, not random. --}}
    <div class="flex flex-col">
        @foreach($this->streaks as $index => $row)
        <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
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

            <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                 class="w-5 h-3.5 rounded-sm shrink-0"
                 alt="{{ $row->country_code }}">

            <span class="font-semibold text-sm flex-1 truncate transition-colors
                         text-travertine-800 group-hover:text-oxblood
                         dark:text-zinc-100 dark:group-hover:text-white">
                {{ $row->name }}
            </span>

            {{-- Streak chip — orange variant of the delta chip --}}
            <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                         bg-orange-100 text-orange-800 border border-orange-300
                         dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20">
                {{ $row->streak }}W
            </span>
        </a>
        @endforeach
    </div>
</div>