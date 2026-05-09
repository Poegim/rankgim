<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-3 sm:p-5">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            🏅 Recent achievements
            @if($this->lastSnapshotDate)
                <span class="font-normal normal-case text-zinc-600 ml-1">— {{ \Carbon\Carbon::parse($this->lastSnapshotDate)->format('d M Y') }}</span>
            @endif
        </p>
        <a href="{{ route('achievements.index') }}"
           class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors"
           wire:navigate>
            View all →
        </a>
    </div>

    @if($this->recentAchievements->isEmpty())
        <p class="text-zinc-500 text-sm text-center py-4">No achievements unlocked yet.</p>
    @else

        {{-- Grid: 2 cols mobile → 4 desktop. No carousel, no scroll. --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($this->visibleAchievements as $a)
                @php
                    // Race base color — resolved from CSS custom properties defined in app.css.
                    // Use var() in inline styles so Tailwind JIT never strips these values.
                    $raceColorVar = match($a['race']) {
                        'Terran'  => 'var(--color-race-terran)',
                        'Zerg'    => 'var(--color-race-zerg)',
                        'Protoss' => 'var(--color-race-protoss)',
                        'Random'  => 'var(--color-race-random)',
                        default   => 'var(--color-race-unknown)',
                    };
                @endphp

                <div class="flex flex-col">

                    {{-- Player chip --}}
                    <a href="{{ route('players.show', ['id' => $a['player_id'], 'slug' => \Illuminate\Support\Str::slug($a['player_name'])]) }}"
                       wire:navigate
                       class="flex items-center gap-2 rounded-t-xl px-3 py-2 border-x border-t shrink-0"
                       style="background: linear-gradient(90deg, color-mix(in srgb, {{ $raceColorVar }} 18%, transparent) 0%, color-mix(in srgb, {{ $raceColorVar }} 6%, transparent) 100%); border-color: color-mix(in srgb, {{ $raceColorVar }} 30%, transparent);">
                        <img src="{{ asset('images/country_flags/' . strtolower($a['country']) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0"
                             alt="{{ $a['country'] }}">
                        <span class="text-xs font-bold text-zinc-100 truncate">{{ $a['player_name'] }}</span>
                        <span class="text-xs font-semibold shrink-0" style="color: {{ $raceColorVar }};">{{ $a['race'][0] }}</span>
                    </a>

                    {{-- Achievement card — fills remaining height so all cards in a row line up --}}
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
                        class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors px-3 py-1.5 rounded-md hover:bg-zinc-700/40">
                    {{ $showMore ? 'Show less ↑' : 'Show more ↓' }}
                </button>
            </div>
        @endif

    @endif

</div>