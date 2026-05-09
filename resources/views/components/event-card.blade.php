@props(['event'])

@php
    $isLive   = $event->isLive();
    $isPast   = $event->isPast();
    $isStream = $event->isStream();
    $isOpen   = $event->isOpen();

    // Type label colors — both light and dark inline since we cannot use dark: on style.
    // Light: deeper purple/amber for contrast on cream.
    // Dark: bright pastels for contrast on near-black.
    $typeLabelColorLight = $isStream ? '#7e22ce' : '#b45309';
    $typeLabelColorDark  = $isStream ? '#c084fc' : '#fcd34d';

    // Race tint colors — use CSS vars so they auto-adjust per theme.
    // Vars are defined in app.css under :root and :root:not(.dark).
    $raceVar = fn($race) => match($race) {
        'Terran'  => ['base' => 'var(--color-race-terran)',  'soft' => 'var(--color-race-terran-soft)'],
        'Zerg'    => ['base' => 'var(--color-race-zerg)',    'soft' => 'var(--color-race-zerg-soft)'],
        'Protoss' => ['base' => 'var(--color-race-protoss)', 'soft' => 'var(--color-race-protoss-soft)'],
        'Random'  => ['base' => 'var(--color-race-random)',  'soft' => 'var(--color-race-random-soft)'],
        default   => ['base' => 'var(--color-race-unknown)', 'soft' => 'var(--color-race-unknown-soft)'],
    };

    // Countdown card colors — amber for Open, purple for Stream.
    // Two sets per theme since we render two blocks (dark:hidden / hidden dark:block).
    // Light tints lower opacity than dark since they sit on cream, not near-black.
    $countdownColorLight  = $isStream ? '#7e22ce' : '#b45309';
    $countdownColorDark   = $isStream ? '#c084fc' : '#fbbf24';
    $countdownBgLight     = $isStream ? 'rgba(126,34,206,0.06)' : 'rgba(180,83,9,0.07)';
    $countdownBgDark      = $isStream ? 'rgba(168,85,247,0.10)' : 'rgba(245,158,11,0.10)';
    $countdownBorderLight = $isStream ? 'rgba(126,34,206,0.25)' : 'rgba(180,83,9,0.30)';
    $countdownBorderDark  = $isStream ? 'rgba(168,85,247,0.25)' : 'rgba(245,158,11,0.25)';

    $iso = $event->starts_at?->toIso8601String();
    $showCountdown = ! $isPast && ! $isLive && $iso;

    // Merge registered + guest players into a single flat list so the grid
    // fills columns evenly regardless of player source.
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

    // Grid column count: 2 columns for 2+ players, 1 for solo.
    $playerCols = $allPlayers->count() > 1 ? 2 : 1;
@endphp

