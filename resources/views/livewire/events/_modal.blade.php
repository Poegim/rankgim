{{-- Add / Edit event modal.
     Included as a partial because it relies on many Livewire properties
     ($showModal, $name, $type, $startsAt, $links[], $selectedPlayers[], etc.)
     and using a Blade component would mean threading 20+ props through. --}}

@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    wire:click.self="$set('showModal', false)">
    <div class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-3xl overflow-y-auto max-h-[90vh]">

        {{-- Header --}}
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

        {{-- Body: two-column on lg+ --}}
        <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-x-5 gap-y-3">

            {{-- LEFT COLUMN: basics --}}
            <div class="space-y-3">

                {{-- Event name --}}
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

                {{-- Event type selector --}}
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

                {{-- Start datetime + Timezone --}}
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

                {{-- Online / Location --}}
                <div>
                    <label class="flex items-center gap-1.5 text-sm text-zinc-400 cursor-pointer mb-1">
                        <input type="checkbox" wire:model.live="isOnline"
                            class="rounded bg-zinc-800 border-zinc-600 text-amber-500 focus:ring-amber-500/30">
                        Online event
                    </label>
                    @if(! $isOnline)
                        <input type="text" wire:model="location"
                            class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500/50"
                            placeholder="Location (city, venue)">
                    @endif
                </div>

                {{-- Description --}}
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

                {{-- External links --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-sm text-zinc-400">External links</label>
                        <button type="button" wire:click="addLink" class="text-xs text-amber-400 hover:text-amber-300">
                            + Add link
                        </button>
                    </div>

                    {{-- Predefined link toggles --}}
                    @if(count($this->predefinedLinks) > 0)
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach($this->predefinedLinks as $i => $pre)
                                @php $active = ! empty($predefinedLinksSelected[$i]); @endphp
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

                    {{-- Custom links list --}}
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

                {{-- Players & Korean guests (tabbed) --}}
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

                    {{-- Registered players tab --}}
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

                    {{-- Korean guests tab --}}
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

        {{-- Footer --}}
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