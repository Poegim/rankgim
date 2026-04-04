<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Events</flux:heading>
            <flux:subheading>
                Tournaments, showmatches & community events
            </flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            {{-- View toggle --}}
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Event
            </button>
            @endauth
        </div>
    </div>

    {{-- Timeline --}}
    @if($this->groupedEvents->isEmpty())
    <div class="text-center py-16 text-zinc-500">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        <p class="text-lg">No {{ $view === 'past' ? 'past' : 'upcoming' }} events</p>
        @auth
        <button wire:click="openAddModal" class="mt-3 text-sm text-amber-400 hover:text-amber-300">Add the first
            one</button>
        @endauth
    </div>
    @else
    <div class="relative">
        {{-- Timeline line --}}
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

        {{-- Events in this month --}}
        @foreach($events as $event)
        @php $isPast = $event->isPast(); @endphp
        <div class="relative sm:pl-14 mb-3">
            {{-- Timeline dot --}}
            <div class="absolute left-[15px] top-5 w-[9px] h-[9px] rounded-full hidden sm:block
                            {{ $isPast ? 'bg-zinc-600' : 'bg-amber-400' }}">
            </div>

            <div class="rounded-xl border transition-colors
                            {{ $isPast
                                ? 'bg-zinc-800/30 border-zinc-700/50 opacity-75'
                                : 'bg-zinc-800/50 border-zinc-700/50 hover:border-zinc-600' }}">

                <div class="p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
<div class="flex flex-col sm:flex-row justify-between gap-3 w-full min-w-0">
    {{-- Left: name / description / links --}}
    <div class="grid gap-2 min-w-0">
        <div class="flex items-center gap-2 min-w-0">
            <h3 class="font-semibold text-white truncate">{{ $event->name }}</h3>
            @if($event->is_online)
            <span class="text-xs text-zinc-500 shrink-0">Online</span>
            @else
            <span class="text-xs text-zinc-500 shrink-0">📍 {{ $event->location }}</span>
            @endif
        </div>
        <div>
            @if($event->description)
            <p class="mt-2 text-sm text-zinc-400 line-clamp-2">{{ $event->description }}</p>
            @endif
        </div>
                                {{-- External links --}}
                                @if($event->hasLinks())
                                <div class="flex flex-wrap gap-1.5 sm:flex-nowrap">
                                    @foreach($event->parsedLinks() as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition-all hover:scale-105"
                                        style="background: {{ $link['color'] }}15; color: {{ $link['color'] }}; border: 1px solid {{ $link['color'] }}33;">
                                        @switch($link['type'])
                                        @case('twitch')
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z" />
                                        </svg>
                                        @break
                                        @case('challonge')
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" /></svg>
                                        @break
                                        @case('youtube')
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                                        </svg>
                                        @break
                                        @case('facebook')
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                        </svg>
                                        @break
                                        @case('discord')
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z" />
                                        </svg>
                                        @break
                                        @default
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        @endswitch
                                        {{ $link['label'] ?: ucfirst($link['type']) }}
                                    </a>
                                    @endforeach
                                </div>
                                @endif
    </div>

    {{-- Right: dates + countdown --}}
    <div class="flex flex-col gap-1 shrink-0">
        <div class="flex flex-col gap-1 text-sm font-mono">
            @foreach($event->displayDates() as $dt)
            <div class="text-zinc-500 whitespace-nowrap">
                {{ $dt['datetime'] }} {{ $dt['label'] }}
            </div>
            @endforeach
        </div>

        @if(!$isPast)
        <div
            class="text-xs font-mono text-amber-400/80 mt-1"
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
            }"
        >
            <span x-show="d > 0" x-text="d + 'd '"></span><span x-text="String(h).padStart(2,'0') + 'h ' + String(m).padStart(2,'0') + 'm ' + String(s).padStart(2,'0') + 's'"></span>
        </div>
        @endif
    </div>
