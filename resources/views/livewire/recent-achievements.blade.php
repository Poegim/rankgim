<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40">

    {{-- Header --}}
    <div class="flex items-center justify-between px-5 pt-5 pb-4">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            🏅 Recent achievements
            @if($this->lastSnapshotDate)
                <span class="font-normal normal-case text-zinc-600 ml-1">— {{ \Carbon\Carbon::parse($this->lastSnapshotDate)->format('d M Y') }}</span>
            @endif
        </p>
    </div>

    @if($this->recentAchievements->isEmpty())
        <p class="text-zinc-500 text-sm text-center py-4 px-5 pb-5">No achievements unlocked yet.</p>
    @else
        <div x-data="{
            canLeft: false, canRight: false,
            update() {
                const el = this.$refs.row;
                this.canLeft  = el.scrollLeft > 4;
                this.canRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 4;
            },
            left()  { this.$refs.row.scrollBy({ left: -320, behavior: 'smooth' }); },
            right() { this.$refs.row.scrollBy({ left:  320, behavior: 'smooth' }); },
        }" x-init="$nextTick(() => update())" class="relative">

            {{-- Left arrow --}}
            <button
                x-on:click="left()"
                x-show="canLeft"
                class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-zinc-900/80 border border-zinc-700 text-zinc-300 hover:text-white hover:border-zinc-500 transition-all cursor-pointer text-xl shadow-lg"
                style="display: none;"
            >‹</button>

            {{-- Right arrow --}}
            <button
                x-on:click="right()"
                x-show="canRight"
                class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-zinc-900/80 border border-zinc-700 text-zinc-300 hover:text-white hover:border-zinc-500 transition-all cursor-pointer text-xl shadow-lg"
                style="display: none;"
            >›</button>

            {{-- Scrollable row — flush to edges, no px padding --}}
            <div x-ref="row"
                 x-on:scroll="update()"
                 class="flex items-stretch gap-3 overflow-x-auto pb-5 px-5"
                 style="scrollbar-width: none; -ms-overflow-style: none;">
                @foreach($this->recentAchievements as $a)
                @php
                    $raceColor = match($a['race']) {
                        'Terran'  => '#3b82f6',
                        'Zerg'    => '#a855f7',
                        'Protoss' => '#eab308',
                        'Random'  => '#f97316',
                        default   => '#71717a',
                    };
                @endphp

                <div class="flex-none flex flex-col w-[82vw] sm:w-64 md:w-72">

                    {{-- Player chip --}}
                    <a href="{{ route('players.show', ['id' => $a['player_id'], 'slug' => \Illuminate\Support\Str::slug($a['player_name'])]) }}"
                       wire:navigate
                       class="flex items-center gap-2 rounded-t-xl px-3 py-2 border-x border-t shrink-0"
                       style="background: linear-gradient(90deg, {{ $raceColor }}30 0%, {{ $raceColor }}10 100%); border-color: {{ $raceColor }}50;">
                        <img src="{{ asset('images/country_flags/' . strtolower($a['country']) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0">
                        <span class="text-xs font-bold text-zinc-100 truncate">{{ $a['player_name'] }}</span>
                        <span class="text-xs font-semibold shrink-0" style="color: {{ $raceColor }};">{{ $a['race'][0] }}</span>
                    </a>

                    {{-- Achievement card — fills remaining height --}}
                    <div class="flex-1 -mt-px [&>div]:w-full [&>div]:h-full [&>div]:rounded-t-none">
                        <x-achievement-card
                            :achievement="$a"
                            :unlocked-at="$a['unlocked_at']"
                            :total-players="$this->totalPlayers"
                        />
                    </div>

                </div>
                @endforeach
            </div>

        </div>
    @endif

</div>