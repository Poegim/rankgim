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
                    class="group flex items-stretch gap-0 overflow-hidden rounded-lg border border-zinc-700/60 bg-zinc-900/70 transition hover:border-rose-500/60 hover:bg-zinc-900"
                >
                    @php
                        $widgetPlatform = $s['platform'] ?? 'soop';
                        $widgetBadge = match ($widgetPlatform) {
                            'twitch' => ['label' => 'TW', 'bg' => '#9146ff'],
                            'soop'   => ['label' => 'SP', 'bg' => '#ef4444'],
                            default  => ['label' => strtoupper(substr($widgetPlatform, 0, 2)), 'bg' => '#52525b'],
                        };
                        $widgetAccent = $s['race']
                            ? "var(--color-race-{$s['race']})"
                            : 'rgb(82, 82, 91)';
                    @endphp

                    {{-- Race accent bar on the left edge --}}
                    <div class="w-1 shrink-0" style="background: {{ $widgetAccent }};"></div>

                    {{-- Thumbnail with overlay platform badge --}}
                    <div class="relative aspect-video w-28 shrink-0 overflow-hidden bg-zinc-950">
                        @if ($s['thumbnail'])
                            <img
                                src="{{ $s['thumbnail'] }}"
                                alt=""
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                        @endif
                        <span
                            class="absolute left-1 top-1 rounded px-1 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white"
                            style="background: {{ $widgetBadge['bg'] }};"
                        >
                            {{ $widgetBadge['label'] }}
                        </span>
                    </div>

                    {{-- Content: name + race chip + viewers --}}
                    <div class="min-w-0 flex-1 space-y-1 p-2">
                        <div class="flex items-center gap-2">
                            @if ($s['is_favorite'] ?? false)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5 shrink-0 text-amber-400" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            <span class="truncate text-sm font-semibold text-zinc-50">
                                {{ $s['label'] ?: $s['user_nick'] }}
                            </span>
                            @if ($s['race'])
                                <span
                                    class="shrink-0 rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white"
                                    style="background: {{ $widgetAccent }};"
                                >
                                    {{ $s['race'] }}
                                </span>
                            @endif
                        </div>

                        <p class="text-[11px] text-zinc-400">
                            <span class="inline-flex items-center gap-1 font-medium text-zinc-300">
                                <span class="inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-rose-500"></span>
                                {{ number_format($s['viewers']) }}
                            </span>
                            @if ($s['started_at'])
                                <span class="text-zinc-500">· {{ $s['started_at']->diffForHumans() }}</span>
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