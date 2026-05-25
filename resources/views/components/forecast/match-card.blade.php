@props([
    'match',                       // ForecastMatch instance with predictions loaded
    'userPrediction' => null,      // ForecastPrediction|null — current user's pick
    'canManageGames' => false,     // bool — show admin actions
    'compact' => false,            // bool — dashboard variant (hides admin row + prediction badge)
])

@php
    // ─── Match-level state ───────────────────────────────────────────────
    $isLocked    = $match->isLocked();
    $isSettled   = $match->isSettled();
    $isForeigner = $match->match_type === 'foreigner';
    $isOpen      = ! $isSettled && ! $isLocked;

    // ─── Player resolution: foreigner uses FK, others use snapshot fields ───
    $nameA    = $isForeigner ? ($match->playerA?->name ?? '?') : ($match->player_a_name ?? '?');
    $nameB    = $isForeigner ? ($match->playerB?->name ?? '?') : ($match->player_b_name ?? '?');
    $raceA    = $match->player_a_race;
    $raceB    = $match->player_b_race;
    $countryA = $isForeigner ? ($match->playerA?->country_code ?? null) : ($match->match_type === 'korean' ? 'kr' : ($match->player_a_country ?? null));
    $countryB = $isForeigner ? ($match->playerB?->country_code ?? null) : ($match->match_type === 'korean' ? 'kr' : ($match->player_b_country ?? null));

    // ─── Race CSS variables — single source of truth from app.css.        ─
    // Vars auto-adjust per theme via :root:not(.dark) overrides.            ─
    // base = saturated hue (gradients, accent strips)                       ─
    // soft = lighter/darker variant (race label text)                       ─
    $raceVars = fn($race) => match($race) {
        'Terran'  => ['base' => 'var(--color-race-terran)',  'soft' => 'var(--color-race-terran-soft)'],
        'Zerg'    => ['base' => 'var(--color-race-zerg)',    'soft' => 'var(--color-race-zerg-soft)'],
        'Protoss' => ['base' => 'var(--color-race-protoss)', 'soft' => 'var(--color-race-protoss-soft)'],
        'Random'  => ['base' => 'var(--color-race-random)',  'soft' => 'var(--color-race-random-soft)'],
        default   => ['base' => 'var(--color-race-unknown)', 'soft' => 'var(--color-race-unknown-soft)'],
    };
    $varsA = $raceVars($raceA);
    $varsB = $raceVars($raceB);

    // ─── Odds — lower = favorite ─────────────────────────────────────────
    $oddsA = round((float) $match->odds_a * (float) $match->multiplier, 2);
    $oddsB = round((float) $match->odds_b * (float) $match->multiplier, 2);
    $favoriteSide = $oddsA < $oddsB ? 'a' : ($oddsB < $oddsA ? 'b' : null);
    // Odds color pairs — favorite green, underdog amber, tie neutral.       
    $aOddsColor = $favoriteSide === 'a'
        ? 'text-emerald-700 dark:text-emerald-400'
        : ($favoriteSide === 'b'
            ? 'text-amber-700 dark:text-amber-400'
            : 'text-travertine-700 dark:text-zinc-300');
    $bOddsColor = $favoriteSide === 'b'
        ? 'text-emerald-700 dark:text-emerald-400'
        : ($favoriteSide === 'a'
            ? 'text-amber-700 dark:text-amber-400'
            : 'text-travertine-700 dark:text-zinc-300');

    // ─── Settled winner resolution ───────────────────────────────────────
    $winnerName  = null;
    $winningSide = null;
    if ($isSettled) {
        if ($match->winner_id) {
            $winnerName  = $match->winner?->name;
            $winningSide = $match->winner_id === $match->player_a_id ? 'a' : 'b';
        } elseif ($match->winner_side === 'a') {
            $winnerName = $nameA; $winningSide = 'a';
        } elseif ($match->winner_side === 'b') {
            $winnerName = $nameB; $winningSide = 'b';
        }
    }

    // ─── User's own prediction resolution ────────────────────────────────
    $pickedName = null;
    $userPickedSide = null;
    if ($userPrediction) {
        if ($userPrediction->pick_player_id) {
            $pickedName     = $userPrediction->pickedPlayer?->name ?? $nameA;
            $userPickedSide = $userPrediction->pick_player_id === $match->player_a_id ? 'a' : 'b';
        } elseif ($userPrediction->pick_side === 'a') {
            $pickedName = $nameA; $userPickedSide = 'a';
        } elseif ($userPrediction->pick_side === 'b') {
            $pickedName = $nameB; $userPickedSide = 'b';
        }
    }

    // ─── Crowd split — already-loaded predictions, no extra query ────────
    $totalPicks = $match->predictions->count();
    $picksA = $match->predictions->filter(function ($p) use ($match, $isForeigner) {
        return $isForeigner ? $p->pick_player_id === $match->player_a_id : $p->pick_side === 'a';
    })->count();
    $crowdA     = $totalPicks > 0 ? round(($picksA / $totalPicks) * 100) : 50;
    $crowdB     = 100 - $crowdA;
    $crowdEmpty = $totalPicks === 0;

    // ─── Match-type label colors — light + dark variants.                 ─
    // Light: deeper, more saturated. Dark: bright pastel as before.        ─
    $typeLabelLight = match($match->match_type) {
        'foreigner' => '#047857',  // emerald-700
        'korean'    => '#b91c1c',  // red-700
        'national'  => '#1d4ed8',  // blue-700
        'clan'      => '#7e22ce',  // purple-700
        default     => '#574a31',  // travertine-700
    };
    $typeLabelDark = match($match->match_type) {
        'foreigner' => '#6ee7b7',
        'korean'    => '#fca5a5',
        'national'  => '#93c5fd',
        'clan'      => '#d8b4fe',
        default     => '#a1a1aa',
    };

    // ─── Per-side flags for which UI state each side is in ───────────────
    $aIsWinner  = $isSettled && $winningSide === 'a';
    $aIsLoser   = $isSettled && $winningSide === 'b';
    $aIsMine    = $userPickedSide === 'a';
    $bIsWinner  = $isSettled && $winningSide === 'b';
    $bIsLoser   = $isSettled && $winningSide === 'a';
    $bIsMine    = $userPickedSide === 'b';
    $aClickable = $isOpen && ! $compact && ! $userPrediction;
    $bClickable = $isOpen && ! $compact && ! $userPrediction;
