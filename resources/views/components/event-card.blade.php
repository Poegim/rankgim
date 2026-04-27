@props(['event'])

@php
    $isLive   = $event->isLive();
    $isPast   = $event->isPast();
    $isStream = $event->isStream();
    $isOpen   = $event->isOpen();

    $typeLabelColor = $isStream ? '#c084fc' : '#fcd34d';
    $cardBorder = $isPast
        ? 'border-zinc-800/60'
        : ($isStream ? 'border-purple-500/25' : 'border-amber-500/25');

    $raceColor = fn($race) => match($race) {
        'Terran'  => '#60a5fa',
        'Zerg'    => '#fb7185',
        'Protoss' => '#e8c66b',
        'Random'  => '#fb923c',
        default   => '#a1a1aa',
    };

    $countdownColor  = $isStream ? '#c084fc' : '#fbbf24';
    $countdownBg     = $isStream ? 'rgba(168,85,247,0.10)' : 'rgba(245,158,11,0.10)';
    $countdownBorder = $isStream ? 'rgba(168,85,247,0.25)' : 'rgba(245,158,11,0.25)';

    $iso = $event->starts_at?->toIso8601String();
    $showCountdown = ! $isPast && ! $isLive && $iso;
@endphp

<div @class([
        'rounded-xl border transition-colors overflow-hidden',
        $cardBorder,
        'bg-zinc-900/30' => $isPast,
        'bg-zinc-900/50 hover:border-zinc-600/80' => ! $isPast,
        'opacity-75' => $isPast,
    ])>

    {{-- Header strip --}}
    <div class="flex items-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 border-b border-zinc-800/60 text-xs">
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
            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-medium bg-emerald-500/15 text-emerald-300 border border-emerald-500/25 shrink-0">
                <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-emerald-400"></span>
                <span class="hidden sm:inline">Registration open</span>
                <span class="sm:hidden">Reg open</span>
            </span>
        @elseif($isPast)
            <span class="font-mono text-[11px] text-zinc-500 shrink-0">ended</span>
        @endif
    </div>

    {{-- Body: content + countdown --}}
    <div class="px-3 sm:px-4 py-3 sm:grid sm:grid-cols-[1fr_auto] sm:gap-4">
        <div class="min-w-0">
            <h3 class="font-semibold text-white text-base sm:text-lg leading-tight mb-1.5">
                {{ $event->name }}
            </h3>

            @if($event->description)
                <p class="text-xs sm:text-sm text-zinc-400 leading-relaxed mb-2.5 sm:mb-3">
                    {{ $event->description }}
                </p>
            @endif

            {{-- Players --}}
            @if($event->players->isNotEmpty() || ! empty($event->guest_players))
                <div class="flex flex-wrap gap-2 mb-3">
                @foreach($event->players as $p)
                    <a href="{{ route('players.show', ['id' => $p->id, 'slug' => \Illuminate\Support\Str::slug($p->name)]) }}"
                       wire:navigate
                       class="inline-flex items-stretch h-7 rounded-md overflow-hidden border border-zinc-700/60 hover:border-zinc-600 transition-colors">
                        {{-- Flag flush left, like in match-card --}}
                        <span class="block w-9 shrink-0 bg-cover bg-center"
                              style="background-image: url('{{ asset('images/country_flags/' . strtolower($p->country_code) . '.svg') }}');"
                              aria-label="{{ $p->country_code }}"></span>
                        {{-- Race name in race-tinted block --}}
                        <span class="flex items-center px-2 text-[10px] font-bold uppercase tracking-wider"
                              style="background: {{ $raceColor($p->race) }}33; color: {{ $raceColor($p->race) }};">
                            {{ strtoupper(substr($p->race, 0, 1)) }}
                        </span>
                        {{-- Nick on the right --}}
                        <span class="flex items-center px-3 bg-zinc-800/50 text-zinc-100 font-semibold text-sm">
                            {{ $p->name }}
                        </span>
                    </a>
                @endforeach

                @if(! empty($event->guest_players))
                    @foreach($event->guest_players as $g)
                        @php $gRace = $g['race'] ?? 'Unknown'; @endphp
                        <span class="inline-flex items-stretch h-7 rounded-md overflow-hidden border border-zinc-700/60">
                            <span class="block w-9 shrink-0 bg-cover bg-center"
                                  style="background-image: url('{{ asset('images/country_flags/' . strtolower($g['country_code'] ?? 'kr') . '.svg') }}');"></span>
                            <span class="flex items-center px-2 text-[10px] font-bold uppercase tracking-wider"
                                  style="background: {{ $raceColor($gRace) }}33; color: {{ $raceColor($gRace) }};">
                                {{ strtoupper(substr($gRace, 0, 1)) }}
                            </span>
                            <span class="flex items-center px-3 bg-zinc-800/50 text-zinc-100 font-semibold text-sm">
                                {{ $g['name'] }}
                            </span>
                        </span>
                    @endforeach
                @endif
                </div>
            @endif

            {{-- Location chip — date moved into the countdown card --}}
            @if($event->location)
                <div class="flex flex-wrap gap-1.5 mb-2.5 sm:mb-3">
                    <span class="font-mono inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-md bg-zinc-800/60 border border-zinc-700/60 text-[11px] sm:text-xs text-zinc-100">
                        <span class="text-zinc-500">📍</span>
                        <span>{{ $event->location }}</span>
                    </span>
                </div>
            @endif

            {{-- External links --}}
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

        {{-- Countdown card — blocks + start date --}}
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
                <div class="rounded-lg border p-2 sm:p-2.5"
                     style="background: rgba(245,158,11,0.05); border-color: {{ $countdownBorder }};">

                    <div class="grid grid-cols-4 sm:flex gap-1 mb-2">
                        <div class="text-center px-1.5 sm:px-2 py-1.5 rounded-md border min-w-0 sm:min-w-[46px]"
                             style="background: {{ $countdownBg }}; border-color: {{ $countdownBorder }};">
                            <div class="font-mono text-base sm:text-xl font-bold leading-none tabular-nums"
                                 style="color: {{ $countdownColor }};"
                                 x-text="d"></div>
                            <div class="text-[8px] sm:text-[9px] uppercase tracking-wider text-zinc-400 font-medium mt-0.5 sm:mt-1">
                                <span class="hidden sm:inline">Days</span><span class="sm:hidden">D</span>
                            </div>
                        </div>
                        <div class="text-center px-1.5 sm:px-2 py-1.5 rounded-md border min-w-0 sm:min-w-[46px]"
                             style="background: {{ $countdownBg }}; border-color: {{ $countdownBorder }};">
                            <div class="font-mono text-base sm:text-xl font-bold leading-none tabular-nums"
                                 style="color: {{ $countdownColor }};"
                                 x-text="String(h).padStart(2,'0')"></div>
                            <div class="text-[8px] sm:text-[9px] uppercase tracking-wider text-zinc-400 font-medium mt-0.5 sm:mt-1">
                                <span class="hidden sm:inline">Hours</span><span class="sm:hidden">H</span>
                            </div>
                        </div>
                        <div class="text-center px-1.5 sm:px-2 py-1.5 rounded-md border min-w-0 sm:min-w-[46px]"
                             style="background: {{ $countdownBg }}; border-color: {{ $countdownBorder }};">
                            <div class="font-mono text-base sm:text-xl font-bold leading-none tabular-nums"
                                 style="color: {{ $countdownColor }};"
                                 x-text="String(m).padStart(2,'0')"></div>
                            <div class="text-[8px] sm:text-[9px] uppercase tracking-wider text-zinc-400 font-medium mt-0.5 sm:mt-1">
                                <span class="hidden sm:inline">Min</span><span class="sm:hidden">M</span>
                            </div>
                        </div>
                        <div class="text-center px-1.5 sm:px-2 py-1.5 rounded-md border min-w-0 sm:min-w-[46px]"
                             style="background: {{ $countdownBg }}; border-color: {{ $countdownBorder }};">
                            <div class="font-mono text-base sm:text-xl font-bold leading-none tabular-nums"
                                 style="color: {{ $countdownColor }};"
                                 x-text="String(s).padStart(2,'0')"></div>
                            <div class="text-[8px] sm:text-[9px] uppercase tracking-wider text-zinc-400 font-medium mt-0.5 sm:mt-1">
                                <span class="hidden sm:inline">Sec</span><span class="sm:hidden">S</span>
                            </div>
                        </div>
                    </div>

                    {{-- Start date under the blocks. Dashboard widget is CET-only,
                         no toggle to user timezone. --}}
                    <div class="text-center pt-2 border-t font-mono"
                         style="border-color: {{ $countdownBorder }};">
                        <span class="text-[12px] sm:text-[13px] font-semibold"
                              style="color: {{ $countdownColor }};">{{ $event->starts_at->format('d M H:i') }}</span>
                        <span class="text-[10px] text-zinc-400 ml-1">CET</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>