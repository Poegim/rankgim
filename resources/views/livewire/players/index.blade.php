@use('Illuminate\Support\Str')
<div>
    @php
        $raceColors = [
            'Terran'  => 'text-blue-500',
            'Zerg'    => 'text-purple-500',
            'Protoss' => 'text-yellow-500',
            'Random'  => 'text-orange-400',
            'Unknown' => 'text-zinc-400',
        ];
    @endphp

    {{-- Toast Notifications --}}
    <div
        x-data="{ show: false }"
        x-on:player-saved.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Player saved
    </div>
    <div
        x-data="{ show: false }"
        x-on:player-updated.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Player updated
    </div>
    <div
        x-data="{ show: false }"
        x-on:player-deleted.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Player deleted
    </div>
    <div
        x-data="{ show: false }"
        x-on:cannot-delete.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-red-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ❌ Cannot delete — player has games
    </div>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Players</flux:heading>
            <flux:text>{{ $players->total() }} players</flux:text>
        </div>
        @auth
            @if(auth()->user()->canManageGames())
                <flux:button variant="primary" wire:click="openAddModal">
                    Add Player
                </flux:button>
            @endif
        @endauth
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <flux:input 
            type="text" 
            autocomplete="off"
            wire:model.live.debounce.300ms="search" 
            placeholder="Search players..."
        />
    </div>

    {{-- Players Table --}}
    <flux:table :paginate="$players">
        <flux:table.columns>
            <flux:table.column>Player</flux:table.column>
            <flux:table.column>Country</flux:table.column>
            <flux:table.column>Race</flux:table.column>
            @auth
                @if(auth()->user()->canManageGames())
                    <flux:table.column></flux:table.column>
                @endif
            @endauth
            <flux:table.column width="1"></flux:table.column>
        </flux:table.columns>
        @foreach($players as $player)
    <flux:table.row :key="$player->id">
        <flux:table.cell>
            <a href="{{ route('players.show', ['id' => $player->id, 'slug' => $player->name]) }}"
               class="hover:underline font-medium text-zinc-800 dark:text-white">
                {{ $player->name }}
            </a>
        </flux:table.cell>
        <flux:table.cell>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                     class="w-5 h-3.5 rounded-sm shrink-0" alt="{{ $player->country }}">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $player->country }}</span>
            </div>
        </flux:table.cell>
        <flux:table.cell>
            <span class="text-sm font-bold {{ $raceColors[$player->race] ?? 'text-zinc-400' }}">
                {{ $player->race }}
            </span>
        </flux:table.cell>
        <flux:table.cell></flux:table.cell>
        @auth
            @if(auth()->user()->canManageGames())
            <flux:table.cell>
                <div class="flex items-center gap-2">
                    <flux:button size="sm" variant="ghost"
                        wire:click="edit({{ $player->id }})"
                        wire:loading.attr="disabled"
                        wire:target="edit({{ $player->id }})">Edit</flux:button>
                    <flux:modal.trigger name="delete-player-{{ $player->id }}">
                        <flux:button size="sm" variant="danger">Delete</flux:button>
                    </flux:modal.trigger>
                    <flux:modal name="delete-player-{{ $player->id }}" class="min-w-[22rem]">
                        <form class="space-y-6" wire:submit="delete({{ $player->id }})">
                            <div>
                                <flux:heading size="lg">Delete player?</flux:heading>
                                <flux:subheading class="mt-2">Are you sure you want to delete <strong>{{ $player->name }}</strong>?</flux:subheading>
                            </div>
                            <div class="flex justify-end gap-2">
                                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                                <flux:button type="submit" variant="danger" wire:loading.attr="disabled">Delete</flux:button>
                            </div>
                        </form>
                    </flux:modal>
                </div>
            </flux:table.cell>
            @endif
        @endauth
    </flux:table.row>

    {{-- Alias rows --}}
    @foreach($player->aliases as $alias)
    <flux:table.row :key="'alias-'.$alias->id" class="bg-zinc-50 dark:bg-zinc-800/50">
        <flux:table.cell>
            <div class="flex items-center gap-2 pl-4">
                <span class="text-xs text-zinc-400">↳</span>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $alias->name }}</span>
            </div>
        </flux:table.cell>
        <flux:table.cell></flux:table.cell>
        <flux:table.cell></flux:table.cell>
        <flux:table.cell>
            <span class="text-xs text-zinc-400">alias of {{ $player->name }}</span>
        </flux:table.cell>
        @auth
            @if(auth()->user()->canManageGames())
            <flux:table.cell>
                <div class="flex items-center gap-2">
                    <flux:button size="sm" variant="ghost"
                        wire:click="edit({{ $alias->id }})"
                        wire:loading.attr="disabled"
                        wire:target="edit({{ $alias->id }})">Edit</flux:button>
                    <flux:modal.trigger name="delete-player-{{ $alias->id }}">
                        <flux:button size="sm" variant="danger">Delete</flux:button>
                    </flux:modal.trigger>
                    <flux:modal name="delete-player-{{ $alias->id }}" class="min-w-[22rem]">
                        <form class="space-y-6" wire:submit="delete({{ $alias->id }})">
                            <div>
                                <flux:heading size="lg">Delete alias?</flux:heading>
                                <flux:subheading class="mt-2">Are you sure you want to delete alias <strong>{{ $alias->name }}</strong>?</flux:subheading>
                            </div>
                            <div class="flex justify-end gap-2">
                                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                                <flux:button type="submit" variant="danger" wire:loading.attr="disabled">Delete</flux:button>
                            </div>
                        </form>
                    </flux:modal>
                </div>
            </flux:table.cell>
            @endif
        @endauth
    </flux:table.row>
    @endforeach
