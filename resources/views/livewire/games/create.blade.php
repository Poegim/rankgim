@php
    $raceColors = [
        'Terran'  => 'text-blue-500',
        'Zerg'    => 'text-purple-500',
        'Protoss' => 'text-yellow-500',
        'Random'  => 'text-orange-400',
        'Unknown' => 'text-zinc-400',
    ];
@endphp
<div>
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('tournaments.show', $this->tournament->id) }}"
           class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <flux:heading size="xl">Add Game</flux:heading>
            <flux:text>{{ $this->tournament->name }}</flux:text>
        </div>
    </div>

    <div class="max-w-xl flex flex-col gap-6">

        {{-- Winner --}}
        <div x-data="{ open: false }" class="relative">
            <flux:input
                wire:model.live.debounce.300ms="winnerSearch"
                label="Winner"
                placeholder="Search player..."
                autocomplete="off"
                x-on:focus="open = true"
                x-on:input="open = true"
                x-on:click.away="open = false"
            />
            @if($this->winnerResults->isNotEmpty())
            <div x-show="open"
                 class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                @foreach($this->winnerResults as $player)
                <button type="button"
                        wire:click="selectWinner({{ $player->id }}, '{{ addslashes($player->name) }}')"
                        x-on:click="open = false"
                        class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                             class="w-7 h-5 rounded-sm shrink-0">
                        <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">{{ $player->name }}</span>
                        <span class="text-xs {{ $raceColors[$player->race] ?? 'text-zinc-400' }}">{{ $player->race }}</span>
                    </div>
                    @if($player->aliases->isNotEmpty())
                    <div class="pl-7 mt-0.5">
                        <span class="text-xs text-zinc-400">aka: {{ $player->aliases->pluck('name')->join(', ') }}</span>
                    </div>
                    @endif
                </button>
                @endforeach
            </div>
            @endif
            @error('winnerId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Loser --}}
        <div x-data="{ open: false }" class="relative">
            <flux:input
                wire:model.live.debounce.300ms="loserSearch"
                label="Loser"
                placeholder="Search player..."
                autocomplete="off"
                x-on:focus="open = true"
                x-on:input="open = true"
                x-on:click.away="open = false"
            />
            @if($this->loserResults->isNotEmpty())
            <div x-show="open"
                 class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                @foreach($this->loserResults as $player)
                <button type="button"
                        wire:click="selectLoser({{ $player->id }}, '{{ addslashes($player->name) }}')"
                        x-on:click="open = false"
                        class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                             class="w-7 h-5 rounded-sm shrink-0">
                        <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">{{ $player->name }}</span>
                        <span class="text-xs {{ $raceColors[$player->race] ?? 'text-zinc-400' }}">{{ $player->race }}</span>
                    </div>
                    @if($player->aliases->isNotEmpty())
                    <div class="pl-7 mt-0.5">
                        <span class="text-xs text-zinc-400">aka: {{ $player->aliases->pluck('name')->join(', ') }}</span>
                    </div>
                    @endif
                </button>
                @endforeach
            </div>
            @endif
            @error('loserId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Date --}}
        <flux:input
            type="datetime-local"
            wire:model="dateTime"
            label="Date & Time"
        />
        @error('dateTime') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

        {{-- Result --}}
        <flux:select wire:model="result" label="Result">
            <option value="1">Win / Loss</option>
            <option value="3">Draw</option>
        </flux:select>

        {{-- Actions --}}
        <div class="flex gap-3">
            <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled">
                Save Game
            </flux:button>
            <flux:button variant="ghost" href="{{ route('tournaments.show', $this->tournament->id) }}" wire:navigate>
                Cancel
            </flux:button>
        </div>
    </div>
</div>