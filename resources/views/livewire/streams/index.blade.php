@php
    // Race tab metadata. Empty string = "All" pseudo-race.
    $raceLabels = [
        ''        => 'All',
        'terran'  => 'Terran',
        'protoss' => 'Protoss',
        'zerg'    => 'Zerg',
        'random'  => 'Random',
    ];

    // Platform tab metadata. Empty string = "All platforms".
    $platformLabels = [
        ''       => 'All',
        'soop'   => 'SOOP',
        'twitch' => 'Twitch',
    ];

    // Per-platform accent colors for the active-state pill (matches card badges).
    // Brand colors — identical in both themes.
    $platformAccents = [
        'soop'   => '#ef4444', // SOOP red
        'twitch' => '#9146ff', // Twitch official purple
    ];
@endphp

<div wire:poll.{{ $pollSeconds }}s class="space-y-6">

    {{-- ════════════════════════════════════════════════════════════════════
         Header with last-refresh indicator
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex items-center justify-between">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em]
                  text-oxblood dark:text-zinc-500">
            📺 Live streams
        </p>
        @if ($this->lastFetchedAt)
            {{-- Stale = warning state, otherwise muted hint --}}
            <p @class([
                'text-xs',
                'text-amber-700 dark:text-amber-400' => $this->isStale,
                'text-travertine-500 dark:text-zinc-500' => ! $this->isStale,
            ])>
                @if ($this->isStale)
                    stale · updated {{ $this->lastFetchedAt->diffForHumans() }}
                @else
                    updated {{ $this->lastFetchedAt->diffForHumans() }}
                @endif
            </p>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         Filter rows: platform on top, race below
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-2">

        {{-- Platform filter tabs --}}
        <div class="flex flex-wrap items-center gap-1">
            <span class="mr-1 text-[10px] font-semibold uppercase tracking-widest
                         text-travertine-500 dark:text-zinc-600">
                Platform
            </span>
            @foreach ($platformLabels as $value => $label)
                @php
                    $isActive = $platformFilter === $value;
                    $accent   = $platformAccents[$value] ?? null;

                    // Active state for a specific platform → tint with brand accent.
                    // Active state for "All" → handled via Tailwind classes below.
                    $style = $value !== '' && $isActive
                        ? "background: {$accent}; color: white;"
                        : '';
                @endphp
                <button
                    type="button"
                    wire:click="setPlatform('{{ $value }}')"
                    @if ($style) style="{{ $style }}" @endif
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors
                        {{ $isActive && $value === ''
                            ? 'bg-oxblood text-oxblood-content dark:bg-zinc-100 dark:text-zinc-900'
                            : '' }}
                        {{ ! $isActive
                            ? 'text-travertine-600 hover:text-oxblood hover:bg-travertine-200 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700'
                            : '' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Race filter tabs --}}
        <div class="flex flex-wrap items-center gap-1">
            <span class="mr-1 text-[10px] font-semibold uppercase tracking-widest
                         text-travertine-500 dark:text-zinc-600">
                Race
            </span>
            @foreach ($raceLabels as $value => $label)
                @php
                    $isActive = $raceFilter === $value;

                    // Active tab for a race → solid race-color background via CSS var.
                    // Race var auto-adjusts per theme (darker on cream).
                    $style = $value !== '' && $isActive
                        ? "background: var(--color-race-{$value}); color: white;"
                        : '';
                @endphp
                <button
                    type="button"
                    wire:click="setRace('{{ $value }}')"
                    @if ($style) style="{{ $style }}" @endif
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors
                        {{ $isActive && $value === ''
                            ? 'bg-oxblood text-oxblood-content dark:bg-zinc-100 dark:text-zinc-900'
                            : '' }}
                        {{ ! $isActive
                            ? 'text-travertine-600 hover:text-oxblood hover:bg-travertine-200 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700'
                            : '' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         Section: Featured (whitelist)
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-3">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em]
                  text-oxblood dark:text-zinc-500">
            Featured
            <span class="ml-1 font-sans font-normal normal-case tracking-normal
                         text-travertine-500 dark:text-zinc-600">
                ({{ count($this->featured) }})
            </span>
        </p>

        @if (count($this->featured) === 0)
            <p class="text-sm text-travertine-600 dark:text-zinc-400">
                No featured streams match this filter right now.
            </p>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($this->featured as $s)
                    <div wire:key="card-{{ $s['platform'] }}-{{ $s['user_id'] }}">
                        @include('livewire.streams.partials.card', ['s' => $s, 'showLabel' => true])
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         Section: Other live streams (non-whitelisted).
         Hidden when a race filter is active — non-whitelisted streams have
         no race in our DB.
         ════════════════════════════════════════════════════════════════════ --}}
    @if ($raceFilter === '')
        <div class="space-y-3">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em]
                      text-oxblood dark:text-zinc-500">
                Other live streams
                <span class="ml-1 font-sans font-normal normal-case tracking-normal
                             text-travertine-500 dark:text-zinc-600">
                    ({{ count($this->others) }})
                </span>
            </p>

            @if (count($this->others) === 0)
                <p class="text-sm text-travertine-600 dark:text-zinc-400">
                    No other live streams match this filter right now.
                </p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($this->others as $s)
                        <div wire:key="card-{{ $s['platform'] }}-{{ $s['user_id'] }}">
                            @php $s['is_featured'] = false @endphp
                            @include('livewire.streams.partials.card', ['s' => $s, 'showLabel' => false])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>