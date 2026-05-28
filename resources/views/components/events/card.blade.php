@props([
    'event',
    'canManage' => false,
])

@php
    $isLive   = $event->isLive();
    $isPast   = $event->isPast();
    $isStream = $event->isStream();
    $isOpen   = $event->isOpen();

    // Type accent — light variant is darker (readable on cream),
    // dark variant stays bright. Used via the two-span trick where the
    // color sits on text; tints/borders use rgba pairs below.
    $typeLight = $isStream ? '#7e22ce' : '#b45309';   // purple-700 / amber-700
    $typeDark  = $isStream ? '#c084fc' : '#fbbf24';   // purple-400 / amber-300

    // Timeline dot color + soft ring — LIVE/past are semantic and stay fixed,
    // future dots follow the type accent but pick the brighter dark hue
    // (it sits on the page bg, reads fine in both themes).
    $dotColor = $isLive
        ? '#f87171'
        : ($isPast ? '#a8a29e' : $typeDark);
    $dotGlow = $isLive
        ? '0 0 0 3px rgba(248,113,113,0.2)'
        : ($isPast ? 'none' : ($isStream
            ? '0 0 0 3px rgba(192,132,252,0.15)'
            : '0 0 0 3px rgba(251,191,36,0.15)'));

    // Race color resolver — single source of truth is app.css.
    // Never hardcode race hexes here.
    $raceColor = fn($race) => match($race) {
        'Terran', 'Zerg', 'Protoss', 'Random'
                => "var(--color-race-" . strtolower($race) . "-soft)",
        default => "var(--color-race-unknown-soft)",
    };

    // Countdown accent tints/borders — rgba pairs per theme.
    $cdBgLight     = $isStream ? 'rgba(126,34,206,0.08)'  : 'rgba(180,83,9,0.08)';
    $cdBgDark      = $isStream ? 'rgba(168,85,247,0.10)'  : 'rgba(245,158,11,0.10)';
    $cdBorderLight = $isStream ? 'rgba(126,34,206,0.30)'  : 'rgba(180,83,9,0.30)';
    $cdBorderDark  = $isStream ? 'rgba(168,85,247,0.25)'  : 'rgba(245,158,11,0.25)';

    $iso = $event->starts_at?->toIso8601String();
    $showCountdown = ! $isPast && ! $isLive && $iso;

    // Merge registered players and guest players into one flat list
    // so the 2-column grid always fills evenly regardless of player source.
    $allPlayers = collect($event->players->map(fn($p) => [
        'type'         => 'registered',
        'id'           => $p->id,
        'name'         => $p->name,
        'race'         => $p->race,
        'country_code' => $p->country_code,
    ]))->concat(
        collect($event->guest_players ?? [])->map(fn($g) => [
            'type'         => 'guest',
            'name'         => $g['name'],
            'race'         => $g['race'] ?? 'Unknown',
            'country_code' => $g['country_code'] ?? 'kr',
        ])
    );
@endphp

