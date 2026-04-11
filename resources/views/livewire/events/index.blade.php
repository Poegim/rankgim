<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Events</flux:heading>
            <flux:subheading>
                Tournaments, showmatches &amp; community events
            </flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            {{-- Time view toggle --}}
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

    {{-- Type filter tabs: All / Stream / Open --}}
    <div class="flex gap-1.5 mb-6">
        <button wire:click="setTypeFilter('all')" class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors border
                {{ $typeFilter === 'all'
                    ? 'bg-zinc-700 text-white border-zinc-600'
                    : 'text-zinc-400 border-zinc-700 hover:text-white hover:border-zinc-500' }}">
            All
        </button>
        {{-- Stream tab — purple accent --}}
        <button wire:click="setTypeFilter('stream')" class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition-colors border
                {{ $typeFilter === 'stream'
                    ? 'bg-purple-500/20 text-purple-300 border-purple-500/40'
                    : 'text-zinc-400 border-zinc-700 hover:text-purple-300 hover:border-purple-500/30' }}">
            {{-- TV/stream icon --}}
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 10l4.553-2.277A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a1 1 0 011-1h10a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z" />
            </svg>
            Stream
        </button>
        {{-- Open tab — amber accent --}}
        <button wire:click="setTypeFilter('open')" class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition-colors border
                {{ $typeFilter === 'open'
                    ? 'bg-amber-500/20 text-amber-300 border-amber-500/40'
                    : 'text-zinc-400 border-zinc-700 hover:text-amber-300 hover:border-amber-500/30' }}">
            {{-- Trophy icon --}}
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
            Open
        </button>
    </div>

    {{-- Timeline --}}
    @if($this->groupedEvents->isEmpty())
    <div class="text-center py-16 text-zinc-500">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-lg">No {{ $view === 'past' ? 'past' : 'upcoming' }} events</p>
        @auth
        <button wire:click="openAddModal" class="mt-3 text-sm text-amber-400 hover:text-amber-300">Add the first
            one</button>
        @endauth
    </div>
    @else
    <div class="relative">
        {{-- Timeline vertical line --}}
        <div class="absolute left-[19px] top-0 bottom-0 w-px bg-zinc-700/50 hidden sm:block"></div>

        @foreach($this->groupedEvents as $month => $events)
        {{-- Month header --}}
        <div class="relative flex items-center gap-3 mb-4 mt-8 first:mt-0">
            <div
                class="relative z-10 w-10 h-10 rounded-full bg-zinc-800 border border-zinc-600 flex items-center justify-center text-xs font-mono text-zinc-300 hidden sm:flex">
                {{ \Illuminate\Support\Str::substr($month, 0, 3) }}
            </div>
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider">{{ $month }}</h2>
        </div>

        @foreach($events as $event)
        @php
        $isPast = $event->isPast();
        $isStream = $event->isStream();
        $isOpen = $event->isOpen();
        @endphp
        <div class="relative sm:pl-14 mb-3" wire:key="event-{{ $event->id }}">
            {{-- Timeline dot --}}
            <div class="absolute left-[15px] top-5 w-[9px] h-[9px] rounded-full hidden sm:block
                        {{ $isPast ? 'bg-zinc-600' : ($isStream ? 'bg-purple-400' : 'bg-amber-400') }}">
            </div>

            <div class="rounded-xl border transition-colors p-4
    {{ $isPast
        ? 'bg-zinc-900/40 border-zinc-700/40'
        : ($isStream
            ? 'bg-zinc-900/80 border-purple-500/20 hover:border-purple-500/40'
            : 'bg-zinc-900/80 border-amber-500/20 hover:border-amber-500/40') }}">

                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3">

                    {{-- Left column: everything --}}
                    <div class="min-w-0">

                        {{-- Badge + name --}}
                        <div class="flex items-center gap-1.5 min-w-0">
                            @if($isStream)
                            <span
                                class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-purple-500/15 text-purple-300 border border-purple-500/25">Stream</span>
                            @else
                            <span
                                class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/15 text-amber-300 border border-amber-500/25">Open</span>
                            @endif
                            <h3 class="font-semibold text-white truncate">{{ $event->name }}</h3>
                        </div>

                        {{-- Description --}}
                        @if($event->description)
                        <p class="text-sm text-zinc-400 mt-2 leading-relaxed">{{ $event->description }}</p>
                        @endif

                        {{-- Players --}}
                        @if($event->players->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($event->players as $p)
                            <a href="{{ route('players.show', ['id' => $p->id, 'slug' => $p->name]) }}" wire:navigate
                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs bg-zinc-800 border border-zinc-700 transition-colors hover:border-zinc-500
                    {{ $p->race === 'Terran' ? 'text-blue-400 hover:text-blue-300' : ($p->race === 'Zerg' ? 'text-purple-400 hover:text-purple-300' : 'text-yellow-400 hover:text-yellow-300') }}">
                                <img src="{{ asset('images/country_flags/' . strtolower($p->country_code) . '.svg') }}"
                                    class="w-4 h-3 rounded-sm shrink-0">
                                {{ $p->name }}
                            </a>
                            @endforeach
                        </div>
                        @endif

                        {{-- Dates --}}
                        <div class="hidden sm:flex flex-wrap gap-x-3 gap-y-1 mt-3">
                            @foreach($event->displayDates() as $dt)
                            <span class="text-xs font-mono text-zinc-500 whitespace-nowrap">
                                {{ $dt['datetime'] }} <span class="text-zinc-600">{{ $dt['label'] }}</span>
                            </span>
                            @endforeach
                        </div>

                        {{-- Links --}}
                        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-1.5 mt-3">
                            @if($event->is_online && !$event->location)
                            <span class="flex sm:inline-flex items-center justify-center sm:justify-start gap-1 px-2.5 py-2 sm:py-0.5 rounded text-xs text-zinc-500"
                                style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08)">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                                </svg>
                                Online
                            </span>
                            @elseif($event->location)
                            <span class="flex sm:inline-flex items-center justify-center sm:justify-start gap-1 px-2.5 py-2 sm:py-0.5 rounded text-xs text-zinc-500"
                                style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08)">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $event->location }}
                            </span>
                            @endif

                            @foreach($event->parsedLinks() as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                class="flex sm:inline-flex items-center justify-center sm:justify-start gap-1 px-2.5 py-2 sm:py-0.5 rounded text-xs font-medium transition-opacity hover:opacity-80"
                                style="background: {{ $link['color'] }}20; color: {{ $link['color'] }}; border: 1px solid {{ $link['color'] }}40">
                                {{ $link['label'] ?: ucfirst($link['type']) }}
                            </a>
                            @endforeach

                            @if($isOpen && !$isPast)
                            <span class="flex sm:inline-flex items-center justify-center sm:justify-start gap-1.5 px-2.5 py-2 sm:py-0.5 rounded-full text-xs font-medium bg-green-500/15 text-green-400 border border-green-500/25">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-400 shrink-0"></span>
                                Registration open
                            </span>
                            @endif
                        </div>


                    </div>

                    {{-- Right column: date + countdown --}}
                    @if(!$isPast)
                    <div class="flex flex-col items-start sm:items-end">
                        <p class="uppercase text-sm sm:text-lg font-mono font-bold {{ $isStream ? 'text-purple-300' : 'text-amber-300' }}">
                            <span x-data x-text="new Intl.DateTimeFormat(navigator.language, {
                                day: 'numeric',
                                month: 'long',
                                hour: '2-digit',
                                minute: '2-digit',
                                timeZone: 'Europe/Warsaw'
                            }).format(new Date({{ $event->starts_at->timestamp }} * 1000))"></span>
                            <span class="opacity-50 text-sm">CET</span>
                        </p>
                        <div class="text-base sm:text-lg font-mono {{ $isStream ? 'text-purple-300/60' : 'text-amber-300/60' }}"
                            x-data="{
                    target: {{ $event->starts_at->timestamp }},
                    d: 0, h: 0, m: 0, s: 0,
                    init() { this.tick(); setInterval(() => this.tick(), 1000); },
                    tick() {
                        const diff = this.target - Math.floor(Date.now() / 1000);
                        if (diff <= 0) { this.d = this.h = this.m = this.s = 0; return; }
                        this.d = Math.floor(diff / 86400);
                        this.h = Math.floor((diff % 86400) / 3600);
                        this.m = Math.floor((diff % 3600) / 60);
                        this.s = diff % 60;
                    }
                }">
                            <span x-show="d > 0" x-text="d + 'd '"></span><span
                                x-text="String(h).padStart(2,'0') + 'h ' + String(m).padStart(2,'0') + 'm ' + String(s).padStart(2,'0') + 's'"></span>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- Reactions & Comments --}}
                <div class="flex items-center gap-3 mt-3">
                    <livewire:reactions.reaction-bar :model="$event" :key="'reactions-'.$event->id" />
                    <button
                        wire:click="$dispatch('open-comments', { modelType: 'App\\Models\\Event', modelId: {{ $event->id }} })"
                        class="flex items-center gap-1.5 text-xs text-zinc-500 hover:text-zinc-300 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        @php $commentCount = $event->comments()->whereNull('parent_id')->count() @endphp
                        {{ $commentCount }} {{ Str::plural('comment', $commentCount) }}
                    </button>
                </div>

                {{-- Edit/Delete --}}
                @auth
                @if(auth()->id() === $event->created_by || auth()->user()->canManageGames())
                <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-zinc-700/30">
                    <button wire:click="openEditModal({{ $event->id }})"
                        class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">Edit</button>
                    <span class="text-zinc-700">·</span>
                    <button wire:click="$set('confirmingDeleteId', {{ $event->id }})"
                        class="text-xs text-zinc-500 hover:text-red-400 transition-colors">Delete</button>
                    <span class="ml-auto text-xs text-zinc-600">by {{ $event->user?->name ?? 'unknown' }}</span>
                </div>
                @endif
                @endauth
            </div>
        </div>
        @endforeach
        @endforeach
    </div>
    @endif

    {{-- Delete confirmation modal --}}
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

    {{-- Add / Edit modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
        wire:click.self="$set('showModal', false)">
        <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-lg mx-4 overflow-y-auto max-h-[90vh]">
            <div class="flex items-center justify-between p-5 border-b border-zinc-700/50">
                <h2 class="text-lg font-semibold text-white">
                    {{ $editingId ? 'Edit Event' : 'Add Event' }}
                </h2>
                <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">

                {{-- Event name --}}
                <div>
                    <label class="block text-sm text-zinc-400 mb-1">Event name</label>
                    <input type="text" wire:model="name"
                        class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                        placeholder="e.g. Sunday Open #12" list="event-name-suggestions">
                    <datalist id="event-name-suggestions">
                        @foreach($this->recentEventNames as $eventName)
                        <option value="{{ $eventName }}">
                            @endforeach
                    </datalist>
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Event type selector --}}
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Event type</label>
                    <div class="flex gap-1.5">
                        {{-- Stream option --}}
                        <button type="button" wire:click="$set('type', 'stream')" class="flex-1 flex flex-col items-center gap-1 px-2 py-2 rounded-lg border text-sm transition-colors
                                {{ $type === 'stream'
                                    ? 'bg-purple-500/15 border-purple-500/40 text-purple-300'
                                    : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.277A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a1 1 0 011-1h10a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z" />
                            </svg>
                            <span class="font-medium">Stream</span>
                            <span class="text-xs opacity-70">Watch only</span>
                        </button>
                        {{-- Open tournament option --}}
                        <button type="button" wire:click="$set('type', 'open')" class="flex-1 flex flex-col items-center gap-1 px-2 py-2 rounded-lg border text-sm transition-colors
                                {{ $type === 'open'
                                    ? 'bg-amber-500/15 border-amber-500/40 text-amber-300'
                                    : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:border-zinc-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            <span class="font-medium">Open</span>
                            <span class="text-xs opacity-70">Register &amp; play</span>
                        </button>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm text-zinc-400 mb-1">Description <span
                            class="text-zinc-600">(optional)</span></label>
                    <textarea wire:model="description" rows="3"
                        class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50 resize-none"
                        placeholder="Details, format, rules…"></textarea>
                    @error('description') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Start datetime + Timezone --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="col-span-1">
                        <label class="block text-sm text-zinc-400 mb-1">Start</label>
                        <input type="datetime-local" wire:model="startsAt"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50">
                        @error('startsAt') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-1">
                        <label class="block text-sm text-zinc-400 mb-1">Timezone</label>
                        <select wire:model="timezone"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50">
                            @foreach($this->timezones as $tz => $label)
                            <option value="{{ $tz }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Online / Location --}}
                <div>
                    <label class="flex items-center gap-1.5 text-sm text-zinc-400 cursor-pointer">
                        <input type="checkbox" wire:model.live="isOnline"
                            class="rounded bg-zinc-800 border-zinc-600 text-amber-500 focus:ring-amber-500/30">
                        Online event
                    </label>
                    @if(!$isOnline)
                    <input type="text" wire:model="location"
                        class="w-full mt-2 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                        placeholder="Location (city, venue)">
                    @endif
                </div>

                {{-- External links --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm text-zinc-400">External links</label>
                        <button type="button" wire:click="addLink" class="text-xs text-amber-400 hover:text-amber-300">
                            + Add link
                        </button>
                    </div>

                    {{-- Predefined links --}}
                    @if(count($this->predefinedLinks) > 0)
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        @foreach($this->predefinedLinks as $i => $pre)
                        @php $active = !empty($predefinedLinksSelected[$i]); @endphp
                        <button type="button" wire:click="togglePredefinedLink({{ $i }})"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium border transition-colors
                                {{ $active
                                    ? 'bg-zinc-600 border-zinc-500 text-white'
                                    : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:border-zinc-500 hover:text-white' }}">
                            @if($active)
                            <svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            @endif
                            {{ $pre['label'] }}
                        </button>
                        @endforeach
                    </div>
                    @endif

                    @foreach($links as $i => $link)
                    <div class="flex items-start gap-1.5 mb-2" wire:key="link-{{ $i }}">
                        <div class="flex flex-col gap-1">
                            <select wire:model="links.{{ $i }}.type"
                                class="w-28 shrink-0 rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50">
                                @foreach($this->linkTypes as $key => $meta)
                                <option value="{{ $key }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                            @error('links.' . $i . '.type') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-1 flex-1">
                            <input type="url" wire:model="links.{{ $i }}.url"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50"
                                placeholder="https://…">
                            @error('links.' . $i . '.url') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-1">
                            <input type="text" wire:model="links.{{ $i }}.label"
                                class="w-24 rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50"
                                placeholder="Custom label (optional)">
                            @error('links.' . $i . '.label') <p class="text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="button" wire:click="removeLink({{ $i }})"
                            class="text-zinc-500 hover:text-red-400 transition-colors pt-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>

                {{-- Players --}}
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Players <span
                            class="text-zinc-600">(optional)</span></label>

                    {{-- Search input with live dropdown --}}
                    <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                        <input type="text" wire:model.live.debounce.200ms="playerSearch" x-on:focus="open = true"
                            x-on:input="open = true" autocomplete="off" placeholder="Search player…"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50" />

                        @if(strlen($playerSearch) >= 2)
                        <div x-show="open"
                            class="absolute z-50 w-full mt-1 rounded-lg bg-zinc-900 border border-zinc-700 shadow-lg overflow-hidden">
                            @forelse($this->playerResults as $player)
                            <button type="button" wire:click="addPlayer({{ $player->id }})" x-on:click="open = false"
                                class="flex items-center gap-1.5 w-full px-3 py-2 text-left hover:bg-zinc-800 transition-colors">
                                <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                    class="w-6 h-4 rounded-sm shrink-0">
                                <span class="text-sm font-semibold text-white">{{ $player->name }}</span>
                                <span
                                    class="text-xs ml-auto
                                    {{ $player->race === 'Terran' ? 'text-blue-400' : ($player->race === 'Zerg' ? 'text-purple-400' : 'text-yellow-400') }}">
                                    {{ $player->race }}
                                </span>
                            </button>
                            @empty
                            <div class="px-3 py-2 text-sm text-zinc-500">No players found</div>
                            @endforelse
                        </div>
                        @endif
                    </div>

                    {{-- Selected players chips --}}
                    @if(count($selectedPlayers) > 0)
                    <div class="flex flex-wrap gap-1.5 mt-2">
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

            </div>

            {{-- Modal footer --}}
            <div class="flex justify-end gap-3 p-5 border-t border-zinc-700/50">
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
