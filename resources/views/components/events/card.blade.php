@props([
    'event',
    'canManage' => false,
])

@php
    $isLive   = $event->isLive();
    $isPast   = $event->isPast();
    $isStream = $event->isStream();
    $isOpen   = $event->isOpen();

    // Type label color — tinted text instead of a heavier pill badge.
    $typeLabelColor = $isStream ? '#c084fc' : '#fcd34d';

    // Card border accent depends on type and past/future state.
    $cardBorder = $isPast
        ? 'border-zinc-800/60'
        : ($isStream ? 'border-purple-500/25' : 'border-amber-500/25');

    // Timeline dot color + soft ring around it.
    $dotColor = $isLive
        ? '#f87171'
        : ($isPast ? '#52525b' : ($isStream ? '#c084fc' : '#fbbf24'));
    $dotGlow = $isLive
        ? '0 0 0 3px rgba(248,113,113,0.2)'
        : ($isPast ? 'none' : ($isStream
            ? '0 0 0 3px rgba(192,132,252,0.15)'
            : '0 0 0 3px rgba(251,191,36,0.15)'));

    // Race color resolver — used in both registered and guest player lists.
    // Mirrors what's defined in app.css @theme: --color-race-{race}-soft.
    $raceColor = fn($race) => match($race) {
        'Terran'  => '#60a5fa',
        'Zerg'    => '#fb7185',
        'Protoss' => '#e8c66b',
        'Random'  => '#fb923c',
        default   => '#a1a1aa',
    };

    // ISO timestamp for client-side timezone formatting.
    $iso = $event->starts_at?->toIso8601String();
@endphp

