@use('Illuminate\Support\Str')
<div
    class="rounded-lg overflow-hidden
           border border-travertine-300 dark:border-zinc-700/60
           bg-travertine-50 dark:bg-zinc-800/40"
    style="border-left: 4px solid #ca8a04;"
>
    {{-- Header strip — yellow/gold stays constant across themes (semantic = crown/dominance) --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5
                border-b border-travertine-300 dark:border-zinc-700/50
                bg-travertine-75 dark:bg-zinc-900/30">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] flex items-center gap-1.5 min-w-0"
           style="color: #a16207;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #ca8a04; color: #fefce8;">👑</span>
            <span class="truncate">Most dominant</span>
        </p>
        <span class="text-xs font-mono shrink-0
                     text-travertine-500 dark:text-zinc-500">last year</span>
    </div>

    {{-- Player rows --}}
    <div class="flex flex-col">
        @foreach($this->players as $index => $row)
            @php $ratio = $row->total > 0 ? round(($row->wins / $row->total) * 100) : 0; @endphp
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

                {{-- Win-ratio chip — colour follows the ratio so a sub-50% case
                     (unlikely on this list, but kept for safety) reads instantly. --}}
                @if($ratio >= 50)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                                 bg-yellow-100 text-yellow-800 border border-yellow-300
                                 dark:bg-yellow-500/10 dark:text-yellow-400 dark:border-yellow-500/20"
                          title="{{ $row->wins }}/{{ $row->total }} games won">
                        {{ $ratio }}%
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-sm font-bold shrink-0
                                 bg-red-100 text-red-800 border border-red-300
                                 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20"
                          title="{{ $row->wins }}/{{ $row->total }} games won">
                        {{ $ratio }}%
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</div>