</div>


                    </div>

                    {{-- Edit/Delete for owner or admin/mod --}}
                    @auth
                    @if(auth()->id() === $event->created_by || auth()->user()->canManageGames())
                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-zinc-700/30">
                        <button wire:click="openEditModal({{ $event->id }})"
                            class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">
                            Edit
                        </button>
                        <span class="text-zinc-700">·</span>
                        <button wire:click="$set('confirmingDeleteId', {{ $event->id }})"
                            class="text-xs text-zinc-500 hover:text-red-400 transition-colors">
                            Delete
                        </button>
                        <span class="ml-auto text-xs text-zinc-600">
                            by {{ $event->user?->name ?? 'unknown' }}
                        </span>
                    </div>
                    @endif
                    @endauth
                </div>
            </div>
        </div>
        @endforeach
        @endforeach
    </div>
    @endif

    {{-- ════════════════════════════════════════════════ --}}
    {{-- Add / Edit Modal --}}
    {{-- ════════════════════════════════════════════════ --}}
    @auth
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data
        x-on:keydown.escape.window="$wire.set('showModal', false)">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60" wire:click="$set('showModal', false)"></div>

        {{-- Modal content --}}
        <div
            class="relative w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-xl bg-zinc-900 border border-zinc-700 shadow-2xl">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4">
                    {{ $editingId ? 'Edit Event' : 'Add Event' }}
                </h2>

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">Event name *</label>
                        <input type="text" wire:model="name"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                            placeholder="e.g. Through life with Netwars #42">
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">Description</label>
                        <textarea wire:model="description" rows="2"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50 resize-none"
                            placeholder="Server: EU, channel: op rankgim"></textarea>
                    </div>

                    {{-- Date/time + Timezone --}}
                    <div class="grid grid-cols-5 gap-3">
                        <div class="col-span-3">
                            <label class="block text-sm text-zinc-400 mb-1">Starts at *</label>
                            <input type="datetime-local" wire:model="startsAt"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50">
                            @error('startsAt') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
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
                        <label class="flex items-center gap-2 text-sm text-zinc-400 cursor-pointer">
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

                    {{-- External Links --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm text-zinc-400">External links</label>
                            <button type="button" wire:click="addLink"
                                class="text-xs text-amber-400 hover:text-amber-300">
                                + Add link
                            </button>
                        </div>

                        @foreach($links as $i => $link)
                        <div class="flex items-start gap-2 mb-2" wire:key="link-{{ $i }}">
                            <select wire:model="links.{{ $i }}.type"
                                class="w-28 shrink-0 rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50">
                                @foreach($this->linkTypes as $key => $meta)
                                <option value="{{ $key }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                            <input type="url" wire:model="links.{{ $i }}.url" placeholder="https://..."
                                class="flex-1 rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50">
                            <input type="text" wire:model="links.{{ $i }}.label" placeholder="Label"
                                class="w-20 shrink-0 rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1.5 text-xs text-white focus:outline-none focus:border-amber-500/50">
                            <button type="button" wire:click="removeLink({{ $i }})"
                                class="shrink-0 p-1.5 text-zinc-500 hover:text-red-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        @endforeach
                        @error('links.*.url') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-zinc-700/50">
                    <button wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button wire:click="save"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-amber-500 text-black hover:bg-amber-400 transition-colors">
                        {{ $editingId ? 'Save Changes' : 'Create Event' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endauth

    @if($confirmingDeleteId)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60" wire:click="$set('confirmingDeleteId', null)"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-sm rounded-xl bg-zinc-900 border border-zinc-700 shadow-2xl p-6">
            <h2 class="text-lg font-semibold text-white mb-2">
                Delete event
            </h2>

            <p class="text-sm text-zinc-400 mb-6">
                Are you sure you want to delete this event? This action cannot be undone.
            </p>

            <div class="flex items-center justify-end gap-3">
                <button wire:click="$set('confirmingDeleteId', null)"
                    class="px-4 py-2 text-sm text-zinc-400 hover:text-white">
                    Cancel
                </button>

                <button wire:click="delete"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-red-500 text-white hover:bg-red-400">
                    Delete
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
