<div x-data="{
        userTz: Intl.DateTimeFormat().resolvedOptions().timeZone,
        showLocal: false,
        formatTime(iso, tz) {
            // Format: '30 Mar, 18:00' — matches CET formatting on the server.
            return new Intl.DateTimeFormat('en-GB', {
                day: '2-digit', month: 'short',
                hour: '2-digit', minute: '2-digit', hour12: false,
                timeZone: tz,
            }).format(new Date(iso)).replace(',', '');
        },
        tzAbbr(iso, tz) {
            // Best-effort short timezone abbreviation (CET, EST, JST, etc.)
            const parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: tz, timeZoneName: 'short',
            }).formatToParts(new Date(iso));
            const part = parts.find(p => p.type === 'timeZoneName');
            return part ? part.value : tz;
        },
        toggleTz() { this.showLocal = !this.showLocal; },
    }">
    {{-- ═══════════════════════════════════════════════════════════════════
         Header
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Events</flux:heading>
            <flux:subheading>
                Tournaments, showmatches &amp; community events
            </flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            {{-- Time view toggle (upcoming/past/all) --}}
            <div class="flex rounded-lg overflow-hidden border border-zinc-700">
                <button wire:click="setView('upcoming')"
                    class="px-3 py-1.5 text-sm transition-colors {{ $view === 'upcoming' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:text-white' }}">
                    Upcoming
                </button>
                <button wire:click="setView('past')"
                    class="px-3 py-1.5 text-sm transition-colors {{ $view === 'past' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:text-white' }}">
                    Past
                </button>
                <button wire:click="setView('all')"
                    class="px-3 py-1.5 text-sm transition-colors {{ $view === 'all' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:text-white' }}">
                    All
                </button>
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

    {{-- ═══════════════════════════════════════════════════════════════════
         Filter row: type filter (All/Stream/Open) + timezone toggle
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        {{-- Type filter tabs --}}
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

        {{-- Timezone toggle. Pure client-side via Alpine, no persistence:
             reads each date's ISO timestamp from data-iso and reformats in
             the user's timezone (Intl API). Refreshing the page resets to CET. --}}
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

    {{-- ═══════════════════════════════════════════════════════════════════
         Timeline
         ═══════════════════════════════════════════════════════════════════ --}}
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
                    @php
                        $isLive   = $event->isLive();
                        $isPast   = $event->isPast();
                        $isStream = $event->isStream();
                        $isOpen   = $event->isOpen();

                        // Type label color — same pattern as match-card. Tinted text
                        // replaces the heavier pill we had before.
                        $typeLabelColor = $isStream ? '#c084fc' : '#fcd34d';

                        // Card border accent depends on type and past/future state.
                        $cardBorder = $isPast
                            ? 'border-zinc-800/60'
                            : ($isStream ? 'border-purple-500/25' : 'border-amber-500/25');

                        // Timeline dot color
                        $dotColor = $isLive
                            ? '#f87171'
                            : ($isPast ? '#52525b' : ($isStream ? '#c084fc' : '#fbbf24'));
                        $dotGlow = $isLive
                            ? '0 0 0 3px rgba(248,113,113,0.2)'
                            : ($isPast ? 'none' : ($isStream
                                ? '0 0 0 3px rgba(192,132,252,0.15)'
                                : '0 0 0 3px rgba(251,191,36,0.15)'));
                    @endphp

                    <div class="relative sm:pl-14 mb-3" wire:key="event-{{ $event->id }}">
                        {{-- Timeline dot --}}
                        @if($isLive)
                            <div class="absolute left-[14px] top-[18px] w-[11px] h-[11px] rounded-full hidden sm:block animate-pulse"
                                 style="background: {{ $dotColor }}; box-shadow: {{ $dotGlow }};"></div>
                        @else
                            <div class="absolute left-[14px] top-[18px] w-[11px] h-[11px] rounded-full hidden sm:block"
                                 style="background: {{ $dotColor }}; box-shadow: {{ $dotGlow }};"></div>
                        @endif

                        <div class="rounded-xl border transition-colors overflow-hidden
                            {{ $cardBorder }}
                            {{ $isPast ? 'bg-zinc-900/30' : 'bg-zinc-900/50 hover:border-zinc-600/80' }}
                            {{ $isPast ? 'opacity-75' : '' }}">

                            {{-- ─── Header strip (match-card style) ────────────────── --}}
                            <div class="flex items-center gap-2 px-4 py-2.5 border-b border-zinc-800/60 text-xs">
                                {{-- LIVE badge — most prominent, comes first --}}
                                @if($isLive)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/20 text-red-300 border border-red-500/40 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        LIVE
                                    </span>
                                @endif

                                {{-- Type label (tinted text, no pill) --}}
                                <span class="font-semibold uppercase tracking-wider text-[10px] shrink-0"
                                      style="color: {{ $typeLabelColor }};">
                                    {{ $isStream ? 'Stream' : 'Open' }}
                                </span>

                                {{-- Event name --}}
                                <span class="text-zinc-700">·</span>
                                <span class="text-zinc-400 truncate min-w-0 flex-1">{{ $event->name }}</span>

                                {{-- Right-side meta: registration / past indicator --}}
                                @if($isOpen && !$isPast)
                                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/15 text-emerald-300 border border-emerald-500/25 shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Registration open
                                    </span>
                                @elseif($isPast)
                                    <span class="font-mono text-[11px] text-zinc-500 shrink-0">ended</span>
                                @endif
                            </div>

                            {{-- ─── Body ────────────────────────────────────────────── --}}
                            <div class="px-4 py-3">
                                {{-- Description — only if present, treated as body lead --}}
                                @if($event->description)
                                    <p class="text-sm text-zinc-400 leading-relaxed mb-3">{{ $event->description }}</p>
                                @endif

                                {{-- Players (registered + guest) — unified pill list --}}
                                @if($event->players->isNotEmpty() || !empty($event->guest_players))
                                    <div class="flex flex-wrap gap-x-3 gap-y-2 mb-3">
                                        @foreach($event->players as $p)
                                            @php
                                                $playerRaceColor = match($p->race) {
                                                    'Terran'  => '#60a5fa',
                                                    'Zerg'    => '#fb7185',
                                                    'Protoss' => '#e8c66b',
                                                    'Random'  => '#fb923c',
                                                    default   => '#a1a1aa',
                                                };
                                            @endphp
                                            <a href="{{ route('players.show', ['id' => $p->id, 'slug' => \Illuminate\Support\Str::slug($p->name)]) }}"
                                               wire:navigate
                                               class="inline-flex items-center gap-1.5 text-xs sm:text-sm hover:opacity-80 transition-opacity"
                                               style="color: {{ $playerRaceColor }};">
                                                <img src="{{ asset('images/country_flags/' . strtolower($p->country_code) . '.svg') }}"
                                                     class="w-4 h-3 rounded-sm shrink-0">
                                                <span class="font-medium">{{ $p->name }}</span>
                                            </a>
                                        @endforeach

                                        @if(!empty($event->guest_players))
                                            @foreach($event->guest_players as $g)
                                                @php
                                                    $guestRaceColor = match($g['race'] ?? 'Unknown') {
                                                        'Terran'  => '#60a5fa',
                                                        'Zerg'    => '#fb7185',
                                                        'Protoss' => '#e8c66b',
                                                        'Random'  => '#fb923c',
                                                        default   => '#a1a1aa',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center gap-1.5 text-xs sm:text-sm"
                                                      style="color: {{ $guestRaceColor }};">
                                                    <img src="{{ asset('images/country_flags/' . strtolower($g['country_code'] ?? 'kr') . '.svg') }}"
                                                         class="w-4 h-3 rounded-sm shrink-0">
                                                    <span class="font-medium">{{ $g['name'] }}</span>
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif

                                {{-- ─── Meta row: dates + location + links ─────────── --}}
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                    {{-- Dates — each one carries data-iso for the JS toggle --}}
                                    @foreach($event->displayDates() as $dt)
                                        @php
                                            // displayDates() returns ['datetime' => '30 Mar, 18:00 CET', 'label' => '...'].
                                            // We need a raw ISO timestamp for client-side timezone math.
                                            // If the array has 'iso' it's used directly; otherwise we fall back
                                            // to the event's starts_at as a sane default.
                                            $iso = $dt['iso'] ?? $event->starts_at?->toIso8601String();
                                        @endphp
                                        <span class="font-mono text-[11px] text-zinc-400 inline-flex items-center gap-1.5 whitespace-nowrap">
                                            <span class="text-zinc-600">🗓</span>
                                            @if($iso)
                                                <span class="text-zinc-300"
                                                      x-text="showLocal
                                                          ? formatTime('{{ $iso }}', userTz)
                                                          : formatTime('{{ $iso }}', 'Europe/Warsaw')"
                                                      x-cloak>{{ $dt['datetime'] }}</span>
                                                <span class="text-zinc-600 text-[10px] uppercase"
                                                      x-text="showLocal
                                                          ? tzAbbr('{{ $iso }}', userTz)
                                                          : 'CET'"
                                                      x-cloak>CET</span>
                                            @else
                                                <span class="text-zinc-300">{{ $dt['datetime'] }}</span>
                                            @endif
                                            @if(!empty($dt['label']))
                                                <span class="text-zinc-600">{{ $dt['label'] }}</span>
                                            @endif
                                        </span>
                                    @endforeach

                                    {{-- Location --}}
                                    @if($event->location)
                                        <span class="font-mono text-[11px] text-zinc-400 inline-flex items-center gap-1.5">
                                            <span class="text-zinc-600">📍</span>
                                            <span>{{ $event->location }}</span>
                                        </span>
                                    @endif

                                    {{-- Mobile-only registration chip (desktop has it in header) --}}
                                    @if($isOpen && !$isPast)
                                        <span class="sm:hidden inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/15 text-emerald-300 border border-emerald-500/25">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Registration open
                                        </span>
                                    @endif

                                    {{-- Links pushed to the right --}}
                                    @if(count($event->parsedLinks()) > 0)
                                        <span class="ml-auto flex flex-wrap items-center gap-1.5">
                                            @foreach($event->parsedLinks() as $link)
                                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium transition-opacity hover:opacity-80"
                                                    style="background: {{ $link['color'] }}20; color: {{ $link['color'] }}; border: 0.5px solid {{ $link['color'] }}40;">
                                                    {{ $link['label'] ?: ucfirst($link['type']) }} ↗
                                                </a>
                                            @endforeach
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- ─── Reactions / comments ────────────────────────────── --}}
                            <div class="px-4 py-2 border-t border-zinc-800/60 flex items-center gap-3 text-xs">
                                <livewire:reactions.reaction-bar :model="$event" :key="'reactions-'.$event->id" />
                                <button
                                    wire:click="$dispatch('open-comments', { modelType: 'App\\Models\\Event', modelId: {{ $event->id }} })"
                                    class="flex items-center gap-1.5 text-zinc-500 hover:text-zinc-300 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    @php $commentCount = $event->comments()->whereNull('parent_id')->count() @endphp
                                    {{ $commentCount }} {{ Str::plural('comment', $commentCount) }}
                                </button>

                                {{-- Edit/Delete pushed to the right, only for owner/mods --}}
                                @auth
                                    @if(auth()->id() === $event->created_by || auth()->user()->canManageGames())
                                        <span class="ml-auto flex items-center gap-2 text-[11px] text-zinc-600">
                                            <span>by {{ $event->user?->name ?? 'unknown' }}</span>
                                            <span class="text-zinc-700">·</span>
                                            <button wire:click="openEditModal({{ $event->id }})"
                                                class="text-zinc-500 hover:text-zinc-300 transition-colors">Edit</button>
                                            <span class="text-zinc-700">·</span>
                                            <button wire:click="$set('confirmingDeleteId', {{ $event->id }})"
                                                class="text-zinc-500 hover:text-red-400 transition-colors">Delete</button>
                                        </span>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         Delete confirmation modal — unchanged
         ═══════════════════════════════════════════════════════════════════ --}}
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

    {{-- ═══════════════════════════════════════════════════════════════════
         Add / Edit modal — unchanged from the original (form has its own
         lifecycle, refactor not in scope here).
         ═══════════════════════════════════════════════════════════════════ --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
            wire:click.self="$set('showModal', false)">
            <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-3xl overflow-y-auto max-h-[90vh]">
                {{-- Modal header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-700/50">
                    <h2 class="text-lg font-semibold text-white">
                        {{ $editingId ? 'Edit Event' : 'Add Event' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body: two-column grid on lg+ --}}
                <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-x-5 gap-y-3">
                    {{-- LEFT COLUMN: basics --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm text-zinc-400 mb-1">Event name</label>
                            <input type="text" wire:model="name"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                                placeholder="e.g. Sunday Open #12" list="event-name-suggestions">
                            <datalist id="event-name-suggestions">
                                @foreach($this->recentEventNames as $eventName)
                                    <option value="{{ $eventName }}"></option>
                                @endforeach
                            </datalist>
                            @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-zinc-400 mb-1.5">Event type</label>
                            <div class="grid grid-cols-2 gap-1.5">
                                <button type="button" wire:click="$set('type', 'stream')"
                                    class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border text-sm transition-colors
                                        {{ $type === 'stream'
                                            ? 'bg-purple-500/15 border-purple-500/40 text-purple-300'
                                            : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 10l4.553-2.277A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a1 1 0 011-1h10a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z" />
                                    </svg>
                                    <span class="font-medium">Stream</span>
                                </button>
                                <button type="button" wire:click="$set('type', 'open')"
                                    class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border text-sm transition-colors
                                        {{ $type === 'open'
                                            ? 'bg-amber-500/15 border-amber-500/40 text-amber-300'
                                            : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                    <span class="font-medium">Open</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm text-zinc-400 mb-1">Start</label>
                                <input type="datetime-local" wire:model="startsAt"
                                    class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50">
                                @error('startsAt') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm text-zinc-400 mb-1">Timezone</label>
                                <select wire:model="timezone"
                                    class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50">
                                    @foreach($this->timezones as $tz => $label)
                                        <option value="{{ $tz }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-sm text-zinc-400 cursor-pointer mb-1">
                                <input type="checkbox" wire:model.live="isOnline"
                                    class="rounded bg-zinc-800 border-zinc-600 text-amber-500 focus:ring-amber-500/30">
                                Online event
                            </label>
                            @if(!$isOnline)
                                <input type="text" wire:model="location"
                                    class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                                    placeholder="Location (city, venue)">
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm text-zinc-400 mb-1">Description <span class="text-zinc-600">(optional)</span></label>
                            <textarea wire:model="description" rows="2"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50 resize-none"
                                placeholder="Details, format, rules…"></textarea>
                            @error('description') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: links + players --}}
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-sm text-zinc-400">External links</label>
                                <button type="button" wire:click="addLink" class="text-xs text-amber-400 hover:text-amber-300">
                                    + Add link
                                </button>
                            </div>

                            @if(count($this->predefinedLinks) > 0)
                                <div class="flex flex-wrap gap-1 mb-2">
                                    @foreach($this->predefinedLinks as $i => $pre)
                                        @php $active = !empty($predefinedLinksSelected[$i]); @endphp
                                        <button type="button" wire:click="togglePredefinedLink({{ $i }})"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium border transition-colors
                                                {{ $active
                                                    ? 'bg-zinc-600 border-zinc-500 text-white'
                                                    : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:border-zinc-500 hover:text-white' }}">
                                            @if($active)
                                                <svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                            {{ $pre['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @foreach($links as $i => $link)
                                <div class="flex items-center gap-1 mb-1.5" wire:key="link-{{ $i }}">
                                    <select wire:model="links.{{ $i }}.type"
                                        class="w-24 shrink-0 rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50">
                                        @foreach($this->linkTypes as $key => $meta)
                                            <option value="{{ $key }}">{{ $meta['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <input type="url" wire:model="links.{{ $i }}.url"
                                        class="flex-1 min-w-0 rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50"
                                        placeholder="https://…">
                                    <input type="text" wire:model="links.{{ $i }}.label"
                                        class="w-20 shrink-0 rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50"
                                        placeholder="Label">
                                    <button type="button" wire:click="removeLink({{ $i }})"
                                        class="text-zinc-500 hover:text-red-400 transition-colors shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @error('links.' . $i . '.type') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                                @error('links.' . $i . '.url') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                                @error('links.' . $i . '.label') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                            @endforeach
                        </div>

                        <div x-data="{ tab: 'players' }">
                            <label class="block text-sm text-zinc-400 mb-1.5">Players <span class="text-zinc-600">(optional)</span></label>

                            <div class="flex gap-1 p-1 bg-zinc-800/50 border border-zinc-700 rounded-lg mb-2">
                                <button type="button" x-on:click="tab = 'players'"
                                    :class="tab === 'players' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:text-white'"
                                    class="flex-1 px-2 py-1 rounded-md text-xs font-medium transition-colors">
                                    Registered
                                    @if(count($selectedPlayers) > 0)
                                        <span class="ml-1 text-amber-400">{{ count($selectedPlayers) }}</span>
                                    @endif
                                </button>
                                <button type="button" x-on:click="tab = 'guests'"
                                    :class="tab === 'guests' ? 'bg-zinc-700 text-white' : 'text-zinc-400 hover:text-white'"
                                    class="flex-1 px-2 py-1 rounded-md text-xs font-medium transition-colors inline-flex items-center justify-center gap-1">
                                    <img src="{{ asset('images/country_flags/kr.svg') }}" class="w-3.5 h-2.5 rounded-sm">
                                    Korean guests
                                    @if(count($selectedGuests) > 0)
                                        <span class="ml-1 text-amber-400">{{ count($selectedGuests) }}</span>
                                    @endif
                                </button>
                            </div>

                            <div x-show="tab === 'players'" x-cloak>
                                <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                                    <input type="text" wire:model.live.debounce.200ms="playerSearch"
                                        x-on:focus="open = true" x-on:input="open = true"
                                        autocomplete="off" placeholder="Search player…"
                                        class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50" />

                                    @if(strlen($playerSearch) >= 2)
                                        <div x-show="open"
                                            class="absolute z-50 w-full mt-1 rounded-lg bg-zinc-900 border border-zinc-700 shadow-lg overflow-hidden max-h-52 overflow-y-auto">
                                            @forelse($this->playerResults as $player)
                                                @php
                                                    $searchRaceColor = match($player->race) {
                                                        'Terran'  => '#60a5fa',
                                                        'Zerg'    => '#fb7185',
                                                        'Protoss' => '#e8c66b',
                                                        'Random'  => '#fb923c',
                                                        default   => '#a1a1aa',
                                                    };
                                                @endphp
                                                <button type="button" wire:click="addPlayer({{ $player->id }})"
                                                    x-on:click="open = false"
                                                    class="flex items-center gap-1.5 w-full px-3 py-1.5 text-left hover:bg-zinc-800 transition-colors">
                                                    <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                                        class="w-5 h-3.5 rounded-sm shrink-0">
                                                    <span class="text-sm font-semibold text-white truncate">{{ $player->name }}</span>
                                                    <span class="text-xs ml-auto shrink-0" style="color: {{ $searchRaceColor }};">
                                                        {{ $player->race }}
                                                    </span>
                                                </button>
                                            @empty
                                                <div class="px-3 py-2 text-sm text-zinc-500">No players found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>

                                @if(count($selectedPlayers) > 0)
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($selectedPlayers as $player)
                                            <span wire:key="selected-{{ $player['id'] }}"
                                                class="inline-flex items-center gap-1.5 pl-1.5 pr-1 py-0.5 rounded-full text-xs bg-zinc-700 border border-zinc-600 text-white">
                                                <img src="{{ asset('images/country_flags/' . strtolower($player['country_code']) . '.svg') }}"
                                                    class="w-4 h-3 rounded-sm shrink-0">
                                                {{ $player['name'] }}
                                                <button type="button" wire:click="removePlayer({{ $player['id'] }})"
                                                    class="ml-0.5 text-zinc-400 hover:text-red-400 transition-colors leading-none">✕</button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div x-show="tab === 'guests'" x-cloak>
                                <div class="flex gap-2 items-end">
                                    <div class="flex-1">
                                        <input type="text"
                                            wire:model="newGuestName"
                                            wire:keydown.enter.prevent="addGuestManually"
                                            autocomplete="off"
                                            placeholder="e.g. Flash, Jaedong…"
                                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50" />
                                    </div>
                                    <div>
                                        <select wire:model="newGuestRace"
                                            class="rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50">
                                            <option value="Terran">Terran</option>
                                            <option value="Zerg">Zerg</option>
                                            <option value="Protoss">Protoss</option>
                                            <option value="Random">Random</option>
                                            <option value="Unknown">Unknown</option>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="text"
                                            wire:model="newGuestCountry"
                                            maxlength="2"
                                            placeholder="KR"
                                            class="w-16 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white uppercase focus:outline-none focus:border-amber-500/50" />
                                    </div>
                                    <button type="button"
                                        wire:click="addGuestManually"
                                        class="px-3 py-2 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 text-sm font-medium transition-colors whitespace-nowrap">
                                        + Add
                                    </button>
                                </div>

                                @error('newGuestName')
                                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror

                                @if(count($selectedGuests) > 0)
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($selectedGuests as $i => $guest)
                                            @php
                                                $chipRaceColor = match($guest['race'] ?? 'Unknown') {
                                                    'Terran'  => '#60a5fa',
                                                    'Zerg'    => '#fb7185',
                                                    'Protoss' => '#e8c66b',
                                                    'Random'  => '#fb923c',
                                                    default   => '#a1a1aa',
                                                };
                                            @endphp
                                            <span wire:key="guest-{{ $i }}"
                                                class="inline-flex items-center gap-1.5 pl-1.5 pr-1 py-0.5 rounded-full text-xs bg-zinc-700 border border-zinc-600">
                                                <img src="{{ asset('images/country_flags/' . strtolower($guest['country_code']) . '.svg') }}"
                                                    class="w-4 h-3 rounded-sm shrink-0">
                                                <span style="color: {{ $chipRaceColor }};">{{ $guest['name'] }}</span>
                                                <button type="button" wire:click="removeGuest('{{ $guest['name'] }}')"
                                                    class="ml-0.5 text-zinc-500 hover:text-red-400 transition-colors">✕</button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-5 py-3 border-t border-zinc-700/50">
                    <button wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button wire:click="save" class="px-4 py-2 text-sm font-medium rounded-lg
                            {{ $type === 'open'
                                ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20'
                                : 'bg-purple-500/10 text-purple-400 border border-purple-500/20 hover:bg-purple-500/20' }}
                            transition-colors">
                        {{ $editingId ? 'Save changes' : 'Add event' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>