@endforeach
</flux:table.rows>
    </flux:table>

    {{-- Add Player Modal --}}
    <flux:modal name="add-player" class="min-w-[32rem]" wire:model="showAddModal">
        <form class="space-y-6" wire:submit="save">
            <div>
                <flux:heading size="lg">Add Player</flux:heading>
            </div>

            <flux:input 
                wire:model="name" 
                label="Player Name" 
                placeholder="e.g. Flash"
            />

            <div class="grid grid-cols-2 gap-4">
                <div x-data="{
                    open: false,
                    search: '',
                    selected: 0,
                    get filtered() {
                        if (!this.search) return $wire.countriesList;
                        return $wire.countriesList.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase()));
                    }
                }" class="relative">
                    <flux:label>Country</flux:label>
                    <flux:input
                        x-model="search"
                        placeholder="Search country..."
                        autocomplete="off"
                        x-on:focus="open = true; selected = 0"
                        x-on:click.away="open = false"
                        x-on:input="open = true; selected = 0"
                        x-on:keydown.arrow-down.prevent="selected = Math.min(selected + 1, filtered.length - 1)"
                        x-on:keydown.arrow-up.prevent="selected = Math.max(selected - 1, 0)"
                        x-on:keydown.enter.prevent="if (filtered[selected]) { $wire.set('country', filtered[selected].code); search = filtered[selected].name; open = false; }"
                    />
                    <div x-show="open && filtered.length > 0"
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="(c, index) in filtered" :key="c.code">
                            <button type="button"
                                    x-bind:class="selected === index ? 'bg-indigo-100 dark:bg-indigo-900' : ''"
                                    x-on:mouseover="selected = index"
                                    x-on:click="$wire.set('country', c.code); search = c.name; open = false; selected = 0"
                                    class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                                <img :src="`/images/country_flags/${c.code.toLowerCase()}.svg`" class="w-5 h-3.5 rounded-sm shrink-0">
                                <span x-text="c.name" class="text-zinc-800 dark:text-white"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <flux:select wire:model="race" label="Race">
                <option value="Terran">Terran</option>
                <option value="Zerg">Zerg</option>
                <option value="Protoss">Protoss</option>
                <option value="Random">Random</option>
                <option value="Unknown">Unknown</option>
            </flux:select>

            {{-- AKA Autocomplete --}}
            <div x-data="{ open: false, selected: 0 }" class="relative">
                <flux:input 
                    wire:model.live.debounce.300ms="akaSearch" 
                    label="AKA (Also Known As)" 
                    placeholder="Search for main player..."
                    autocomplete="off"
                    x-on:focus="open = true"
                    x-on:input="open = true"
                    x-on:click.away="open = false"
                    x-on:keydown.arrow-down.prevent="if (open) selected = Math.min(selected + 1, {{ max($this->akaResults->count() - 1, 0) }})"
                    x-on:keydown.arrow-up.prevent="if (open) selected = Math.max(selected - 1, 0)"
                    x-on:keydown.enter.prevent="if (open && {{ $this->akaResults->count() }} > 0) { $refs['aka-' + selected].click(); }"
                />
                <flux:subheading class="mt-1">Optional: Link this as an alias of another player</flux:subheading>
                
                @if($this->akaResults->isNotEmpty())
                <div x-show="open" 
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    @foreach($this->akaResults as $index => $p)
                    <button type="button"
                            x-ref="aka-{{ $index }}"
                            wire:click="selectAka({{ $p->id }}, '{{ $p->name }}')"
                            x-on:click="open = false; selected = 0"
                            x-bind:class="selected === {{ $index }} ? 'bg-indigo-100 dark:bg-indigo-900' : ''"
                            class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($p->country_code) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0">
                        <span class="text-sm text-zinc-800 dark:text-white">{{ $p->name }}</span>
                        <span class="text-xs {{ $raceColors[$p->race] ?? 'text-zinc-400' }}">
                            {{ $p->race }}
                        </span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeAddModal">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Save Player
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Player Modal --}}
    <flux:modal name="edit-player" class="min-w-[32rem]" wire:model="showEditModal">
        <form class="space-y-6" wire:submit="update">
            <div>
                <flux:heading size="lg">Edit Player</flux:heading>
            </div>

            <flux:input 
                wire:model="editName" 
                label="Player Name"
            />

            <div class="grid grid-cols-2 gap-4">
                <div x-data="{
                    open: false,
                    search: '',
                    selected: 0,
                    get filtered() {
                        if (!this.search) return $wire.countriesList;
                        return $wire.countriesList.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase()));
                    }
                }"
                x-on:set-edit-country.window="
                    const match = $wire.countriesList.find(c => c.code === $event.detail.code);
                    if (match) search = match.name;
                "
                class="relative">
                    <flux:label>Country</flux:label>
                    <flux:input
                        x-model="search"
                        placeholder="Search country..."
                        autocomplete="off"
                        x-on:focus="open = true; selected = 0"
                        x-on:click.away="open = false"
                        x-on:input="open = true; selected = 0"
                        x-on:keydown.arrow-down.prevent="selected = Math.min(selected + 1, filtered.length - 1)"
                        x-on:keydown.arrow-up.prevent="selected = Math.max(selected - 1, 0)"
                        x-on:keydown.enter.prevent="if (filtered[selected]) { $wire.set('editCountry', filtered[selected].code); search = filtered[selected].name; open = false; }"
                    />
                    <div x-show="open && filtered.length > 0"
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="(c, index) in filtered" :key="c.code">
                            <button type="button"
                                    x-bind:class="selected === index ? 'bg-indigo-100 dark:bg-indigo-900' : ''"
                                    x-on:mouseover="selected = index"
                                    x-on:click="$wire.set('editCountry', c.code); search = c.name; open = false; selected = 0"
                                    class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                                <img :src="`/images/country_flags/${c.code.toLowerCase()}.svg`" class="w-5 h-3.5 rounded-sm shrink-0">
                                <span x-text="c.name" class="text-zinc-800 dark:text-white"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <flux:select wire:model="editRace" label="Race">
                <option value="Terran">Terran</option>
                <option value="Zerg">Zerg</option>
                <option value="Protoss">Protoss</option>
                <option value="Random">Random</option>
                <option value="Unknown">Unknown</option>
            </flux:select>

            {{-- Edit AKA Autocomplete --}}
            <div x-data="{ open: false, selected: 0 }" class="relative">
                <div class="flex items-center justify-between mb-1">
                    <flux:label>AKA (Also Known As)</flux:label>
                    @if($editAkaId)
                        <button type="button" wire:click="clearEditAka"
                            class="text-xs text-red-400 hover:text-red-600">
                            ✕ Remove AKA
                        </button>
                    @endif
                </div>
                <flux:input 
                    wire:model.live.debounce.300ms="editAkaSearch" 
                    placeholder="Search for main player..."
                    autocomplete="off"
                    x-on:focus="open = true"
                    x-on:input="open = true"
                    x-on:click.away="open = false"
                    x-on:keydown.arrow-down.prevent="if (open) selected = Math.min(selected + 1, {{ max($this->editAkaResults->count() - 1, 0) }})"
                    x-on:keydown.arrow-up.prevent="if (open) selected = Math.max(selected - 1, 0)"
                    x-on:keydown.enter.prevent="if (open && {{ $this->editAkaResults->count() }} > 0) { $refs['edit-aka-' + selected].click(); }"
                />
                <flux:subheading class="mt-1">Optional: Link this as an alias of another player</flux:subheading>
                
                @if($this->editAkaResults->isNotEmpty())
                <div x-show="open" 
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    @foreach($this->editAkaResults as $index => $p)
                    <button type="button"
                            x-ref="edit-aka-{{ $index }}"
                            wire:click="selectEditAka({{ $p->id }}, '{{ $p->name }}')"
                            x-on:click="open = false; selected = 0"
                            x-bind:class="selected === {{ $index }} ? 'bg-indigo-100 dark:bg-indigo-900' : ''"
                            class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($p->country_code) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0">
                        <span class="text-sm text-zinc-800 dark:text-white">{{ $p->name }}</span>
                        <span class="text-xs {{ $raceColors[$p->race] ?? 'text-zinc-400' }}">
                            {{ $p->race }}
                        </span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showEditModal', false)">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Update Player
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>