@endphp

{{-- ═══════════════════════════════════════════════════════════════════════
     Card shell — sand surface in light (sits inside parchment widget       
     container), zinc-900 in dark.                                          
     ═══════════════════════════════════════════════════════════════════════ --}}
<div @class([
    'rounded-lg border overflow-hidden transition-colors',
    'border-travertine-300 bg-travertine-100/50 dark:border-zinc-800/60 dark:bg-zinc-900/30' => $isSettled,
    'border-travertine-300 bg-travertine-75 hover:border-travertine-400 dark:border-zinc-700/60 dark:bg-zinc-900/50 dark:hover:border-zinc-600/80' => ! $isSettled,
])>

    {{-- ─── Header strip: type label + event + countdown/lock indicator ─── --}}
    <div class="flex items-center gap-2 px-4 py-2.5 text-xs
                border-b border-travertine-300 dark:border-zinc-800/60">

        {{-- Type label — two-span color swap (inline style can't use dark:) --}}
        <span class="font-semibold uppercase tracking-wider text-[10px] shrink-0 dark:hidden"
              style="color: {{ $typeLabelLight }};">
            {{ ucfirst($match->match_type) }}
        </span>
        <span class="font-semibold uppercase tracking-wider text-[10px] shrink-0 hidden dark:inline"
              style="color: {{ $typeLabelDark }};">
            {{ ucfirst($match->match_type) }}
        </span>

        @if($match->event)
            <span class="text-travertine-400 dark:text-zinc-700">·</span>
            <span class="truncate min-w-0
                         text-travertine-600 dark:text-zinc-400">{{ $match->event->name }}</span>
        @endif

        @if($isOpen)
            {{-- Countdown — amber in both themes (urgency signal) --}}
            <div class="font-mono text-[11px] flex items-center gap-1.5 ml-auto shrink-0
                        text-amber-700 dark:text-amber-400"
                 x-data="{
                     target: {{ $match->scheduled_at->timestamp }},
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
                <span>⏱</span>
                <span class="tabular-nums"
                      x-text="(d > 0 ? d + 'd ' : '') + String(h).padStart(2,'0') + 'h ' + String(m).padStart(2,'0') + 'm ' + String(s).padStart(2,'0') + 's'"></span>
            </div>
        @elseif($isLocked)
            <span class="font-mono text-[11px] ml-auto shrink-0 flex items-center gap-1.5
                         text-travertine-600 dark:text-zinc-400">
                <span>🔒</span><span>picks locked</span>
            </span>
        @else
            <span class="font-mono text-[11px] ml-auto shrink-0
                         text-travertine-500 dark:text-zinc-500">
                {{ $match->scheduled_at->format('d M · H:i') }} CET
            </span>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         Pick row — Side A, VS divider, Side B
         Each side: flat flag (left for A / right for B), race-tinted bg
         gradient on the wrapper, name + race label + odds.
         Cyan = "my pick" — reserved color, no race or status uses it.
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-2 sm:grid sm:grid-cols-[1fr_auto_1fr] sm:items-stretch px-4 py-3">

        @php
            // ─── Side A wrapper classes ──────────────────────────────────
            // Shared base for both states + clickable types (button/div).
            $aWrapClass = 'group relative flex items-stretch rounded-lg border overflow-hidden text-left transition-all min-w-0';

            // ─── Side A semantic state classes ───────────────────────────
            // Light + dark pair for every state. Race tint takes over when
            // no semantic state owns the bg (see $aUsesRaceTint below).
            $aIsOpponent = $userPickedSide === 'b' && ! $isSettled;
            $aWrapState = match(true) {
                // Winner — emerald success state
                $aIsWinner   => 'bg-emerald-100 border-emerald-400 dark:bg-emerald-500/10 dark:border-emerald-500/40',
                // Loser — light: faded travertine; dark: faded zinc
                $aIsLoser    => 'bg-travertine-200/60 border-travertine-300 opacity-40 dark:bg-zinc-900/40 dark:border-zinc-800/60',
                // My pick — cyan reserved; same in both themes for instant recognition
                $aIsMine     => 'bg-cyan-100 border-cyan-500 ring-1 ring-inset ring-cyan-400 dark:bg-cyan-500/15 dark:border-cyan-500/60 dark:ring-cyan-500/30',
                // Opponent's side when user already picked — dimmed
                $aIsOpponent => 'border-travertine-300 opacity-50 dark:border-zinc-800/60',
                // Clickable open match — hover amber CTA hint
                $aClickable  => 'border-travertine-300 hover:border-amber-600/50 cursor-pointer dark:border-zinc-700/60 dark:hover:border-amber-500/50',
                // Default static state
                default      => 'border-travertine-300 dark:border-zinc-700/40',
            };

            // ─── Side A race tint background — only when no semantic state
            //     overrides. Theme base differs (travertine-75 vs zinc-800).
            //     We inject base via CSS var on .match-side-bg-a so a single
            //     gradient string works in both themes.
            $aUsesRaceTint = ! $aIsWinner && ! $aIsLoser && ! $aIsMine && ! $aIsOpponent;
            $aWrapStyle = $aUsesRaceTint
                ? 'background: linear-gradient(135deg, color-mix(in srgb, ' . $varsA['base'] . ' 22%, transparent) 0%, color-mix(in srgb, ' . $varsA['base'] . ' 6%, transparent) 60%, transparent 100%), var(--match-side-base);'
                : '';
        @endphp

        {{-- ── Side A wrapper (button when interactive, div when static) ── --}}
        @if($aClickable && auth()->check())
            <button wire:click="openBetModal({{ $match->id }}, 'a')"
                    class="{{ $aWrapClass }} {{ $aWrapState }} match-side"
                    style="{{ $aWrapStyle }}">
        @elseif($aClickable)
            <button type="button" x-data x-on:click.stop.prevent="$dispatch('open-guest-wallet-modal')"
                    class="{{ $aWrapClass }} {{ $aWrapState }} match-side"
                    style="{{ $aWrapStyle }}">
        @else
            <div class="{{ $aWrapClass }} {{ $aWrapState }} match-side"
                 style="{{ $aWrapStyle }}">
        @endif

            {{-- Flag block on the left --}}
            @if($countryA)
                <div class="w-14 sm:w-16 shrink-0 self-stretch overflow-hidden">
                    <img src="{{ asset('images/country_flags/' . strtolower($countryA) . '.svg') }}"
                         class="w-full h-full object-cover" alt="{{ $countryA }}">
                </div>
            @else
                <div class="w-14 sm:w-16 shrink-0 self-stretch
                            bg-travertine-200 dark:bg-zinc-800/60"></div>
            @endif

            {{-- Name + race + odds --}}
            <div class="flex-1 min-w-0 flex items-center gap-3 py-2.5 px-3">
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm truncate leading-tight
                                text-travertine-900 dark:text-zinc-100">
                        {{ $nameA }}
                        @if($aIsMine)
                            {{-- MY PICK badge — cyan reserved; force colors in both themes --}}
                            <span class="ml-1.5 inline-block px-1.5 py-px rounded text-[9px] font-bold tracking-wider align-middle"
                                  style="background: #06b6d4; color: #022c33;">MY PICK</span>
                        @endif
                        @if($aIsWinner)
                            <span class="text-[9px] font-mono ml-1
                                         text-emerald-700 dark:text-emerald-400">• WIN</span>
                        @endif
                    </div>
                    @if($raceA && $raceA !== 'Unknown')
                        {{-- Race label — uses soft var which auto theme-adjusts --}}
                        <div class="text-[10px] uppercase tracking-wider font-semibold leading-tight mt-0.5"
                             style="color: {{ $varsA['soft'] }};">
                            {{ $raceA }}
                        </div>
                    @endif
                </div>
                <div class="flex flex-col items-end shrink-0">
                    <span class="font-mono font-bold text-sm tabular-nums {{ $aOddsColor }}">
                        ×{{ number_format($oddsA, 2) }}
                    </span>
                    @if($aClickable)
                        <span class="text-[9px] font-semibold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity hidden sm:block
                                     text-amber-700/80 dark:text-amber-400/80">
                            Pick →
                        </span>
                    @endif
                </div>
            </div>

        @if($aClickable)
            </button>
        @else
            </div>
        @endif

        {{-- ── VS divider ── --}}
        <div class="flex items-center justify-center gap-2 px-1 sm:gap-0">
            <div class="flex-1 h-px sm:hidden
                        bg-travertine-300 dark:bg-zinc-700/50"></div>
            @if($isSettled)
                <span class="text-lg">🏆</span>
            @else
                <span class="text-[10px] font-black tracking-[0.2em]
                             text-travertine-400 dark:text-zinc-600">VS</span>
            @endif
            <div class="flex-1 h-px sm:hidden
                        bg-travertine-300 dark:bg-zinc-700/50"></div>
        </div>

        @php
            // ─── Side B wrapper — mirror of A ────────────────────────────
            $bIsOpponent = $userPickedSide === 'a' && ! $isSettled;
            $bWrapClass = 'group relative flex items-stretch rounded-lg border overflow-hidden text-left transition-all min-w-0';
            $bWrapState = match(true) {
                $bIsWinner   => 'bg-emerald-100 border-emerald-400 dark:bg-emerald-500/10 dark:border-emerald-500/40',
                $bIsLoser    => 'bg-travertine-200/60 border-travertine-300 opacity-40 dark:bg-zinc-900/40 dark:border-zinc-800/60',
                $bIsMine     => 'bg-cyan-100 border-cyan-500 ring-1 ring-inset ring-cyan-400 dark:bg-cyan-500/15 dark:border-cyan-500/60 dark:ring-cyan-500/30',
                $bIsOpponent => 'border-travertine-300 opacity-50 dark:border-zinc-800/60',
                $bClickable  => 'border-travertine-300 hover:border-amber-600/50 cursor-pointer dark:border-zinc-700/60 dark:hover:border-amber-500/50',
                default      => 'border-travertine-300 dark:border-zinc-700/40',
            };
            // Gradient flipped (225deg) so race tint fades from the right flag.
            $bUsesRaceTint = ! $bIsWinner && ! $bIsLoser && ! $bIsMine && ! $bIsOpponent;
            $bWrapStyle = $bUsesRaceTint
                ? 'background: linear-gradient(225deg, color-mix(in srgb, ' . $varsB['base'] . ' 22%, transparent) 0%, color-mix(in srgb, ' . $varsB['base'] . ' 6%, transparent) 60%, transparent 100%), var(--match-side-base);'
                : '';
        @endphp

        {{-- ── Side B wrapper ── --}}
        @if($bClickable && auth()->check())
            <button wire:click="openBetModal({{ $match->id }}, 'b')"
                    class="{{ $bWrapClass }} {{ $bWrapState }} match-side"
                    style="{{ $bWrapStyle }}">
        @elseif($bClickable)
            <button type="button" x-data x-on:click.stop.prevent="$dispatch('open-guest-wallet-modal')"
                    class="{{ $bWrapClass }} {{ $bWrapState }} match-side"
                    style="{{ $bWrapStyle }}">
        @else
            <div class="{{ $bWrapClass }} {{ $bWrapState }} match-side"
                 style="{{ $bWrapStyle }}">
        @endif

            {{-- Name + race + odds (mirrored — text right-aligned, odds on the left) --}}
            <div class="flex-1 min-w-0 flex items-center gap-3 py-2.5 px-3 flex-row-reverse">
                <div class="flex-1 min-w-0 text-right">
                    <div class="font-bold text-sm truncate leading-tight
                                text-travertine-900 dark:text-zinc-100">
                        @if($bIsWinner)
                            <span class="text-[9px] font-mono mr-1
                                         text-emerald-700 dark:text-emerald-400">WIN •</span>
                        @endif
                        @if($bIsMine)
                            <span class="mr-1.5 inline-block px-1.5 py-px rounded text-[9px] font-bold tracking-wider align-middle"
                                  style="background: #06b6d4; color: #022c33;">MY PICK</span>
                        @endif
                        {{ $nameB }}
                    </div>
                    @if($raceB && $raceB !== 'Unknown')
                        <div class="text-[10px] uppercase tracking-wider font-semibold leading-tight mt-0.5"
                             style="color: {{ $varsB['soft'] }};">
                            {{ $raceB }}
                        </div>
                    @endif
                </div>
                <div class="flex flex-col items-start shrink-0">
                    <span class="font-mono font-bold text-sm tabular-nums {{ $bOddsColor }}">
                        ×{{ number_format($oddsB, 2) }}
                    </span>
                    @if($bClickable)
                        <span class="text-[9px] font-semibold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity hidden sm:block
                                     text-amber-700/80 dark:text-amber-400/80">
                            ← Pick
                        </span>
                    @endif
                </div>
            </div>

            {{-- Flag block on the right --}}
            @if($countryB)
                <div class="w-14 sm:w-16 shrink-0 self-stretch overflow-hidden">
                    <img src="{{ asset('images/country_flags/' . strtolower($countryB) . '.svg') }}"
                         class="w-full h-full object-cover" alt="{{ $countryB }}">
                </div>
            @else
                <div class="w-14 sm:w-16 shrink-0 self-stretch
                            bg-travertine-200 dark:bg-zinc-800/60"></div>
            @endif

        @if($bClickable)
            </button>
        @else
            </div>
        @endif

    </div>

    {{-- ─── Theme-aware base color for race gradient (consumed by .match-side) ─
         Sand in light, near-black in dark. Defined once per card to avoid
         leaking into siblings. Used in inline gradient via var(). ─────────── --}}
    <style>
        .match-side { --match-side-base: rgba(244,236,216,0.60); }
        .dark .match-side { --match-side-base: rgba(39,39,42,0.40); }
    </style>

    {{-- ═══════════════════════════════════════════════════════════════════
         Crowd split bar — community sentiment.                              
         A=blue / B=red is a near-universal sports/gambling convention      
         (Polymarket, Kalshi). Colors stay constant in both themes.         
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="px-4 pb-3">
        <div class="flex items-center gap-2">
            <span @class([
                'font-mono font-semibold text-[11px] tabular-nums min-w-[2.25rem]',
                'text-travertine-400 dark:text-zinc-700' => $crowdEmpty,
                'text-blue-700 dark:text-blue-400' => ! $crowdEmpty,
            ])>
                {{ $crowdEmpty ? '—' : $crowdA . '%' }}
            </span>

            <div class="flex-1 h-1.5 rounded-full overflow-hidden flex
                        bg-travertine-300 dark:bg-zinc-800">
                @if($crowdEmpty)
                    {{-- Hatched pattern — works in both themes since stripes are dark-on-dark or dark-on-light --}}
                    <div class="w-full h-full opacity-40
                                bg-[repeating-linear-gradient(45deg,#a89a74,#a89a74_4px,#d4cab0_4px,#d4cab0_8px)]
                                dark:bg-[repeating-linear-gradient(45deg,#3f3f46,#3f3f46_4px,#27272a_4px,#27272a_8px)]"></div>
                @else
                    {{-- A=blue side (or green if A won, gray if A lost) --}}
                    <div class="h-full transition-all duration-500"
                        style="width: {{ $crowdA }}%;
                               background: {{ $isSettled && $winningSide === 'a' ? '#10b981' : ($isSettled ? '#9ca3af' : '#3b82f6') }};"></div>
                    {{-- B=red side (or green if B won, gray if B lost) --}}
                    <div class="h-full transition-all duration-500"
                        style="width: {{ $crowdB }}%;
                               background: {{ $isSettled && $winningSide === 'b' ? '#10b981' : ($isSettled ? '#9ca3af' : '#ef4444') }};"></div>
                @endif
            </div>

            <span @class([
                'font-mono font-semibold text-[11px] tabular-nums min-w-[2.25rem] text-right',
                'text-travertine-400 dark:text-zinc-700' => $crowdEmpty,
                'text-red-700 dark:text-red-400' => ! $crowdEmpty,
            ])>
                {{ $crowdEmpty ? '—' : $crowdB . '%' }}
            </span>
        </div>

        <div class="text-center text-[10px] uppercase tracking-wider mt-1
                    text-travertine-500 dark:text-zinc-600">
            @if($crowdEmpty)
                Be the first to pick
            @else
                {{ $totalPicks }} {{ Str::plural('pick', $totalPicks) }}
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         User's own prediction badge (skipped in compact/dashboard mode).   
         Four states: won / lost / refunded / pending — each with its own  
         theme-paired surface.                                               
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $compact && $userPrediction && $pickedName)
        @php
            $predStyle = match($userPrediction->result) {
                'won'      => 'bg-emerald-100 border-emerald-300 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-300',
                'lost'     => 'bg-red-100 border-red-300 text-red-800 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300',
                'refunded' => 'bg-travertine-200 border-travertine-300 text-travertine-700 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-400',
                default    => 'bg-amber-50 border-amber-300 text-amber-800 dark:bg-amber-500/5 dark:border-amber-500/20 dark:text-amber-200',
            };
            $predIcon = match($userPrediction->result) {
                'won'      => '✓',
                'lost'     => '✗',
                'refunded' => '↩',
                default    => '⏳',
            };
        @endphp
        <div class="mx-4 mb-3 rounded-lg px-3 py-2 text-xs border flex items-center gap-2 flex-wrap {{ $predStyle }}">
            <span class="font-bold">{{ $predIcon }}</span>
            <span class="min-w-0 truncate">
                Your pick: <strong>{{ $pickedName }}</strong>
                · {{ number_format($userPrediction->stake, 2) }} pts
                · ×{{ number_format($userPrediction->odds_at_time, 2) }}
                @if($userPrediction->bonus_multiplier > 1)
                    <span class="opacity-70">(×{{ number_format($userPrediction->bonus_multiplier, 2) }} perk)</span>
                @endif
            </span>
            @if($userPrediction->result === 'won')
                <span class="ml-auto font-bold font-mono shrink-0">+{{ number_format($userPrediction->actual_payout, 2) }} pts</span>
            @elseif($userPrediction->result === 'pending')
                <span class="ml-auto font-mono shrink-0 opacity-70">→ {{ number_format($userPrediction->potential_payout, 2) }} pts</span>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         Footer — full schedule details
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $compact)
        <div class="px-4 py-2 border-t flex items-center justify-between font-mono text-[11px]
                    border-travertine-300 text-travertine-500
                    dark:border-zinc-800/60 dark:text-zinc-500">
            @if($isOpen)
                <span class="flex items-center gap-1.5">
                    <span class="text-travertine-400 dark:text-zinc-600">🔒</span>
                    <span>Locks</span>
                    <span class="text-travertine-700 dark:text-zinc-300">{{ $match->locked_at->format('d M, H:i') }}</span>
                    <span class="text-travertine-400 dark:text-zinc-600">CET</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="text-travertine-400 dark:text-zinc-600">🗓</span>
                    <span>Match</span>
                    <span class="text-travertine-700 dark:text-zinc-300">{{ $match->scheduled_at->format('d M, H:i') }}</span>
                    <span class="text-travertine-400 dark:text-zinc-600">CET</span>
                </span>
            @elseif($isLocked)
                <span class="flex items-center gap-1.5">
                    <span class="text-travertine-400 dark:text-zinc-600">🔒</span>
                    <span>Locked</span>
                    <span class="text-travertine-700 dark:text-zinc-300">{{ $match->locked_at->format('d M, H:i') }}</span>
                    <span class="text-travertine-400 dark:text-zinc-600">CET</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="text-travertine-400 dark:text-zinc-600">🗓</span>
                    <span>Match</span>
                    <span class="text-travertine-700 dark:text-zinc-300">{{ $match->scheduled_at->format('d M, H:i') }}</span>
                    <span class="text-travertine-400 dark:text-zinc-600">CET</span>
                </span>
            @else
                <span class="flex items-center gap-1.5">
                    <span class="text-travertine-400 dark:text-zinc-600">🏆</span>
                    @if($winnerName)
                        <span>Winner</span>
                        <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ $winnerName }}</span>
                    @else
                        <span class="text-travertine-600 dark:text-zinc-400">Settled</span>
                    @endif
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="text-travertine-400 dark:text-zinc-600">🗓</span>
                    <span class="text-travertine-700 dark:text-zinc-300">{{ $match->scheduled_at->format('d M, H:i') }}</span>
                    <span class="text-travertine-400 dark:text-zinc-600">CET</span>
                </span>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         Admin row — settle / edit / delete
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $compact && $canManageGames)
        <div class="px-4 py-2 border-t flex items-center gap-3 text-xs
                    border-travertine-300 dark:border-zinc-800/60">
            @if($isOpen || $isLocked)
                <button wire:click="openSettleModal({{ $match->id }})"
                    class="transition-colors
                           text-emerald-700 hover:text-emerald-800
                           dark:text-emerald-400 dark:hover:text-emerald-300">Settle</button>
                <span class="text-travertine-400 dark:text-zinc-700">·</span>
            @endif
            <button wire:click="openEditMatchModal({{ $match->id }})"
                class="transition-colors
                       text-travertine-600 hover:text-oxblood
                       dark:text-zinc-400 dark:hover:text-zinc-200">Edit</button>
            <span class="text-travertine-400 dark:text-zinc-700">·</span>
            <button wire:click="$set('confirmingDeleteId', {{ $match->id }})"
                class="transition-colors
                       text-travertine-500 hover:text-red-700
                       dark:text-zinc-500 dark:hover:text-red-400">Delete</button>
            @if($isSettled && $match->settled_at)
                <span class="ml-auto font-mono text-[10px]
                             text-travertine-500 dark:text-zinc-600">
                    settled {{ $match->settled_at->diffForHumans() }}
                </span>
            @endif
        </div>
    @endif
</div>