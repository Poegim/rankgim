<div class="rounded-lg p-3 sm:p-5
            border border-travertine-300 dark:border-zinc-700/60
            bg-travertine-50 dark:bg-zinc-800/40">

    {{-- Header — Cinzel oxblood signature, modern almanac convention --}}
    <div class="flex items-center justify-between mb-4">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em]
                  text-oxblood dark:text-zinc-500">
            🏅 Recent achievements
            @if($this->lastSnapshotDate)
                {{-- Snapshot date — faint hint, normal case (not Cinzel) --}}
                <span class="font-sans normal-case tracking-normal ml-1
                             font-normal text-travertine-500 dark:text-zinc-600">
                    — {{ \Carbon\Carbon::parse($this->lastSnapshotDate)->format('d M Y') }}
                </span>
            @endif
        </p>
        <a href="{{ route('achievements.index') }}"
           class="text-xs transition-colors
                  text-travertine-600 hover:text-oxblood
                  dark:text-zinc-400 dark:hover:text-zinc-200"
           wire:navigate>
            View all →
        </a>
    </div>

    @if($this->recentAchievements->isEmpty())
        <p class="text-sm text-center py-4
                  text-travertine-500 dark:text-zinc-500">
            No achievements unlocked yet.
        </p>
    @else

        {{-- Grid: 2 cols mobile → 4 desktop. No carousel, no scroll. --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($this->visibleAchievements as $a)
                @php
                    // Race base color — resolved from CSS custom properties defined in
                    // app.css. Vars auto-adjust per theme via :root:not(.dark) overrides.
                    $raceColorVar = match($a['race']) {
                        'Terran'  => 'var(--color-race-terran)',
                        'Zerg'    => 'var(--color-race-zerg)',
                        'Protoss' => 'var(--color-race-protoss)',
                        'Random'  => 'var(--color-race-random)',
                        default   => 'var(--color-race-unknown)',
                    };
                @endphp

                <div class="flex flex-col">

                    {{-- Player chip — race-tinted gradient header.                  --}}
                    {{-- Race vars auto-adjust per theme, so the same color-mix      --}}
                    {{-- percentages work in both light (darker race hues) and dark  --}}
                    {{-- (brighter race hues).                                        --}}
                    <a href="{{ route('players.show', ['id' => $a['player_id'], 'slug' => \Illuminate\Support\Str::slug($a['player_name'])]) }}"
                       wire:navigate
                       class="flex items-center gap-2 rounded-t-lg px-3 py-2 border-x border-t shrink-0
                              text-travertine-900 dark:text-zinc-100"
                       style="background: linear-gradient(90deg, color-mix(in srgb, {{ $raceColorVar }} 18%, transparent) 0%, color-mix(in srgb, {{ $raceColorVar }} 6%, transparent) 100%);
                              border-color: color-mix(in srgb, {{ $raceColorVar }} 35%, transparent);">
                        <img src="{{ asset('images/country_flags/' . strtolower($a['country']) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0"
                             alt="{{ $a['country'] }}">
                        <span class="text-xs font-bold truncate">{{ $a['player_name'] }}</span>
                        <span class="text-xs font-semibold shrink-0" style="color: {{ $raceColorVar }};">{{ $a['race'][0] }}</span>
                    </a>

                    {{-- Achievement card — fills remaining height so all cards in a row line up.
                         Overriding rounded-t to none so it joins seamlessly with the player chip. --}}
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

        {{-- Show more / Show less — only when there's more than INITIAL_LIMIT --}}
        @if($this->canShowMore)
            <div class="flex justify-center mt-4">
                <button wire:click="toggleShowMore"
                        class="text-xs transition-colors px-3 py-1.5 rounded-md
                               text-travertine-600 hover:text-oxblood hover:bg-travertine-200/60
                               dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700/40">
                    {{ $showMore ? 'Show less ↑' : 'Show more ↓' }}
                </button>
            </div>
        @endif

    @endif

</div>