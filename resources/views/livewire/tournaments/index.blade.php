<div>
    {{-- Toast Notifications (custom) --}}
    <div
        x-data="{ show: false }"
        x-on:cannot-delete.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-red-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ❌ Cannot delete — tournament has games
    </div>
    <div
        x-data="{ show: false }"
        x-on:tournament-saved.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Tournament saved
    </div>
    <div
        x-data="{ show: false }"
        x-on:tournament-updated.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Tournament updated
    </div>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Tournaments</flux:heading>
        <div class="flex items-center gap-3">
            <flux:text>{{ $this->tournaments->total() }} tournaments</flux:text>
            @auth
                @if(auth()->user()->canManageGames())
                    <flux:button variant="primary" wire:click="openModal">
                        Add Tournament
                    </flux:button>
                @endif
            @endauth
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <flux:input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search tournament..."
        />
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->tournaments">
        <flux:table.columns>
            <flux:table.column>Tournament</flux:table.column>
            <flux:table.column>Games</flux:table.column>
            <flux:table.column>First game</flux:table.column>
            <flux:table.column>Last game</flux:table.column>
            @auth
                @if(auth()->user()->canManageGames())
                    <flux:table.column></flux:table.column>
                @endif
            @endauth
        </flux:table.columns>

        <flux:table.rows>
            @foreach($this->tournaments as $tournament)
            
            
            @if($tournament->games_count > 0)
            <flux:table.row :key="$tournament->id">
                <flux:table.cell>
                    <a href="{{ route('tournaments.show', $tournament->id) }}"
                       class="hover:underline font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">
                        {{ $tournament->name }}
                    </a>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-zinc-500 dark:text-zinc-400">{{ $tournament->games_count }}</span>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-xs text-zinc-400">
                        {{ $tournament->first_game ? \Carbon\Carbon::parse($tournament->first_game)->format('Y-m-d') : '—' }}
                    </span>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-xs text-zinc-400">
                        {{ $tournament->last_game ? \Carbon\Carbon::parse($tournament->last_game)->format('Y-m-d') : '—' }}
                    </span>
                </flux:table.cell>
                @auth
                    @if(auth()->user()->canManageGames())
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button 
                                size="sm" 
                                variant="ghost" 
                                wire:click="edit({{ $tournament->id }})"
                                wire:loading.attr="disabled"
                                wire:target="edit({{ $tournament->id }})">
                                Edit
                            </flux:button>
                            
                            @if($tournament->games_count > 0)
                                <flux:button 
                                    size="sm" 
                                    variant="danger" 
                                    disabled
                                    wire:click="$dispatch('cannot-delete')">
                                    Delete
                                </flux:button>
                            @else
                                <flux:modal.trigger name="delete-{{ $tournament->id }}">
                                    <flux:button size="sm" variant="danger">
                                        Delete
                                    </flux:button>
                                </flux:modal.trigger>
                                
                                <flux:modal name="delete-{{ $tournament->id }}" class="min-w-[22rem]">
                                    <form class="space-y-6" wire:submit="delete({{ $tournament->id }})">
                                        <div>
                                            <flux:heading size="lg">Delete tournament?</flux:heading>
                                            <flux:subheading class="mt-2">
                                                Are you sure you want to delete <strong>{{ $tournament->name }}</strong>? 
                                                This action cannot be undone.
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
                            @endif
                        </div>
                    </flux:table.cell>
                    @endif
                @endauth
            </flux:table.row>
            @elseif(($tournament->games_count === 0) && (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isMod())))
            <flux:table.row :key="$tournament->id">
                <flux:table.cell>
                    <a href="{{ route('tournaments.show', $tournament->id) }}"
                       class="hover:underline font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">
                        {{ $tournament->name }}
                    </a>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-zinc-500 dark:text-zinc-400">{{ $tournament->games_count }}</span>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-xs text-zinc-400">
                        {{ $tournament->first_game ? \Carbon\Carbon::parse($tournament->first_game)->format('Y-m-d') : '—' }}
                    </span>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-xs text-zinc-400">
                        {{ $tournament->last_game ? \Carbon\Carbon::parse($tournament->last_game)->format('Y-m-d') : '—' }}
                    </span>
                </flux:table.cell>
                @auth
                    @if(auth()->user()->canManageGames())
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button 
                                size="sm" 
                                variant="ghost" 
                                wire:click="edit({{ $tournament->id }})"
                                wire:loading.attr="disabled"
                                wire:target="edit({{ $tournament->id }})">
                                Edit
                            </flux:button>
                            
                            @if($tournament->games_count > 0)
                                <flux:button 
                                    size="sm" 
                                    variant="danger" 
                                    disabled
                                    wire:click="$dispatch('cannot-delete')">
                                    Delete
                                </flux:button>
                            @else
                                <flux:modal.trigger name="delete-{{ $tournament->id }}">
                                    <flux:button size="sm" variant="danger">
                                        Delete
                                    </flux:button>
                                </flux:modal.trigger>
                                
                                <flux:modal name="delete-{{ $tournament->id }}" class="min-w-[22rem]">
                                    <form class="space-y-6" wire:submit="delete({{ $tournament->id }})">
                                        <div>
                                            <flux:heading size="lg">Delete tournament?</flux:heading>
                                            <flux:subheading class="mt-2">
                                                Are you sure you want to delete <strong>{{ $tournament->name }}</strong>? 
                                                This action cannot be undone.
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
                            @endif
                        </div>
                    </flux:table.cell>
                    @endif
                @endauth
            </flux:table.row>
            @endif




            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Add Tournament Modal --}}
    <flux:modal name="add-tournament" class="min-w-[28rem]" wire:model="showModal">
        <form class="space-y-6" wire:submit="save">
            <div>
                <flux:heading size="lg">Add Tournament</flux:heading>
            </div>

            <flux:input 
                wire:model="name" 
                label="Tournament name" 
                placeholder="e.g. BW Weekly #42"
            />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeModal">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Save
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Tournament Modal --}}
    <flux:modal name="edit-tournament" class="min-w-[28rem]" wire:model="showEditModal">
        <form class="space-y-6" wire:submit="update">
            <div>
                <flux:heading size="lg">Edit Tournament</flux:heading>
            </div>

            <flux:input 
                wire:model="editName" 
                label="Tournament name"
            />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showEditModal', false)">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Update
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>