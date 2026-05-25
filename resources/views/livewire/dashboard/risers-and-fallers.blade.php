@use('Illuminate\Support\Str')
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- ─── Risers ──────────────────────────────────────────────────────── --}}
    <div
        class="rounded-lg overflow-hidden
               border border-travertine-300 dark:border-zinc-700/60
               bg-travertine-50 dark:bg-zinc-800/40"
        style="border-left: 4px solid #16a34a;"
    >
        {{-- Header strip — separated from body by a border, same pattern as
             event-card and forecast match-card. The :emerald color stays
             constant across themes (semantic = green = up), only the bg
             surface adapts. --}}
        <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5
                    border-b border-travertine-300 dark:border-zinc-700/50
                    bg-travertine-75 dark:bg-zinc-900/30">
            {{-- Title — green icon + green Cinzel label, both stay constant --}}
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] flex items-center gap-1.5 min-w-0"
               style="color: #15803d;">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                      style="background: #16a34a; color: #f0fdf4;">📈</span>
                <span class="truncate">Biggest risers</span>
            </p>
            <span class="text-xs font-mono shrink-0
                         text-travertine-500 dark:text-zinc-500">last month</span>
        </div>

        {{-- Player rows — full-row hover + clickable, mono position number on
             the left so it's clear this is a sorted list. --}}
        <div class="flex flex-col">
            @foreach($this->risers as $index => $row)
            <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
               class="flex items-center gap-2.5 px-3 sm:px-4 py-2.5 transition-colors group
                      border-b border-travertine-350 last:border-b-0
                      dark:border-zinc-700/40
                      hover:bg-oxblood/5 dark:hover:bg-zinc-700/20">

                {{-- Position number — top-3 brighter so winners stand out --}}
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

                {{-- Delta chip — emerald success state, theme-paired surface --}}
                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                             bg-emerald-100 text-emerald-800 border border-emerald-300
                             dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                    +{{ $row->rating_change }}
                </span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ─── Fallers ─────────────────────────────────────────────────────── --}}
    <div
        class="rounded-lg overflow-hidden
               border border-travertine-300 dark:border-zinc-700/60
               bg-travertine-50 dark:bg-zinc-800/40"
        style="border-left: 4px solid #dc2626;"
    >
        {{-- Header strip — mirror of risers --}}
        <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5
                    border-b border-travertine-300 dark:border-zinc-700/50
                    bg-travertine-75 dark:bg-zinc-900/30">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] flex items-center gap-1.5 min-w-0"
               style="color: #b91c1c;">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                      style="background: #dc2626; color: #fef2f2;">📉</span>
                <span class="truncate">Biggest fallers</span>
            </p>
            <span class="text-xs font-mono shrink-0
                         text-travertine-500 dark:text-zinc-500">last month</span>
        </div>

        {{-- Player rows --}}
        <div class="flex flex-col">
            @foreach($this->fallers as $index => $row)
            <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
               class="flex items-center gap-2.5 px-3 sm:px-4 py-2.5 transition-colors group
                      border-b border-travertine-350 last:border-b-0
                      dark:border-zinc-700/40
                      hover:bg-oxblood/5 dark:hover:bg-zinc-700/20">

                {{-- Position number — top-3 brighter so worst drops stand out --}}
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

                {{-- Delta chip — red loss state, theme-paired surface --}}
                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                             bg-red-100 text-red-800 border border-red-300
                             dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                    {{ $row->rating_change }}
                </span>
            </a>
            @endforeach
        </div>
    </div>

</div>