<div class="relative sm:pl-14 mb-3" wire:key="event-{{ $event->id }}">
    {{-- Timeline dot --}}
    <div @class([
            'absolute left-[14px] top-[18px] w-[11px] h-[11px] rounded-full hidden sm:block',
            'animate-pulse' => $isLive,
        ])
        style="background: {{ $dotColor }}; box-shadow: {{ $dotGlow }};"></div>

    <div @class([
            'rounded-xl border transition-colors overflow-hidden',
            $cardBorder,
            'bg-zinc-900/30' => $isPast,
            'bg-zinc-900/50 hover:border-zinc-600/80' => ! $isPast,
            'opacity-75' => $isPast,
        ])>

        {{-- ─── Header strip ──────────────────────────────────────────── --}}
        <div class="flex items-center gap-2 px-4 py-2.5 border-b border-zinc-800/60 text-xs">
            @if($isLive)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/20 text-red-300 border border-red-500/40 animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                    LIVE
                </span>
            @endif

            <span class="font-semibold uppercase tracking-wider text-[10px] shrink-0"
                  style="color: {{ $typeLabelColor }};">
                {{ $isStream ? 'Stream' : 'Open' }}
            </span>

            <span class="flex-1"></span>

            @if($isOpen && ! $isPast)
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/15 text-emerald-300 border border-emerald-500/25 shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Registration open
                </span>
            @elseif($isPast)
                <span class="font-mono text-[11px] text-zinc-500 shrink-0">ended</span>
            @endif
        </div>

        {{-- ─── Body ──────────────────────────────────────────────────── --}}
        <div class="px-4 py-3">
            <h3 class="font-semibold text-white text-base sm:text-lg leading-tight mb-2">{{ $event->name }}</h3>

            @if($event->description)
                <p class="text-sm text-zinc-400 leading-relaxed mb-3">{{ $event->description }}</p>
            @endif

            {{-- Players list (registered + guests) --}}
            @if($event->players->isNotEmpty() || ! empty($event->guest_players))
                <div class="flex flex-wrap gap-x-3 gap-y-2 mb-3">
                    @foreach($event->players as $p)
                        <a href="{{ route('players.show', ['id' => $p->id, 'slug' => \Illuminate\Support\Str::slug($p->name)]) }}"
                           wire:navigate
                           class="inline-flex items-center gap-1.5 text-xs sm:text-sm hover:opacity-80 transition-opacity"
                           style="color: {{ $raceColor($p->race) }};">
                            <img src="{{ asset('images/country_flags/' . strtolower($p->country_code) . '.svg') }}"
                                 class="w-4 h-3 rounded-sm shrink-0" alt="{{ $p->country_code }}">
                            <span class="font-medium">{{ $p->name }}</span>
                        </a>
                    @endforeach

                    @if(! empty($event->guest_players))
                        @foreach($event->guest_players as $g)
                            <span class="inline-flex items-center gap-1.5 text-xs sm:text-sm"
                                  style="color: {{ $raceColor($g['race'] ?? 'Unknown') }};">
                                <img src="{{ asset('images/country_flags/' . strtolower($g['country_code'] ?? 'kr') . '.svg') }}"
                                     class="w-4 h-3 rounded-sm shrink-0">
                                <span class="font-medium">{{ $g['name'] }}</span>
                            </span>
                        @endforeach
                    @endif
                </div>
            @endif

            {{-- ─── Meta row: date + location + links ─────────────────── --}}
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                @if($iso)
                    <span class="font-mono text-[11px] text-zinc-400 inline-flex items-center gap-1.5 whitespace-nowrap">
                        <span class="text-zinc-600">🗓</span>
                        {{-- Server-rendered fallback (CET). Alpine x-text overrides
                             when the parent's $showLocal flips to true. --}}
                        <span class="text-zinc-300"
                              x-text="showLocal
                                  ? formatTime('{{ $iso }}', userTz)
                                  : formatTime('{{ $iso }}', 'Europe/Warsaw')"
                              x-cloak>{{ $event->starts_at->format('d M, H:i') }}</span>
                        <span class="text-zinc-600 text-[10px] uppercase"
                              x-text="showLocal
                                  ? tzAbbr('{{ $iso }}', userTz)
                                  : 'CET'"
                              x-cloak>CET</span>
                    </span>
                @endif

                @if($event->location)
                    <span class="font-mono text-[11px] text-zinc-400 inline-flex items-center gap-1.5">
                        <span class="text-zinc-600">📍</span>
                        <span>{{ $event->location }}</span>
                    </span>
                @endif

                {{-- Mobile-only registration chip (desktop has it in header) --}}
                @if($isOpen && ! $isPast)
                    <span class="sm:hidden inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/15 text-emerald-300 border border-emerald-500/25">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Registration open
                    </span>
                @endif

                {{-- Links pushed to the right --}}
                @if(count($event->parsedLinks()) > 0)
                    <span class="ml-auto flex flex-wrap items-center gap-1.5">
                        @foreach($event->parsedLinks() as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium transition-opacity hover:opacity-80"
                               style="background: {{ $link['color'] }}20; color: {{ $link['color'] }}; border: 0.5px solid {{ $link['color'] }}40;">
                                {{ $link['label'] ?: ucfirst($link['type']) }} ↗
                            </a>
                        @endforeach
                    </span>
                @endif
            </div>
        </div>

        {{-- ─── Footer: reactions / comments / admin actions ─────────── --}}
        <div class="px-4 py-2 border-t border-zinc-800/60 flex items-center gap-3 text-xs">
            <livewire:reactions.reaction-bar :model="$event" :key="'reactions-' . $event->id" />

            <button
                wire:click="$dispatch('open-comments', { modelType: 'App\\Models\\Event', modelId: {{ $event->id }} })"
                class="flex items-center gap-1.5 text-zinc-500 hover:text-zinc-300 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                @php $commentCount = $event->comments()->whereNull('parent_id')->count(); @endphp
                {{ $commentCount }} {{ \Illuminate\Support\Str::plural('comment', $commentCount) }}
            </button>

            @auth
                @if(auth()->id() === $event->created_by || $canManage)
                    <span class="ml-auto flex items-center gap-2 text-[11px] text-zinc-600">
                        <span>by {{ $event->user?->name ?? 'unknown' }}</span>
                        <span class="text-zinc-700">·</span>
                        <button wire:click="openEditModal({{ $event->id }})"
                            class="text-zinc-500 hover:text-zinc-300 transition-colors">Edit</button>
                        <span class="text-zinc-700">·</span>
                        <button wire:click="$set('confirmingDeleteId', {{ $event->id }})"
                            class="text-zinc-500 hover:text-red-400 transition-colors">Delete</button>
                    </span>
                @endif
            @endauth
        </div>
    </div>
</div>