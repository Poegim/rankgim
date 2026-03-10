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
        $raceLabels = [
            'Terran'  => 'T',
            'Zerg'    => 'Z',
            'Protoss' => 'P',
            'Random'  => 'R',
            'Unknown' => '?',
        ];
    @endphp

    {{-- Toast Notifications --}}
    <div
        x-data="{ show: false }"
        x-on:game-saved.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Game saved
    </div>
    <div
        x-data="{ show: false }"
        x-on:game-updated.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Game updated
    </div>
    <div
        x-data="{ show: false }"
        x-on:game-deleted.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Game deleted
    </div>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $this->tournament->name }}</flux:heading>
            <flux:text>{{ $this->games->total() }} games</flux:text>
        </div>
        @auth
            @if(auth()->user()->canManageGames())
                <flux:button variant="primary" wire:click="openAddModal">
                    Add Game
                </flux:button>
            @endif
        @endauth
    </div>

    {{-- Games Table --}}
    <flux:table :paginate="$this->games">
        <flux:table.columns>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Winner</flux:table.column>
            <flux:table.column>Loser</flux:table.column>
            <flux:table.column>Result</flux:table.column>
            @auth
                @if(auth()->user()->canManageGames())
                    <flux:table.column></flux:table.column>
                @endif
            @endauth
        </flux:table.columns>
        <flux:table.rows>
            @foreach($this->games as $game)
            <flux:table.row :key="$game->id">
                <flux:table.cell>
                    <span class="text-xs text-zinc-400">
                        {{ \Carbon\Carbon::parse($game->date_time)->format('Y-m-d H:i') }}
                    </span>
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($game->winner->country_code) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0"
                             alt="{{ $game->winner->country }}">
                        <a href="{{ route('players.show', ['id' => $game->winner->id, 'slug' => Str::slug($game->winner->name)]) }}"
                           class="hover:underline font-medium text-green-600 dark:text-green-500">
                            {{ $game->winner->name }}
                        </a>
                        <span class="text-xs font-bold {{ $raceColors[$game->winner->race] ?? 'text-zinc-400' }}">
                            {{ $raceLabels[$game->winner->race] ?? '?' }}
                        </span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($game->loser->country_code) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0"
                             alt="{{ $game->loser->country }}">
                        <a href="{{ route('players.show', ['id' => $game->loser->id, 'slug' => Str::slug($game->loser->name)]) }}"
                           class="hover:underline text-zinc-500 dark:text-zinc-400">
                            {{ $game->loser->name }}
                        </a>
                        <span class="text-xs font-bold {{ $raceColors[$game->loser->race] ?? 'text-zinc-400' }}">
                            {{ $raceLabels[$game->loser->race] ?? '?' }}
                        </span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    @if($game->result == 3)
                        <span class="text-xs text-zinc-500">Draw</span>
                    @else
                        <span class="text-xs text-green-600 dark:text-green-500">Win</span>
                    @endif
                </flux:table.cell>
                @auth
                    @if(auth()->user()->canManageGames())
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            @can('update', $game)
                            <flux:button 
                                size="sm" 
                                variant="ghost" 
                                wire:click="edit({{ $game->id }})"
                                wire:loading.attr="disabled"
                                wire:target="edit({{ $game->id }})">
                                Edit
                            </flux:button>
                            @endcan
                            
                            @can('delete', $game)
                            <flux:modal.trigger name="delete-game-{{ $game->id }}">
                                <flux:button size="sm" variant="danger">
                                    Delete
                                </flux:button>
                            </flux:modal.trigger>
                            @endcan
                            
                            <flux:modal name="delete-game-{{ $game->id }}" class="min-w-[22rem]">
                                <form class="space-y-6" wire:submit="delete({{ $game->id }})">
                                    <div>
                                        <flux:heading size="lg">Delete game?</flux:heading>
                                        <flux:subheading class="mt-2">
                                            Are you sure you want to delete this game?
                                        </flux:subheading>
                                    </div>
                                    
                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Cancel</flux:button>
                                        </flux:modal.close>
                                        <flux:button 
                                            type="submit"
                                            variant="danger"
                                            wire:loading.attr="disabled">
                                            Delete
                                        </flux:button>
                                    </div>
                                </form>
                            </flux:modal>
                        </div>
                    </flux:table.cell>
                    @endif
                @endauth
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Add Game Modal --}}
    <flux:modal name="add-game" class="min-w-[32rem]" wire:model="showAddModal">
        <form class="space-y-6" wire:submit="save">
            <div>
                <flux:heading size="lg">Add Game</flux:heading>
            </div>

            {{-- Winner --}}
            <div x-data="{ open: false, selected: 0 }" class="relative">
                <flux:input 
                    autocomplete="off"
                    x-on:input="open = true"
                    wire:model.live.debounce.300ms="winnerSearch" 
                    label="Winner" 
                    placeholder="Search player..."
                    x-on:focus="open = true"
                    x-on:click.away="open = false"
                    x-on:keydown.arrow-down.prevent="if (open) selected = Math.min(selected + 1, {{ max($this->winnerResults->count() - 1, 0) }})"
                    x-on:keydown.arrow-up.prevent="if (open) selected = Math.max(selected - 1, 0)"
                    x-on:keydown.enter.prevent="if (open && {{ $this->winnerResults->count() }} > 0) { $refs['winner-' + selected].click(); }"
                />
                <div wire:loading wire:target="winnerSearch" class="text-xs text-zinc-500">
                    Searching...
                </div>
                @error('winnerId') 
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                @if($this->winnerResults->isNotEmpty())
                <div x-show="open" 
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    @foreach($this->winnerResults as $index => $player)
                    <button type="button"
                            x-ref="winner-{{ $index }}"
                            wire:click="selectWinner({{ $player->id }}, '{{ $player->name }}')"
                            x-on:click="open = false; selected = 0"
                            x-bind:class="selected === {{ $index }} ? 'bg-indigo-100 dark:bg-indigo-900' : ''"
                            class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0">
                        <span class="text-sm text-zinc-800 dark:text-white">{{ $player->name }}</span>
                        <span class="text-xs {{ $raceColors[$player->race] ?? 'text-zinc-400' }}">
                            {{ $raceLabels[$player->race] ?? '?' }}
                        </span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Loser --}}
            <div x-data="{ open: false, selected: 0 }" class="relative">
                <flux:input 
                    autocomplete="off"
                    x-on:input="open = true"
                    wire:model.live.debounce.300ms="loserSearch" 
                    label="Loser" 
                    placeholder="Search player..."
                    x-on:focus="open = true"
                    x-on:click.away="open = false"
                    x-on:keydown.arrow-down.prevent="if (open) selected = Math.min(selected + 1, {{ max($this->loserResults->count() - 1, 0) }})"
                    x-on:keydown.arrow-up.prevent="if (open) selected = Math.max(selected - 1, 0)"
                    x-on:keydown.enter.prevent="if (open && {{ $this->loserResults->count() }} > 0) { $refs['loser-' + selected].click(); }"
                />
                <div wire:loading wire:target="winnerSearch" class="text-xs text-zinc-500">
                    Searching...
                </div>
                @error('loserId') 
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                @if($this->loserResults->isNotEmpty())
                <div x-show="open" 
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    @foreach($this->loserResults as $index => $player)
                    <button type="button"
                            x-ref="loser-{{ $index }}"
                            wire:click="selectLoser({{ $player->id }}, '{{ $player->name }}')"
                            x-on:click="open = false; selected = 0"
                            x-bind:class="selected === {{ $index }} ? 'bg-indigo-100 dark:bg-indigo-900' : ''"
                            class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0">
                        <span class="text-sm text-zinc-800 dark:text-white">{{ $player->name }}</span>
                        <span class="text-xs {{ $raceColors[$player->race] ?? 'text-zinc-400' }}">
                            {{ $raceLabels[$player->race] ?? '?' }}
                        </span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Date & Time --}}
            <flux:input 
                type="datetime-local" 
                wire:model="dateTime" 
                label="Date & Time"
            />

            {{-- Result --}}
            <flux:select wire:model="result" label="Result">
                <option value="1">Win</option>
                <option value="3">Draw</option>
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeAddModal">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Save Game
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Game Modal --}}
    <flux:modal name="edit-game" class="min-w-[32rem]" wire:model="showEditModal">
        <form class="space-y-6" wire:submit="update">
            <div>
                <flux:heading size="lg">Edit Game</flux:heading>
            </div>

            {{-- Winner --}}
            <div x-data="{ open: false, selected: 0 }" class="relative">
                <flux:input 
                    autocomplete="off"
                    x-on:input="open = true"
                    wire:model.live.debounce.300ms="editWinnerSearch" 
                    label="Winner" 
                    placeholder="Search player..."
                    x-on:focus="open = true"
                    x-on:click.away="open = false"
                    x-on:keydown.arrow-down.prevent="if (open) selected = Math.min(selected + 1, {{ max($this->editWinnerResults->count() - 1, 0) }})"
                    x-on:keydown.arrow-up.prevent="if (open) selected = Math.max(selected - 1, 0)"
                    x-on:keydown.enter.prevent="if (open && {{ $this->editWinnerResults->count() }} > 0) { $refs['edit-winner-' + selected].click(); }"
                />
                @error('editWinnerId') 
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                @if($this->editWinnerResults->isNotEmpty())
                <div x-show="open" 
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    @foreach($this->editWinnerResults as $index => $player)
                    <button type="button"
                            x-ref="edit-winner-{{ $index }}"
                            wire:click="selectEditWinner({{ $player->id }}, '{{ $player->name }}')"
                            x-on:click="open = false; selected = 0"
                            x-bind:class="selected === {{ $index }} ? 'bg-indigo-100 dark:bg-indigo-900' : ''"
                            class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0">
                        <span class="text-sm text-zinc-800 dark:text-white">{{ $player->name }}</span>
                        <span class="text-xs {{ $raceColors[$player->race] ?? 'text-zinc-400' }}">
                            {{ $raceLabels[$player->race] ?? '?' }}
                        </span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Loser --}}
            <div x-data="{ open: false, selected: 0 }" class="relative">
                <flux:input 
                    wire:model.live.debounce.300ms="editLoserSearch" 
                    label="Loser" 
                    placeholder="Search player..."
                    autocomplete="off"
                    x-on:input="open = true"
                    x-on:focus="open = true"
                    x-on:click.away="open = false"
                    x-on:keydown.arrow-down.prevent="if (open) selected = Math.min(selected + 1, {{ max($this->editLoserResults->count() - 1, 0) }})"
                    x-on:keydown.arrow-up.prevent="if (open) selected = Math.max(selected - 1, 0)"
                    x-on:keydown.enter.prevent="if (open && {{ $this->editLoserResults->count() }} > 0) { $refs['edit-loser-' + selected].click(); }"
                />
                @error('editLoserId') 
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                @if($this->editLoserResults->isNotEmpty())
                <div x-show="open" 
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    @foreach($this->editLoserResults as $index => $player)
                    <button type="button"
                            x-ref="edit-loser-{{ $index }}"
                            wire:click="selectEditLoser({{ $player->id }}, '{{ $player->name }}')"
                            x-on:click="open = false; selected = 0"
                            x-bind:class="selected === {{ $index }} ? 'bg-indigo-100 dark:bg-indigo-900' : ''"
                            class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                             class="w-5 h-3.5 rounded-sm shrink-0">
                        <span class="text-sm text-zinc-800 dark:text-white">{{ $player->name }}</span>
                        <span class="text-xs {{ $raceColors[$player->race] ?? 'text-zinc-400' }}">
                            {{ $raceLabels[$player->race] ?? '?' }}
                        </span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Date & Time --}}
            <flux:input 
                type="datetime-local" 
                wire:model="editDateTime" 
                label="Date & Time"
            />

            {{-- Result --}}
            <flux:select wire:model="editResult" label="Result">
                <option value="1">Win</option>
                <option value="3">Draw</option>
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showEditModal', false)">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Update Game
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>