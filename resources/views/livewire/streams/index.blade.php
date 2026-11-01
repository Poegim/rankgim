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
            📺 Streams
        </p>
        @if ($this->lastFetchedAt)
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
         Main tab strip: Live / Channels
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex items-center gap-1 border-b border-travertine-350 dark:border-zinc-800">
        <button wire:click="setTab('live')"
            class="relative px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap
                {{ $tab === 'live'
                    ? 'text-travertine-900 dark:text-white'
                    : 'text-travertine-500 hover:text-travertine-700 dark:text-zinc-500 dark:hover:text-zinc-300' }}">
            <span class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full {{ $tab === 'live' ? 'bg-red-500 animate-pulse' : 'bg-travertine-300 dark:bg-zinc-700' }}"></span>
                Live
            </span>
            @if ($tab === 'live')
                <span class="absolute -bottom-px left-0 right-0 h-0.5 bg-red-500"></span>
            @endif
        </button>

        <button wire:click="setTab('channels')"
            class="relative px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap
                {{ $tab === 'channels'
                    ? 'text-travertine-900 dark:text-white'
                    : 'text-travertine-500 hover:text-travertine-700 dark:text-zinc-500 dark:hover:text-zinc-300' }}">
            <span class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full {{ $tab === 'channels' ? 'bg-oxblood dark:bg-zinc-300' : 'bg-travertine-300 dark:bg-zinc-700' }}"></span>
                All Featured Channels
                <span class="text-[10px] font-normal text-travertine-500 dark:text-zinc-600">
                    @if ($tab === 'channels')
                        ({{ count($this->channels) }})
                    @endif
                </span>
            </span>
            @if ($tab === 'channels')
                <span class="absolute -bottom-px left-0 right-0 h-0.5 bg-oxblood dark:bg-zinc-300"></span>
            @endif
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         Filter rows: platform on top, race below
         (shared between Live and Channels tabs)
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
         TAB: Live — Featured + Other sections (existing content)
         ════════════════════════════════════════════════════════════════════ --}}
    @if ($tab === 'live')

        {{-- Featured (whitelist) --}}
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

        {{-- Other live streams (non-whitelisted, hidden when race filter active) --}}
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

    @endif

    {{-- ════════════════════════════════════════════════════════════════════
         TAB: Channels — all whitelisted streamers with live/offline status
         ════════════════════════════════════════════════════════════════════ --}}
    @if ($tab === 'channels')

        @if (count($this->channels) === 0)
            <p class="text-sm text-travertine-600 dark:text-zinc-400">
                No channels match this filter.
            </p>
        @else
            <div class="overflow-hidden rounded-xl border
                        border-travertine-300 dark:border-zinc-700/60">
                <table class="w-full text-sm">
                    <thead class="text-left text-[10px] font-semibold uppercase tracking-widest
              bg-travertine-100 text-travertine-500
              dark:bg-zinc-900/40 dark:text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">
                            <button wire:click="setChannelSort('name')" class="inline-flex items-center gap-1 hover:text-travertine-800 dark:hover:text-zinc-300">
                                Channel
                                @if ($channelSort === 'name')
                                    <span class="text-oxblood dark:text-zinc-300">{{ $channelDir === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 hidden sm:table-cell">Race</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 hidden md:table-cell">
                            <button wire:click="setChannelSort('last_seen')" class="inline-flex items-center gap-1 hover:text-travertine-800 dark:hover:text-zinc-300">
                                Last seen
                                @if ($channelSort === 'last_seen')
                                    <span class="text-oxblood dark:text-zinc-300">{{ $channelDir === 'desc' ? '↓' : '↑' }}</span>
                                @endif
                            </button>
                        </th>
                    </tr>
                </thead>
                    <tbody class="divide-y divide-travertine-200 dark:divide-zinc-700/60">
                        @foreach ($this->channels as $ch)
                            <tr wire:key="ch-{{ $ch['platform'] }}-{{ $ch['user_id'] }}"
                                class="transition-colors
                                       hover:bg-travertine-75 dark:hover:bg-zinc-800/50">

                                {{-- Channel name + platform badge --}}
                                <td class="px-4 py-3">
                                    <a href="{{ $ch['channel_url'] }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="flex items-center gap-2 group">

                                        {{-- Platform badge --}}
                                        @php
                                            $badge = match ($ch['platform']) {
                                                'twitch' => ['label' => 'TW', 'bg' => '#9146ff'],
                                                'soop'   => ['label' => 'SP', 'bg' => '#ef4444'],
                                                default  => ['label' => '??', 'bg' => '#52525b'],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded text-[9px] font-bold !text-white shrink-0"
                                              style="background: {{ $badge['bg'] }};">
                                            {{ $badge['label'] }}
                                        </span>

                                        <span class="font-medium transition-colors
                                                     text-travertine-900 group-hover:text-oxblood
                                                     dark:text-zinc-200 dark:group-hover:text-white"
                                              @if ($ch['race'])
                                                  style="color: var(--color-race-{{ $ch['race'] }});"
                                              @endif
                                        >
                                            {{ $ch['label'] ?? $ch['user_id'] }}
                                        </span>
                                    </a>
                                </td>

                                {{-- Race chip --}}
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    @if ($ch['race'])
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase !text-white"
                                              style="background: var(--color-race-{{ $ch['race'] }});">
                                            {{ ucfirst($ch['race'][0]) }}
                                        </span>
                                    @else
                                        <span class="text-travertine-400 dark:text-zinc-600">—</span>
                                    @endif
                                </td>

                                {{-- Live status --}}
                                <td class="px-4 py-3">
                                    @if ($ch['is_live'])
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                            LIVE
                                            @if ($ch['viewers'])
                                                <span class="font-normal text-travertine-500 dark:text-zinc-500">
                                                    · {{ number_format($ch['viewers']) }}
                                                </span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-xs text-travertine-400 dark:text-zinc-600">
                                            Offline
                                        </span>
                                    @endif
                                </td>

                                {{-- Last seen --}}
                                <td class="px-4 py-3 hidden md:table-cell text-xs
                                           text-travertine-500 dark:text-zinc-500">
                                    @if ($ch['is_live'])
                                        <span class="text-red-600 dark:text-red-400">Now</span>
                                    @elseif ($ch['last_seen_live_at'])
                                        {{ $ch['last_seen_live_at']->diffForHumans() }}
                                    @else
                                        <span class="text-travertine-400 dark:text-zinc-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    @endif
</div>