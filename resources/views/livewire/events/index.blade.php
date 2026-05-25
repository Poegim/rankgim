<div x-data="{
        userTz: Intl.DateTimeFormat().resolvedOptions().timeZone,
        showLocal: false,
        formatTime(iso, tz) {
            return new Intl.DateTimeFormat('en-GB', {
                day: '2-digit', month: 'short',
                hour: '2-digit', minute: '2-digit', hour12: false,
                timeZone: tz,
            }).format(new Date(iso)).replace(',', '');
        },
        tzAbbr(iso, tz) {
            const parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: tz, timeZoneName: 'short',
            }).formatToParts(new Date(iso));
            const part = parts.find(p => p.type === 'timeZoneName');
            return part ? part.value : tz;
        },
        toggleTz() { this.showLocal = !this.showLocal; },
    }">
    {{-- ═══════════════════════════════════════════════════════════════════
         Page header
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Events</flux:heading>
            <flux:subheading>Tournaments, showmatches &amp; community events</flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            {{-- View toggle (Upcoming / Past / All) — segmented button group --}}
            <div class="flex rounded-lg overflow-hidden
                        border border-travertine-300 dark:border-zinc-700">
                @foreach(['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $key => $label)
                    <button wire:click="setView('{{ $key }}')"
                        @class([
                            'px-3 py-1.5 text-sm transition-colors',
                            // Active state
                            'bg-oxblood !text-oxblood-content dark:bg-zinc-700 dark:!text-white' => $view === $key,
                            // Inactive state
                            'text-travertine-600 hover:text-travertine-900 hover:bg-travertine-200 dark:text-zinc-400 dark:hover:text-white' => $view !== $key,
                        ])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @auth
                <button wire:click="openAddModal"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                           bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200
                           dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 dark:hover:bg-amber-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Event
                </button>
            @endauth
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         Filter row: type filter (All/Stream/Open) + timezone toggle
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <div class="flex gap-1.5">
            {{-- "All" filter — oxblood active in light, zinc active in dark --}}
            <button wire:click="setTypeFilter('all')"
                @class([
                    'px-4 py-1.5 rounded-full text-sm font-medium transition-colors border',
                    'bg-oxblood !text-oxblood-content border-oxblood dark:bg-zinc-700 dark:!text-white dark:border-zinc-600' => $typeFilter === 'all',
                    'text-travertine-600 border-travertine-300 hover:text-travertine-900 hover:border-travertine-400 dark:text-zinc-400 dark:border-zinc-700 dark:hover:text-white dark:hover:border-zinc-500' => $typeFilter !== 'all',
                ])>
                All
            </button>

            {{-- "Stream" filter — purple active --}}
            <button wire:click="setTypeFilter('stream')"
                @class([
                    'flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition-colors border',
                    'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-500/20 dark:text-purple-300 dark:border-purple-500/40' => $typeFilter === 'stream',
                    'text-travertine-600 border-travertine-300 hover:text-purple-700 hover:border-purple-300 dark:text-zinc-400 dark:border-zinc-700 dark:hover:text-purple-300 dark:hover:border-purple-500/30' => $typeFilter !== 'stream',
                ])>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 10l4.553-2.277A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a1 1 0 011-1h10a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z" />
                </svg>
                Stream
            </button>

            {{-- "Open" filter — amber active --}}
            <button wire:click="setTypeFilter('open')"
                @class([
                    'flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition-colors border',
                    'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/40' => $typeFilter === 'open',
                    'text-travertine-600 border-travertine-300 hover:text-amber-700 hover:border-amber-300 dark:text-zinc-400 dark:border-zinc-700 dark:hover:text-amber-300 dark:hover:border-amber-500/30' => $typeFilter !== 'open',
                ])>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                Open
            </button>
        </div>

        {{-- Timezone toggle (Alpine, no persistence; refresh = back to CET) --}}
        <button type="button" x-on:click="toggleTz()"
            class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs transition-colors"
            :class="showLocal
                ? 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/40'
                : 'bg-travertine-50 text-travertine-600 border-travertine-300 hover:text-travertine-900 hover:border-travertine-400 dark:bg-zinc-800/40 dark:text-zinc-400 dark:border-zinc-700 dark:hover:text-zinc-200 dark:hover:border-zinc-600'">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span x-text="showLocal ? 'Show CET' : 'Show my time'"></span>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         Timeline
         ═══════════════════════════════════════════════════════════════════ --}}
    @if($this->groupedEvents->isEmpty())
        <div class="text-center py-16
                    text-travertine-500 dark:text-zinc-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-lg">No {{ $view === 'past' ? 'past' : 'upcoming' }} events</p>
            @auth
                <button wire:click="openAddModal"
                        class="mt-3 text-sm
                               text-oxblood hover:text-oxblood-deep
                               dark:text-amber-400 dark:hover:text-amber-300">
                    Add the first one
                </button>
            @endauth
        </div>
    @else
        <div class="relative">
            {{-- Timeline vertical line — desktop only --}}
            <div class="absolute left-[19px] top-0 bottom-0 w-px hidden sm:block
                        bg-travertine-300 dark:bg-zinc-700/50"></div>

            @foreach($this->groupedEvents as $month => $events)
                {{-- Month header — circle marker on the timeline line --}}
                <div class="relative flex items-center gap-3 mb-4 mt-8 first:mt-0">
                    <div class="relative z-10 w-10 h-10 rounded-full border hidden sm:flex items-center justify-center text-xs font-mono
                                bg-travertine-100 border-travertine-400 text-travertine-800
                                dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300">
                        {{ \Illuminate\Support\Str::substr($month, 0, 3) }}
                    </div>
                    <h2 class="font-cinzel text-sm font-medium uppercase tracking-[0.15em]
                               text-oxblood dark:text-zinc-400">
                        {{ $month }}
                    </h2>
                </div>

                @foreach($events as $event)
                    <x-events.card :event="$event"
                                   :can-manage="auth()->check() && auth()->user()->canManageGames()" />
                @endforeach
            @endforeach
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         Delete confirmation modal
         ═══════════════════════════════════════════════════════════════════ --}}
    @if($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            wire:click.self="$set('confirmingDeleteId', null)">
            <div class="rounded-xl p-6 w-full max-w-sm mx-4
                        bg-travertine-50 border border-travertine-300
                        dark:bg-zinc-900 dark:border-zinc-700">
                <h3 class="text-lg font-semibold mb-2
                           text-travertine-900 dark:text-white">Delete event?</h3>
                <p class="text-sm mb-5
                          text-travertine-600 dark:text-zinc-400">
                    This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('confirmingDeleteId', null)"
                        class="px-4 py-2 text-sm transition-colors
                               text-travertine-600 hover:text-travertine-900
                               dark:text-zinc-400 dark:hover:text-white">
                        Cancel
                    </button>
                    <button wire:click="delete"
                        class="px-4 py-2 text-sm font-medium rounded-lg border transition-colors
                               bg-red-100 text-red-800 border-red-300 hover:bg-red-200
                               dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20 dark:hover:bg-red-500/20">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         Add / Edit modal (partial, retains scope)
         ═══════════════════════════════════════════════════════════════════ --}}
    @include('livewire.events._modal')
</div>