@php
    // Race tab metadata. Empty string = "All" pseudo-race.
    $raceLabels = [
        ''        => 'All',
        'terran'  => 'Terran',
        'protoss' => 'Protoss',
        'zerg'    => 'Zerg',
        'random'  => 'Random',
    ];
@endphp

<div wire:poll.{{ $pollSeconds }}s class="space-y-6">

    {{-- Header with last-refresh indicator --}}
    <div class="flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            📺 Live streams
        </p>
        @if ($this->lastFetchedAt)
            <p class="text-xs {{ $this->isStale ? 'text-amber-400' : 'text-zinc-500' }}">
                @if ($this->isStale)
                    stale · updated {{ $this->lastFetchedAt->diffForHumans() }}
                @else
                    updated {{ $this->lastFetchedAt->diffForHumans() }}
                @endif
            </p>
        @endif
    </div>

    {{-- Race filter tabs --}}
    <div class="flex flex-wrap gap-1">
        @foreach ($raceLabels as $value => $label)
            @php
                $isActive = $raceFilter === $value;

                // Build colored style only for race tabs (not for "All").
                // Active tab uses solid race color background; inactive uses subtle hover.
                $style = $value !== '' && $isActive
                    ? "background: var(--color-race-{$value}); color: white;"
                    : '';
            @endphp
            <button
                type="button"
                wire:click="setRace('{{ $value }}')"
                @if ($style) style="{{ $style }}" @endif
                class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors
                    {{ $isActive && $value === '' ? 'bg-zinc-100 text-zinc-900' : '' }}
                    {{ ! $isActive ? 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700' : '' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Section: Featured (whitelist) --}}
    <div class="space-y-3">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            Featured
            <span class="ml-1 font-normal normal-case tracking-normal text-zinc-600">
                ({{ count($this->featured) }})
            </span>
        </p>

        @if (count($this->featured) === 0)
            <p class="text-sm text-zinc-400">
                No featured streams match this filter right now.
            </p>
        @else
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($this->featured as $s)
                    @include('livewire.streams.partials.card', ['s' => $s, 'showLabel' => true])
                @endforeach
            </div>
        @endif
    </div>

    {{-- Section: Other live streams (non-whitelisted) --}}
    {{-- Only meaningful when no race filter is active — non-whitelisted streams have no race in our DB. --}}
    @if ($raceFilter === '')
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                Other live streams
                <span class="ml-1 font-normal normal-case tracking-normal text-zinc-600">
                    ({{ count($this->others) }})
                </span>
            </p>

            @if (count($this->others) === 0)
                <p class="text-sm text-zinc-400">
                    No other live streams in the StarCraft category.
                </p>
            @else
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($this->others as $s)
                        @include('livewire.streams.partials.card', ['s' => $s, 'showLabel' => false])
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>