<div class="relative sm:pl-14 mb-3" wire:key="event-{{ $event->id }}">
    {{-- Timeline dot --}}
    <div @class([
            'absolute left-[14px] top-[18px] w-[11px] h-[11px] rounded-full hidden sm:block',
            'animate-pulse' => $isLive,
        ])
        style="background: {{ $dotColor }}; box-shadow: {{ $dotGlow }};"></div>

    <div @class([
            'rounded-lg border transition-colors overflow-hidden',
            // Past (muted) — light + dark pair
            'bg-travertine-75 border-travertine-300 dark:bg-zinc-900/30 dark:border-zinc-800/60 opacity-75' => $isPast,
            // Future / live — light + dark pair, type-tinted dark border kept
            'bg-travertine-50 border-travertine-300 hover:border-travertine-400 dark:bg-zinc-900/50 dark:hover:border-zinc-600/80' => ! $isPast,
            'dark:border-purple-500/25' => ! $isPast && $isStream,
            'dark:border-amber-500/25'  => ! $isPast && ! $isStream,
        ])>

        {{-- ─── Header strip — minimal: just type + status chip ──────── --}}
        <div class="flex items-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 border-b border-travertine-350 dark:border-zinc-800/60 text-xs">
            @if($isLive)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/15 text-red-700 border border-red-500/30 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/40 animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 dark:bg-red-400"></span>
                    LIVE
                </span>
            @endif

            {{-- Type label — two-span trick: darker accent in light, bright in dark --}}
            <span class="font-semibold uppercase tracking-wider text-[10px] shrink-0">
                <span class="dark:hidden" style="color: {{ $typeLight }};">{{ $isStream ? 'Stream' : 'Open' }}</span>
                <span class="hidden dark:inline" style="color: {{ $typeDark }};">{{ $isStream ? 'Stream' : 'Open' }}</span>
            </span>

            <span class="flex-1"></span>

            @if($isOpen && ! $isPast)
                <span class="inline-flex items-center gap-1 sm:gap-1.5 px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-medium bg-emerald-500/10 text-emerald-700 border border-emerald-500/25 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/25 shrink-0">
                    <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                    <span class="hidden sm:inline">Registration open</span>
                    <span class="sm:hidden">Reg open</span>
                </span>
            @elseif($isPast)
                <span class="font-mono text-[11px] text-travertine-500 dark:text-zinc-500 shrink-0">ended</span>
            @endif
        </div>

        {{-- ─── Body: content left, countdown top-right on desktop ───── --}}
        <div class="px-3 sm:px-4 py-3 sm:grid sm:grid-cols-[1fr_auto] sm:gap-4">
            <div class="min-w-0">
                {{-- Title + description --}}
                <h3 class="font-semibold text-travertine-900 dark:text-white text-base sm:text-lg leading-tight mb-1.5">
                    {{ $event->name }}
                </h3>

                @if($event->description)
                    <p class="text-xs sm:text-sm text-travertine-600 dark:text-zinc-400 leading-relaxed mb-2.5 sm:mb-3">
                        {{ $event->description }}
                    </p>
                @endif

                {{-- Players — symmetric 2-column grid so odd players don't dangle on a new row --}}
                @if($allPlayers->isNotEmpty())
                <div class="grid gap-x-3 gap-y-2 mb-3
                    {{ $allPlayers->count() > 1 ? 'grid-cols-2 xl:grid-cols-4' : 'grid-cols-1' }}">
                        @foreach($allPlayers as $p)
                            @if($p['type'] === 'registered')
                                <a href="{{ route('players.show', ['id' => $p['id'], 'slug' => \Illuminate\Support\Str::slug($p['name'])]) }}"
                                   wire:navigate
                                   class="inline-flex items-stretch h-7 rounded-md overflow-hidden border border-travertine-300 hover:border-travertine-400 dark:border-zinc-700/60 dark:hover:border-zinc-600 transition-colors min-w-0">
                                    {{-- Country flag --}}
                                    <span class="block w-9 shrink-0 bg-cover bg-center"
                                          style="background-image: url('{{ asset('images/country_flags/' . strtolower($p['country_code']) . '.svg') }}');"
                                          aria-label="{{ $p['country_code'] }}"></span>
                                    {{-- Race initial, tinted via CSS var (color-mix for the soft bg) --}}
                                    <span class="flex items-center px-2 text-[10px] font-bold uppercase tracking-wider shrink-0"
                                          style="background: color-mix(in srgb, {{ $raceColor($p['race']) }} 20%, transparent); color: {{ $raceColor($p['race']) }};">
                                        {{ strtoupper(substr($p['race'], 0, 1)) }}
                                    </span>
                                    {{-- Nick — truncated so long names don't break the grid --}}
                                    <span class="flex items-center px-3 bg-travertine-100 text-travertine-900 dark:bg-zinc-800/50 dark:text-zinc-100 font-semibold text-sm truncate">
                                        {{ $p['name'] }}
                                    </span>
                                </a>
                            @else
                                {{-- Guest player — same pill shape but not a link --}}
                                <span class="inline-flex items-stretch h-7 rounded-md overflow-hidden border border-travertine-300 dark:border-zinc-700/60 min-w-0">
                                    <span class="block w-9 shrink-0 bg-cover bg-center"
                                          style="background-image: url('{{ asset('images/country_flags/' . strtolower($p['country_code']) . '.svg') }}');"></span>
                                    <span class="flex items-center px-2 text-[10px] font-bold uppercase tracking-wider shrink-0"
                                          style="background: color-mix(in srgb, {{ $raceColor($p['race']) }} 20%, transparent); color: {{ $raceColor($p['race']) }};">
                                        {{ strtoupper(substr($p['race'], 0, 1)) }}
                                    </span>
                                    <span class="flex items-center px-3 bg-travertine-100 text-travertine-900 dark:bg-zinc-800/50 dark:text-zinc-100 font-semibold text-sm truncate">
                                        {{ $p['name'] }}
                                    </span>
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Location chip --}}
                @if($event->location)
                    <div class="flex flex-wrap gap-1.5 mb-2.5 sm:mb-3">
                        <span class="font-mono inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-md bg-travertine-100 border border-travertine-300 text-travertine-900 dark:bg-zinc-800/60 dark:border-zinc-700/60 dark:text-zinc-100 text-[11px] sm:text-xs">
                            <span class="text-travertine-500 dark:text-zinc-500">📍</span>
                            <span>{{ $event->location }}</span>
                        </span>
                    </div>
                @endif

                {{-- External links — link color is brand-provided per link, kept fixed in both themes --}}
                @if(count($event->parsedLinks()) > 0)
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($event->parsedLinks() as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-md text-[11px] sm:text-xs font-semibold transition-opacity hover:opacity-80"
                               style="background: {{ $link['color'] }}25; color: {{ $link['color'] }}; border: 0.5px solid {{ $link['color'] }}50;">
                                {{ $link['label'] ?: ucfirst($link['type']) }}
                                <span class="opacity-60">↗</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Countdown card — date prominently on top, digit blocks below --}}
            @if($showCountdown)
                <div class="mt-3 sm:mt-0 self-start"
                     x-data="{
                         target: {{ $event->starts_at->timestamp }},
                         intervalId: null,
                         d: 0, h: 0, m: 0, s: 0,
                         init() {
                             this.tick();
                             this.intervalId = setInterval(() => this.tick(), 1000);
                         },
                         destroy() {
                             if (this.intervalId) clearInterval(this.intervalId);
                         },
                         tick() {
                             const diff = this.target - Math.floor(Date.now() / 1000);
                             if (diff <= 0) { this.d = this.h = this.m = this.s = 0; return; }
                             this.d = Math.floor(diff / 86400);
                             this.h = Math.floor((diff % 86400) / 3600);
                             this.m = Math.floor((diff % 3600) / 60);
                             this.s = diff % 60;
                         }
                     }">
                    {{-- Countdown panel. All theme-aware colors flow from four CSS vars
                         set here; the .cd-* classes in app.css swap light/dark via .dark.
                         Keeps the blade free of two-span tricks and inline-bg conflicts. --}}
                    <div class="cd-panel rounded-lg border p-2 sm:p-2.5"
                         style="--cd-fg-light: {{ $typeLight }};
                                --cd-fg-dark: {{ $typeDark }};
                                --cd-bg-light: {{ $cdBgLight }};
                                --cd-bg-dark: {{ $cdBgDark }};
                                --cd-bd-light: {{ $cdBorderLight }};
                                --cd-bd-dark: {{ $cdBorderDark }};">

                        {{-- Date — large and prominent above the digit blocks --}}
                        <div class="cd-divider text-center pb-2 mb-2 border-b font-mono">
                            <div class="cd-accent text-[15px] sm:text-[17px] font-bold tracking-wide">
                                {{ $event->starts_at->format('d M') }}
                            </div>
                            <div class="cd-accent text-[12px] sm:text-[13px] font-semibold mt-0.5"
                                 style="opacity: 0.88;"
                                 x-cloak
                                 x-text="showLocal
                                     ? formatTime('{{ $iso }}', userTz)
                                     : '{{ $event->starts_at->format('H:i') }}'">
                                {{ $event->starts_at->format('H:i') }}
                            </div>
                            <span class="text-[10px] text-travertine-500 dark:text-zinc-400"
                                  x-cloak
                                  x-text="showLocal ? tzAbbr('{{ $iso }}', userTz) : 'CET'">CET</span>
                        </div>

                        {{-- Digit blocks: D / H / M / S. --}}
                        <div class="grid grid-cols-4 sm:flex gap-1">
                            @php
                                $digits = [
                                    ['x' => 'd',                            'full' => 'Days',  'short' => 'D'],
                                    ['x' => "String(h).padStart(2,'0')",    'full' => 'Hours', 'short' => 'H'],
                                    ['x' => "String(m).padStart(2,'0')",    'full' => 'Min',   'short' => 'M'],
                                    ['x' => "String(s).padStart(2,'0')",    'full' => 'Sec',   'short' => 'S'],
                                ];
                            @endphp
                            @foreach($digits as $dg)
                                <div class="cd-block text-center px-1.5 sm:px-2 py-1.5 rounded-md border min-w-0 sm:min-w-[46px]">
                                    <div class="cd-accent font-mono text-base sm:text-xl font-bold leading-none tabular-nums"
                                         x-text="{{ $dg['x'] }}"></div>
                                    <div class="text-[8px] sm:text-[9px] uppercase tracking-wider text-travertine-500 dark:text-zinc-400 font-medium mt-0.5 sm:mt-1">
                                        <span class="hidden sm:inline">{{ $dg['full'] }}</span><span class="sm:hidden">{{ $dg['short'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ─── Footer: reactions + comments ──────────────────────────── --}}
        <div class="px-3 sm:px-4 py-2 sm:py-2.5 border-t border-travertine-350 dark:border-zinc-800/60 flex items-center gap-1.5 sm:gap-2 flex-wrap">
            <livewire:reactions.reaction-bar :model="$event" :key="'reactions-' . $event->id" />

            @php $commentCount = $event->comments()->whereNull('parent_id')->count(); @endphp
            <button
                wire:click="$dispatch('open-comments', { modelType: 'App\\Models\\Event', modelId: {{ $event->id }} })"
                class="ml-auto inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 rounded-md bg-blue-500/10 border border-blue-500/30 text-blue-700 hover:bg-blue-500/15 dark:text-blue-300 dark:hover:bg-blue-500/15 transition-colors text-[11px] sm:text-xs font-medium">
                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span><strong>{{ $commentCount }}</strong> <span class="hidden sm:inline">{{ \Illuminate\Support\Str::plural('comment', $commentCount) }}</span></span>
            </button>
        </div>

        {{-- ─── Admin row (only for owner / mods) ─────────────────────── --}}
        @auth
            @if(auth()->id() === $event->created_by || $canManage)
                <div class="px-3 sm:px-4 py-1.5 border-t border-travertine-300 dark:border-zinc-800/40 flex items-center gap-2 text-[10px] sm:text-[11px] text-travertine-500 dark:text-zinc-600">
                    <span>by {{ $event->user?->name ?? 'unknown' }}</span>
                    <span class="text-travertine-400 dark:text-zinc-700">·</span>
                    <button wire:click="openEditModal({{ $event->id }})"
                        class="text-travertine-600 hover:text-travertine-900 dark:text-zinc-500 dark:hover:text-zinc-300 transition-colors">Edit</button>
                    <span class="text-travertine-400 dark:text-zinc-700">·</span>
                    <button wire:click="$set('confirmingDeleteId', {{ $event->id }})"
                        class="text-travertine-600 hover:text-red-700 dark:text-zinc-500 dark:hover:text-red-400 transition-colors">Delete</button>
                </div>
            @endif
        @endauth
    </div>
</div>