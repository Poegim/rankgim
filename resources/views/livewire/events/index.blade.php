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
    {{-- ─── Page header ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Events</flux:heading>
            <flux:subheading>Tournaments, showmatches &amp; community events</flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            {{-- View toggle --}}
            <div class="flex rounded-lg overflow-hidden border border-zinc-700">
                @foreach(['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $key => $label)
                    <button wire:click="setView('{{ $key }}')"
                        class="px-3 py-1.5 text-sm transition-colors {{ $view === $key ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:text-white' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @auth
                <button wire:click="openAddModal"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Event
                </button>
            @endauth
        </div>
    </div>

    {{-- ─── Filter row: type filter + tz toggle ──────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <div class="flex gap-1.5">
            <button wire:click="setTypeFilter('all')" class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors border
                    {{ $typeFilter === 'all'
                        ? 'bg-zinc-700 text-white border-zinc-600'
                        : 'text-zinc-400 border-zinc-700 hover:text-white hover:border-zinc-500' }}">
                All
            </button>
            <button wire:click="setTypeFilter('stream')" class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition-colors border
                    {{ $typeFilter === 'stream'
                        ? 'bg-purple-500/20 text-purple-300 border-purple-500/40'
                        : 'text-zinc-400 border-zinc-700 hover:text-purple-300 hover:border-purple-500/30' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 10l4.553-2.277A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a1 1 0 011-1h10a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z" />
                </svg>
                Stream
            </button>
            <button wire:click="setTypeFilter('open')" class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition-colors border
                    {{ $typeFilter === 'open'
                        ? 'bg-amber-500/20 text-amber-300 border-amber-500/40'
                        : 'text-zinc-400 border-zinc-700 hover:text-amber-300 hover:border-amber-500/30' }}">
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
                ? 'bg-amber-500/10 text-amber-300 border-amber-500/40'
                : 'bg-zinc-800/40 text-zinc-400 border-zinc-700 hover:text-zinc-200 hover:border-zinc-600'">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span x-text="showLocal ? 'Show CET' : 'Show my time'"></span>
        </button>
    </div>

    {{-- ─── Timeline ─────────────────────────────────────────────────── --}}
    @if($this->groupedEvents->isEmpty())
        <div class="text-center py-16 text-zinc-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-lg">No {{ $view === 'past' ? 'past' : 'upcoming' }} events</p>
            @auth
                <button wire:click="openAddModal" class="mt-3 text-sm text-amber-400 hover:text-amber-300">
                    Add the first one
                </button>
            @endauth
        </div>
    @else
        <div class="relative">
            {{-- Timeline vertical line — desktop only --}}
            <div class="absolute left-[19px] top-0 bottom-0 w-px bg-zinc-700/50 hidden sm:block"></div>

            @foreach($this->groupedEvents as $month => $events)
                {{-- Month header --}}
                <div class="relative flex items-center gap-3 mb-4 mt-8 first:mt-0">
                    <div class="relative z-10 w-10 h-10 rounded-full bg-zinc-800 border border-zinc-600 hidden sm:flex items-center justify-center text-xs font-mono text-zinc-300">
                        {{ \Illuminate\Support\Str::substr($month, 0, 3) }}
                    </div>
                    <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider">{{ $month }}</h2>
                </div>

                @foreach($events as $event)
                    <x-events.card :event="$event"
                                   :can-manage="auth()->check() && auth()->user()->canManageGames()" />
                @endforeach
            @endforeach
        </div>
    @endif

    {{-- ─── Delete confirmation modal ────────────────────────────────── --}}
    @if($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            wire:click.self="$set('confirmingDeleteId', null)">
            <div class="bg-zinc-900 border border-zinc-700 rounded-xl p-6 w-full max-w-sm mx-4">
                <h3 class="text-lg font-semibold text-white mb-2">Delete event?</h3>
                <p class="text-sm text-zinc-400 mb-5">This action cannot be undone.</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('confirmingDeleteId', null)"
                        class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button wire:click="delete"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── Add / Edit modal (partial, retains scope) ────────────────── --}}
    @include('livewire.events._modal')
</div>