<div class="flex flex-col gap-4">
    <h2 class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
        Most time in top 3
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach ([
            1 => ['medal' => '🥇', 'label' => 'Rank #1', 'accent' => 'text-amber-700 dark:text-amber-400',  'pillClass' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20'],
            2 => ['medal' => '🥈', 'label' => 'Rank #2', 'accent' => 'text-zinc-500 dark:text-zinc-400',    'pillClass' => 'bg-travertine-100 text-travertine-600 border-travertine-300 dark:bg-zinc-700 dark:text-zinc-300 dark:border-zinc-600'],
            3 => ['medal' => '🥉', 'label' => 'Rank #3', 'accent' => 'text-orange-700 dark:text-orange-400', 'pillClass' => 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20'],
        ] as $rank => $s)

        @php $players = $this->podium->get($rank, collect()); @endphp

        <div class="rounded-xl border overflow-hidden
            border-travertine-300 bg-travertine-50
            dark:border-zinc-700/40 dark:bg-zinc-900/50">

            {{-- Card header --}}
            <div class="flex items-center gap-2 px-4 py-2.5 border-b
                bg-travertine-100 border-travertine-300
                dark:bg-zinc-800/60 dark:border-zinc-700/40">
                <span class="text-base leading-none">{{ $s['medal'] }}</span>
                <span class="text-xs font-semibold {{ $s['accent'] }}">{{ $s['label'] }}</span>
            </div>

            @if ($players->isNotEmpty())

            {{-- Hero player (#1 in this rank column) --}}
            @php $hero = $players->first(); @endphp
            <div class="flex items-center gap-3 px-4 py-3 border-b
                border-travertine-300 dark:border-zinc-700/40">
                <img src="{{ asset('images/country_flags/' . strtolower($hero->country_code) . '.svg') }}"
                     class="w-6 h-4 rounded-sm shrink-0">
                <a href="{{ route('players.show', ['id' => $hero->id, 'slug' => Str::slug($hero->name)]) }}"
                   class="text-sm font-bold flex-1 min-w-0 truncate hover:underline
                       text-travertine-900 dark:text-white">
                    {{ $hero->name }}
                </a>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full border shrink-0 {{ $s['pillClass'] }}">
                    {{ $hero->months_count }}mo
                </span>
            </div>

            {{-- Rest of players in this rank bucket --}}
            <div class="py-1">
                @foreach ($players->skip(1) as $row)
                <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
                   class="flex items-center gap-2 px-4 py-1.5 transition-colors group
                       hover:bg-oxblood/5 dark:hover:bg-zinc-800/50">
                    <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                         class="w-4 h-3 rounded-sm shrink-0">
                    <span class="text-sm flex-1 min-w-0 truncate
                        text-travertine-700 group-hover:text-travertine-900
                        dark:text-zinc-400 dark:group-hover:text-zinc-200">
                        {{ $row->name }}
                    </span>
                    <span class="text-xs shrink-0 font-mono
                        text-travertine-400 dark:text-zinc-600">
                        {{ $row->months_count }}mo
                    </span>
                </a>
                @endforeach
            </div>

            @else
            <div class="px-4 py-6 text-center text-xs text-travertine-400 dark:text-zinc-600">
                No data
            </div>
            @endif

        </div>
        @endforeach
    </div>
</div>