<div wire:poll.{{ $pollSeconds }}s class="space-y-3"
     x-data="{
         selectedRace: 'all',
         showAll: false,
         initialLimit: 12,
         matches(race) {
             return this.selectedRace === 'all' || this.selectedRace === race;
         }
     }">

    {{-- Widget header — Cinzel oxblood signature --}}
    <div class="flex items-center justify-between mb-3">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em]
                  text-oxblood dark:text-zinc-500">
            📺 Featured streams
        </p>
        @if ($this->lastFetchedAt)
            {{-- Stale indicator stays amber across themes — it's a warning state. --}}
            {{-- Light variant uses amber-700 for cream contrast.                  --}}
            <p @class([
                'text-xs',
                'text-amber-700 dark:text-amber-400' => $this->isStale,
                'text-travertine-500 dark:text-zinc-500' => ! $this->isStale,
            ])>
                @if ($this->isStale)
                    stale · updated {{ $this->lastFetchedAt->diffForHumans() }}
                @else
                    updated {{ $this->lastFetchedAt->diffForHumans() }}
                @endif
            </p>
        @endif
    </div>

    @if (count($this->streams) === 0)
        <p class="text-sm text-travertine-600 dark:text-zinc-400">
            No SOOP streams live right now.
        </p>
        <div>
            <a href="{{ route('streams.index') }}"
               class="text-xs transition-colors px-3 py-1.5 rounded-md
                      text-travertine-600 hover:text-oxblood hover:bg-travertine-200/60
                      dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700/40">
                Show all
            </a>
        </div>
    @else
        {{-- Race filters — reset collapse state when race changes so the user always sees the first batch of the new filter --}}
        <div class="mb-6 flex flex-wrap gap-2">
            {{-- "All" button — active uses oxblood (brand CTA), inactive uses travertine --}}
            <button
                @click="selectedRace = 'all'; showAll = false"
                :class="selectedRace === 'all'
                    ? 'bg-oxblood text-oxblood-content dark:bg-rose-600 dark:text-white'
                    : 'bg-travertine-200 text-travertine-700 hover:bg-travertine-300 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700'"
                class="rounded-md px-3 py-1.5 text-xs font-bold uppercase transition"
            >
                All
            </button>

            {{-- Race buttons — active uses race-specific tint via CSS vars --}}
            @foreach(['terran', 'protoss', 'zerg'] as $race)
                <button
                    @click="selectedRace = '{{ $race }}'; showAll = false"
                    :class="selectedRace === '{{ $race }}'
                        ? 'border-oxblood bg-oxblood/10 text-oxblood dark:border-rose-500 dark:bg-rose-500/10 dark:text-white'
                        : 'border-travertine-300 text-travertine-600 hover:bg-travertine-200 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                    class="rounded-md border px-3 py-1.5 text-xs font-bold uppercase transition"
                >
                    {{ ucfirst($race) }}
                </button>
            @endforeach

            <div>
                <a href="{{ route('streams.index') }}"
                   class="text-xs transition-colors px-3 py-1.5 rounded-md inline-block
                          text-travertine-600 hover:text-oxblood hover:bg-travertine-200/60
                          dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700/40">
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
                    data-race="{{ strtolower($s['race'] ?? '') }}"
                    x-show="matches('{{ strtolower($s['race'] ?? '') }}') && (showAll || visibleIndex() < initialLimit)"
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
                    class="group flex items-stretch gap-0 overflow-hidden rounded-lg transition
                           border border-travertine-300 bg-travertine-50 hover:border-oxblood/50 hover:bg-travertine-75
                           dark:border-zinc-700/60 dark:bg-zinc-900/70 dark:hover:border-rose-500/60 dark:hover:bg-zinc-900"
                >
                    @php
                        $widgetPlatform = $s['platform'] ?? 'soop';
                        // Platform badges — brand colors, identical in both themes
                        $widgetBadge = match ($widgetPlatform) {
                            'twitch' => ['label' => 'TW', 'bg' => '#9146ff'],
                            'soop'   => ['label' => 'SP', 'bg' => '#ef4444'],
                            default  => ['label' => strtoupper(substr($widgetPlatform, 0, 2)), 'bg' => '#52525b'],
                        };
                        // Race accent — uses CSS var which auto-adjusts per theme
                        $widgetAccent = ($s['race'] ?? null)
                            ? "var(--color-race-{$s['race']})"
                            : 'var(--color-race-unknown)';
                    @endphp

                    {{-- Race accent bar on the left edge --}}
                    <div class="w-1 shrink-0" style="background: {{ $widgetAccent }};"></div>

                    {{-- Thumbnail with overlay platform badge.                       --}}
                    {{-- Thumbnail bg stays near-black in BOTH themes — it's the      --}}
                    {{-- fallback for when image hasn't loaded; black gives best       --}}
                    {{-- contrast for stream thumbnails (game UI is usually dark).     --}}
                    <div class="relative aspect-video w-28 shrink-0 overflow-hidden bg-zinc-950">
                        @if ($s['thumbnail'])
                            <img
                                src="{{ $s['thumbnail'] }}"
                                alt=""
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                        @endif
                        {{-- Platform badge — !text-white preserved (sits on colored bg) --}}
                        <span
                            class="absolute left-1 top-1 rounded px-1 py-0.5 text-[9px] font-bold uppercase tracking-wider !text-white"
                            style="background: {{ $widgetBadge['bg'] }};"
                        >
                            {{ $widgetBadge['label'] }}
                        </span>
                    </div>

                    {{-- Content: name + race chip + viewers --}}
                    <div class="min-w-0 flex-1 space-y-1 p-2">
                        <div class="flex items-center gap-2">
                            @if ($s['is_favorite'] ?? false)
                                {{-- Favorite star — amber in dark, deeper amber in light --}}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                     class="h-3.5 w-3.5 shrink-0 text-amber-600 dark:text-amber-400" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            <span class="truncate text-sm font-semibold
                                         text-travertine-900 dark:text-zinc-50">
                                {{ $s['label'] ?: $s['user_nick'] }}
                            </span>
                            @if ($s['race'])
                                {{-- Race pill — !text-white forced because race bg is colored. --}}
                                <span
                                    class="shrink-0 rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider !text-white"
                                    style="background: {{ $widgetAccent }};"
                                >
                                    {{ $s['race'] }}
                                </span>
                            @endif
                        </div>

                        <p class="text-[11px] text-travertine-600 dark:text-zinc-400">
                            <span class="inline-flex items-center gap-1 font-medium
                                         text-travertine-800 dark:text-zinc-300">
                                {{-- LIVE pulse dot — solid red in both themes --}}
                                <span class="inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-red-500"></span>
                                {{ number_format($s['viewers']) }}
                            </span>
                            @if ($s['started_at'])
                                <span class="text-travertine-500 dark:text-zinc-500">· {{ $s['started_at']->diffForHumans() }}</span>
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
                    class="text-xs transition-colors px-3 py-1.5 rounded-md
                           text-travertine-600 hover:text-oxblood hover:bg-travertine-200/60
                           dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700/40"
                >
                    <span x-text="showAll ? 'Show less ↑' : `Show more (${filteredCount - initialLimit}) ↓`"></span>
                </button>
            </div>
        </div>
    @endif
</div>