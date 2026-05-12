<div wire:poll.{{ $pollSeconds }}s class="space-y-3"
     x-data="{
         selectedRace: 'all',
         showAll: false,
         initialLimit: 12,
         matches(race) {
             return this.selectedRace === 'all' || this.selectedRace === race;
         }
     }">

    {{-- Widget header — matches the dashboard widget contract --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            📺 Featured streams
        </p>
        @if ($this->lastFetchedAt)
            <p class="text-xs {{ $this->isStale ? 'text-amber-400' : 'text-zinc-500' }}">
                @if ($this->isStale)
                    stale · updated {{ $this->lastFetchedAt->diffForHumans() }}
                @else
                    updated {{ $this->lastFetchedAt->diffForHumans() }}
                @endif
            </p>
        @endif
    </div>

    @if (count($this->streams) === 0)
        <p class="text-sm text-zinc-400">
            No SOOP streams live right now.
        </p>
        <div>
                <a href=" {{ route('streams.index') }}"
                   class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors px-3 py-1.5 rounded-md hover:bg-zinc-700/40">
                    Show all
                </a>           
        </div>
    @else
        {{-- Race filters — reset collapse state when race changes so the user always sees the first batch of the new filter --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <button
                @click="selectedRace = 'all'; showAll = false"
                :class="selectedRace === 'all' ? 'bg-rose-600 text-white' : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700'"
                class="rounded-md px-3 py-1.5 text-xs font-bold uppercase transition"
            >
                All
            </button>

            @foreach(['terran', 'protoss', 'zerg'] as $race)
                <button
                    @click="selectedRace = '{{ $race }}'; showAll = false"
                    :class="selectedRace === '{{ $race }}' ? 'border-rose-500 bg-rose-500/10 text-white' : 'border-zinc-700 text-zinc-400 hover:bg-zinc-800'"
                    class="rounded-md border px-3 py-1.5 text-xs font-bold uppercase transition"
                >
                    {{ ucfirst($race) }}
                </button>
            @endforeach
            <div>
                <a href=" {{ route('streams.index') }}"
                   class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors px-3 py-1.5 rounded-md hover:bg-zinc-700/40">
                    Show all
                </a>            
            </div>
        </div>


        {{--
            Each card carries:
              - data-race attribute (used for filtering)
              - visibleIndex — its rank within the currently filtered subset
              - filteredCount — total cards in current subset
            Both are computed in Alpine via x-effect so collapsing always respects the active race filter.
            That avoids the "pick Protoss + Show less, get half a row" problem — limiting happens AFTER race filtering.
        --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
             x-data="{
                 filteredCount: 0,
                 recount() {
                     this.filteredCount = Array.from($el.querySelectorAll('[data-race]'))
                         .filter(el => this.matches(el.dataset.race))
                         .length;
                 }
             }"
             x-init="recount()"
             x-effect="selectedRace; recount()"
        >
            @foreach ($this->streams as $i => $s)
                <a href="{{ $s['play_url'] }}"
                    data-race="{{ strtolower($s['race']) }}"
                    x-show="matches('{{ strtolower($s['race']) }}') && (showAll || visibleIndex() < initialLimit)"
                    x-data="{
                        visibleIndex() {
                            // Count how many same-race-matching siblings come before this one
                            let count = 0;
                            let el = $el.previousElementSibling;
                            while (el) {
                                if (el.dataset.race && matches(el.dataset.race)) count++;
                                el = el.previousElementSibling;
                            }
                            return count;
                        }
                    }"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-start gap-3 rounded-lg border border-zinc-700/60 bg-zinc-900/40 p-3 transition hover:border-rose-500/60 hover:bg-zinc-800/70"
                >
                    @if ($s['thumbnail'])
                        <img
                            src="{{ $s['thumbnail'] }}"
                            alt=""
                            class="block h-16 w-24 shrink-0 rounded object-cover"
                            loading="lazy"
                        >
                    @endif

                    <div class="min-w-0 flex-1 space-y-1">
                        {{-- Row 1: label (player name) + race tag --}}
                        <div class="flex items-center gap-2">
                            <span class="truncate text-sm font-semibold text-zinc-100">
                                {{ $s['label'] }}
                            </span>
                            @if ($s['race'])
                                <span
                                    class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                                    style="background: color-mix(in srgb, var(--color-race-{{ $s['race'] }}) 20%, transparent); color: var(--color-race-{{ $s['race'] }});"
                                >
                                    {{ ucfirst($s['race']) }}
                                </span>
                            @endif
                        </div>

                        {{-- Row 3: meta (viewers + start time) --}}
                        <p class="text-xs text-zinc-500 italic">
                            <span class="font-medium text-zinc-400">
                            👥 {{ number_format($s['viewers']) }}
                            </span>
                            @if ($s['started_at'])
                                · {{ $s['started_at']->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach

            {{-- Show more / less toggle — only rendered when current filter has more than initialLimit results --}}
            <div class="col-span-full flex justify-center mt-2"
                 x-show="filteredCount > initialLimit">
                <button
                    @click="showAll = !showAll"
                    class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors px-3 py-1.5 rounded-md hover:bg-zinc-700/40"
                >
                    <span x-text="showAll ? 'Show less ↑' : `Show more (${filteredCount - initialLimit}) ↓`"></span>
                </button>
            </div>
        </div>
    @endif
</div>