{{-- Outer event card — sits inside upcoming-events wrapper.                --}}
{{-- Light: sand bg (sunken below parchment container).                     --}}
{{-- Dark: zinc-900 (sunken below zinc-800 container).                      --}}
{{-- Border is travertine-300 in BOTH active/Stream/Open — colored borders  --}}
{{-- removed for visual calm; type is communicated via label + countdown.   --}}
<div @class([
        'rounded-lg border transition-colors overflow-hidden',
        'border-travertine-300 dark:border-zinc-800/60' => true,
        'bg-travertine-100/50 dark:bg-zinc-900/30' => $isPast,
        'bg-travertine-75 hover:border-travertine-400 dark:bg-zinc-900/50 dark:hover:border-zinc-600/80' => ! $isPast,
        'opacity-75' => $isPast,
    ])>

    {{-- Header strip — small bar with LIVE/type/registration --}}
    <div class="flex items-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 text-xs
                border-b border-travertine-300 dark:border-zinc-800/60">
        @if($isLive)
            {{-- LIVE — must scream in both themes; solid red bg with white text. --}}
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold !text-white animate-pulse
                         bg-red-600 dark:bg-red-500/80">
                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                LIVE
            </span>
        @endif

        {{-- Type label (Stream/Open) — color-swap via two spans toggled by .dark. --}}
        <span class="font-semibold uppercase tracking-wider text-[10px] shrink-0 dark:hidden"
              style="color: {{ $typeLabelColorLight }};">
            {{ $isStream ? 'Stream' : 'Open' }}
        </span>
        <span class="font-semibold uppercase tracking-wider text-[10px] shrink-0 hidden dark:inline"
              style="color: {{ $typeLabelColorDark }};">
            {{ $isStream ? 'Stream' : 'Open' }}
        </span>

        <span class="flex-1"></span>

        @if($isOpen && ! $isPast)
            {{-- Registration open — emerald chip, both themes simplified --}}
            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-medium shrink-0
                         bg-emerald-100 text-emerald-800 border border-emerald-300
                         dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/25">
                <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                <span class="hidden sm:inline">Registration open</span>
                <span class="sm:hidden">Reg open</span>
            </span>
        @elseif($isPast)
            <span class="font-mono text-[11px] shrink-0
                         text-travertine-500 dark:text-zinc-500">ended</span>
        @endif
    </div>

    {{-- Body: content left + countdown right --}}
    <div class="px-3 sm:px-4 py-3 sm:grid sm:grid-cols-[1fr_auto] sm:gap-4">
        <div class="min-w-0">
            <h3 class="font-semibold text-base sm:text-lg leading-tight mb-1.5
                       text-travertine-900 dark:text-white">
                {{ $event->name }}
            </h3>

            @if($event->description)
                <p class="text-xs sm:text-sm leading-relaxed mb-2.5 sm:mb-3
                          text-travertine-600 dark:text-zinc-400">
                    {{ $event->description }}
                </p>
            @endif

            {{-- Players grid — 2 cols if 2+ players, 1 col solo --}}
            @if($allPlayers->isNotEmpty())
                <div @class([
                        'mb-3',
                        'grid gap-x-3 gap-y-2' => true,
                        'grid-cols-2' => $playerCols === 2,
                        'grid-cols-1' => $playerCols === 1,
                    ])>
                    @foreach($allPlayers as $p)
                        @php $vars = $raceVar($p['race']); @endphp
                        @if($p['type'] === 'registered')
                            <a href="{{ route('players.show', ['id' => $p['id'], 'slug' => \Illuminate\Support\Str::slug($p['name'])]) }}"
                               wire:navigate
                               class="inline-flex items-stretch h-7 rounded-md overflow-hidden transition-colors min-w-0
                                      border border-travertine-300 hover:border-travertine-400
                                      dark:border-zinc-700/60 dark:hover:border-zinc-600">
                                {{-- Country flag --}}
                                <span class="block w-9 shrink-0 bg-cover bg-center"
                                      style="background-image: url('{{ asset('images/country_flags/' . strtolower($p['country_code']) . '.svg') }}');"
                                      aria-label="{{ $p['country_code'] }}"></span>
                                {{-- Race initial — race CSS vars auto theme-adjust --}}
                                <span class="flex items-center px-2 text-[10px] font-bold uppercase tracking-wider shrink-0"
                                      style="background: color-mix(in srgb, {{ $vars['base'] }} 15%, transparent);
                                             color: {{ $vars['soft'] }};">
                                    {{ strtoupper(substr($p['race'], 0, 1)) }}
                                </span>
                                {{-- Nick --}}
                                <span class="flex items-center px-3 font-semibold text-sm truncate
                                             bg-travertine-50 text-travertine-900
                                             dark:bg-zinc-800/50 dark:text-zinc-100">
                                    {{ $p['name'] }}
                                </span>
                            </a>
                        @else
                            {{-- Guest player — same shape but not a link --}}
                            <span class="inline-flex items-stretch h-7 rounded-md overflow-hidden min-w-0
                                         border border-travertine-300 dark:border-zinc-700/60">
                                <span class="block w-9 shrink-0 bg-cover bg-center"
                                      style="background-image: url('{{ asset('images/country_flags/' . strtolower($p['country_code']) . '.svg') }}');"></span>
                                <span class="flex items-center px-2 text-[10px] font-bold uppercase tracking-wider shrink-0"
                                      style="background: color-mix(in srgb, {{ $vars['base'] }} 15%, transparent);
                                             color: {{ $vars['soft'] }};">
                                    {{ strtoupper(substr($p['race'], 0, 1)) }}
                                </span>
                                <span class="flex items-center px-3 font-semibold text-sm truncate
                                             bg-travertine-50 text-travertine-900
                                             dark:bg-zinc-800/50 dark:text-zinc-100">
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
                    <span class="font-mono inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-md text-[11px] sm:text-xs
                                 bg-travertine-50 border border-travertine-300 text-travertine-800
                                 dark:bg-zinc-800/60 dark:border-zinc-700/60 dark:text-zinc-100">
                        <span class="text-travertine-500 dark:text-zinc-500">📍</span>
                        <span>{{ $event->location }}</span>
                    </span>
                </div>
            @endif

            {{-- External links — brand colors kept across themes (Twitch/YouTube etc.) --}}
            @if(count($event->parsedLinks()) > 0)
                <div class="flex flex-wrap gap-1.5">
                    @foreach($event->parsedLinks() as $link)
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-md text-[11px] sm:text-xs font-semibold transition-opacity hover:opacity-80"
                           style="background: {{ $link['color'] }}20; color: {{ $link['color'] }}; border: 1px solid {{ $link['color'] }}45;">
                            {{ $link['label'] ?: ucfirst($link['type']) }}
                            <span class="opacity-60">↗</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Countdown card — date prominently on top, digit blocks below.       --}}
        {{-- Two-block strategy (dark:hidden / hidden dark:block) lets us keep   --}}
        {{-- inline style with theme-specific rgba/hex values without indirection. --}}
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

                {{-- Light mode countdown --}}
                <div class="rounded-lg border p-2 sm:p-2.5 dark:hidden"
                     style="background: {{ $countdownBgLight }}; border-color: {{ $countdownBorderLight }};">

                    <div class="text-center pb-2 mb-2 border-b font-mono"
                         style="border-color: {{ $countdownBorderLight }};">
                        <div class="text-[15px] sm:text-[17px] font-bold tracking-wide"
                             style="color: {{ $countdownColorLight }};">
                            {{ $event->starts_at->format('d M') }}
                        </div>
                        <div class="text-[12px] sm:text-[13px] font-semibold mt-0.5"
                             style="color: {{ $countdownColorLight }}; opacity: 0.85;">
                            {{ $event->starts_at->format('H:i') }}
                            <span class="text-[10px] text-travertine-500 ml-0.5">CET</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 sm:flex gap-1">
                        @foreach([['d', 'D', 'Days'], ['h', 'H', 'Hours'], ['m', 'M', 'Min'], ['s', 'S', 'Sec']] as [$var, $short, $long])
                            <div class="text-center px-1.5 sm:px-2 py-1.5 rounded-md border min-w-0 sm:min-w-[46px]"
                                 style="background: {{ $countdownBgLight }}; border-color: {{ $countdownBorderLight }};">
                                <div class="font-mono text-base sm:text-xl font-bold leading-none tabular-nums"
                                     style="color: {{ $countdownColorLight }};"
                                     x-text="{{ $var === 'd' ? 'd' : "String({$var}).padStart(2,'0')" }}"></div>
                                <div class="text-[8px] sm:text-[9px] uppercase tracking-wider text-travertine-500 font-medium mt-0.5 sm:mt-1">
                                    <span class="hidden sm:inline">{{ $long }}</span>
                                    <span class="sm:hidden">{{ $short }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Dark mode countdown --}}
                <div class="rounded-lg border p-2 sm:p-2.5 hidden dark:block"
                     style="background: {{ $countdownBgDark }}; border-color: {{ $countdownBorderDark }};">

                    <div class="text-center pb-2 mb-2 border-b font-mono"
                         style="border-color: {{ $countdownBorderDark }};">
                        <div class="text-[15px] sm:text-[17px] font-bold tracking-wide"
                             style="color: {{ $countdownColorDark }};">
                            {{ $event->starts_at->format('d M') }}
                        </div>
                        <div class="text-[12px] sm:text-[13px] font-semibold mt-0.5"
                             style="color: {{ $countdownColorDark }}; opacity: 0.85;">
                            {{ $event->starts_at->format('H:i') }}
                            <span class="text-[10px] text-zinc-400 ml-0.5">CET</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 sm:flex gap-1">
                        @foreach([['d', 'D', 'Days'], ['h', 'H', 'Hours'], ['m', 'M', 'Min'], ['s', 'S', 'Sec']] as [$var, $short, $long])
                            <div class="text-center px-1.5 sm:px-2 py-1.5 rounded-md border min-w-0 sm:min-w-[46px]"
                                 style="background: {{ $countdownBgDark }}; border-color: {{ $countdownBorderDark }};">
                                <div class="font-mono text-base sm:text-xl font-bold leading-none tabular-nums"
                                     style="color: {{ $countdownColorDark }};"
                                     x-text="{{ $var === 'd' ? 'd' : "String({$var}).padStart(2,'0')" }}"></div>
                                <div class="text-[8px] sm:text-[9px] uppercase tracking-wider text-zinc-400 font-medium mt-0.5 sm:mt-1">
                                    <span class="hidden sm:inline">{{ $long }}</span>
                                    <span class="sm:hidden">{{ $short }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>