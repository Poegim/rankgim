<div class="flex flex-col gap-4">
    <h2 class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
        Most time in top 10
    </h2>

    <div class="rounded-xl border overflow-hidden
        border-travertine-300 bg-travertine-50
        dark:border-zinc-700/40 dark:bg-zinc-900/50">

        {{-- Header row --}}
        <div class="grid px-4 py-2.5 border-b
            bg-travertine-100 border-travertine-300
            dark:bg-zinc-800/60 dark:border-zinc-700/40"
             style="grid-template-columns: 2rem 1fr 4.5rem 4rem;">
            <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500">#</span>
            <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500">Player</span>
            <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500 text-right">Race</span>
            <span class="text-[10px] uppercase tracking-wider text-travertine-500 dark:text-zinc-500 text-right">Months</span>
        </div>

        {{-- Rows --}}
        @foreach ($this->leaders as $i => $row)
        <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
           class="grid items-center px-4 py-2.5 transition-colors group border-b last:border-b-0
               border-travertine-350 dark:border-zinc-700/30
               hover:bg-oxblood/5 dark:hover:bg-zinc-800/50"
           style="grid-template-columns: 2rem 1fr 4.5rem 4rem;">

            {{-- Rank number --}}
            <span class="text-sm font-mono text-travertine-400 dark:text-zinc-600">{{ $i + 1 }}</span>

            {{-- Flag + name --}}
            <div class="flex items-center gap-2.5 min-w-0">
                <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                     class="rounded-sm shrink-0" style="width: 22px; height: 15px;">
                <span class="text-sm font-medium truncate transition-colors
                    text-travertine-800 group-hover:text-travertine-900
                    dark:text-zinc-300 dark:group-hover:text-white">
                    {{ $row->name }}
                </span>
            </div>

            {{-- Race — CSS vars, single source of truth --}}
            @php
                $raceKey = match($row->race) {
                    'Terran'  => 'terran',
                    'Zerg'    => 'zerg',
                    'Protoss' => 'protoss',
                    'Random'  => 'random',
                    default   => 'unknown',
                };
            @endphp
            <span class="text-xs text-right shrink-0"
                  style="color: var(--color-race-{{ $raceKey }})">{{ $row->race }}</span>

            {{-- Months chip --}}
            <div class="flex justify-end">
                <span class="text-xs font-medium px-2 py-0.5 rounded-full border
                    bg-emerald-100 text-emerald-800 border-emerald-300
                    dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                    {{ $row->months_count }}mo
                </span>
            </div>

        </a>
        @endforeach

    </div>
</div>