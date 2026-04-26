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

    // ─── Race colors — pulled from app.css @theme. Inline-style only,
    //     because Tailwind JIT can't see dynamic class names like bg-race-{race}.
    $raceHex = match($raceA) {
        'Terran'  => '#3b82f6',
        'Zerg'    => '#f43f5e',
        'Protoss' => '#d4af37',
        'Random'  => '#f97316',
        default   => '#71717a',
    };
    $raceHexB = match($raceB) {
        'Terran'  => '#3b82f6',
        'Zerg'    => '#f43f5e',
        'Protoss' => '#d4af37',
        'Random'  => '#f97316',
        default   => '#71717a',
    };
    $raceTextA = match($raceA) {
        'Terran'  => '#60a5fa',
        'Zerg'    => '#fb7185',
        'Protoss' => '#e8c66b',
        'Random'  => '#fb923c',
        default   => '#a1a1aa',
    };
    $raceTextB = match($raceB) {
        'Terran'  => '#60a5fa',
        'Zerg'    => '#fb7185',
        'Protoss' => '#e8c66b',
        'Random'  => '#fb923c',
        default   => '#a1a1aa',
    };

    // ─── Odds — lower = favorite ─────────────────────────────────────────
    $oddsA = round((float) $match->odds_a * (float) $match->multiplier, 2);
    $oddsB = round((float) $match->odds_b * (float) $match->multiplier, 2);
    $favoriteSide = $oddsA < $oddsB ? 'a' : ($oddsB < $oddsA ? 'b' : null);
    $aOddsColor = $favoriteSide === 'a' ? 'text-emerald-400' : ($favoriteSide === 'b' ? 'text-amber-400' : 'text-zinc-300');
    $bOddsColor = $favoriteSide === 'b' ? 'text-emerald-400' : ($favoriteSide === 'a' ? 'text-amber-400' : 'text-zinc-300');

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

    // ─── Match-type label color (replaces the old pill badge) ────────────
    $typeLabelColor = match($match->match_type) {
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
     Card shell
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border overflow-hidden transition-colors
    {{ $isSettled ? 'border-zinc-800/60 bg-zinc-900/30' : 'border-zinc-700/60 bg-zinc-900/50 hover:border-zinc-600/80' }}">

    {{-- ─── Header strip: type label + event + countdown/lock indicator ─── --}}
    <div class="flex items-center gap-2 px-4 py-2.5 border-b border-zinc-800/60 text-xs">
        <span class="font-semibold uppercase tracking-wider text-[10px] shrink-0"
              style="color: {{ $typeLabelColor }};">
            {{ ucfirst($match->match_type) }}
        </span>

        @if($match->event)
            <span class="text-zinc-700">·</span>
            <span class="text-zinc-400 truncate min-w-0">{{ $match->event->name }}</span>
        @endif

        @if($isOpen)
            <div class="font-mono text-[11px] flex items-center gap-1.5 ml-auto shrink-0 text-amber-400"
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
            <span class="font-mono text-[11px] text-zinc-400 ml-auto shrink-0 flex items-center gap-1.5">
                <span>🔒</span><span>picks locked</span>
            </span>
        @else
            <span class="font-mono text-[11px] text-zinc-500 ml-auto shrink-0">
                {{ $match->scheduled_at->utc()->format('d M · H:i') }} CET
            </span>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         Pick row — Side A, VS divider, Side B
         Each side: flat flag (left for A / right for B), race-tinted bg
         gradient on the wrapper, name + race label + odds.
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-2 sm:grid sm:grid-cols-[1fr_auto_1fr] sm:items-stretch px-4 py-3">

        @php
            // Side A wrapper: shared classes + state-dependent style.
            // Race tint shows only when no semantic state owns the bg.
            // Cyan is reserved for "my pick" — no race or status uses it.
            $aIsOpponent = $userPickedSide === 'b' && ! $isSettled;
            $aWrapClass = 'group relative flex items-stretch rounded-lg border overflow-hidden text-left transition-all min-w-0';
            $aWrapState = match(true) {
                $aIsWinner   => 'bg-emerald-500/10 border-emerald-500/40',
                $aIsLoser    => 'bg-zinc-900/40 border-zinc-800/60 opacity-40',
                $aIsMine     => 'bg-cyan-500/15 border-cyan-500/60 ring-1 ring-inset ring-cyan-500/30',
                $aIsOpponent => 'border-zinc-800/60 opacity-50',
                $aClickable  => 'border-zinc-700/60 hover:border-amber-500/50 cursor-pointer',
                default      => 'border-zinc-700/40',
            };
            // Race tint suppressed for any semantic state including opponent dim.
            $aUsesRaceTint = ! $aIsWinner && ! $aIsLoser && ! $aIsMine && ! $aIsOpponent;
            $aWrapStyle = $aUsesRaceTint
                ? 'background: linear-gradient(135deg, color-mix(in srgb, ' . $raceHex . ' 22%, transparent) 0%, color-mix(in srgb, ' . $raceHex . ' 6%, transparent) 60%, transparent 100%), rgba(39,39,42,0.40);'
                : '';
        @endphp

        {{-- ── Side A wrapper (button when interactive, div when static) ── --}}
        @if($aClickable && auth()->check())
            <button wire:click="openBetModal({{ $match->id }}, 'a')" class="{{ $aWrapClass }} {{ $aWrapState }}" style="{{ $aWrapStyle }}">
        @elseif($aClickable)
            <button type="button" x-data x-on:click.stop.prevent="$dispatch('open-guest-wallet-modal')" class="{{ $aWrapClass }} {{ $aWrapState }}" style="{{ $aWrapStyle }}">
        @else
            <div class="{{ $aWrapClass }} {{ $aWrapState }}" style="{{ $aWrapStyle }}">
        @endif

            {{-- Flag block on the left --}}
            @if($countryA)
                <div class="w-14 sm:w-16 shrink-0 self-stretch overflow-hidden">
                    <img src="{{ asset('images/country_flags/' . strtolower($countryA) . '.svg') }}"
                         class="w-full h-full object-cover" alt="{{ $countryA }}">
                </div>
            @else
                <div class="w-14 sm:w-16 shrink-0 self-stretch bg-zinc-800/60"></div>
            @endif

            {{-- Name + race + odds --}}
            <div class="flex-1 min-w-0 flex items-center gap-3 py-2.5 px-3">
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm text-zinc-100 truncate leading-tight">
                        {{ $nameA }}
                        @if($aIsMine)
                            <span class="ml-1.5 inline-block px-1.5 py-px rounded text-[9px] font-bold tracking-wider align-middle"
                                  style="background: #06b6d4; color: #022c33;">MY PICK</span>
                        @endif
                        @if($aIsWinner)
                            <span class="text-[9px] font-mono text-emerald-400 ml-1">• WIN</span>
                        @endif
                    </div>
                    @if($raceA && $raceA !== 'Unknown')
                        <div class="text-[10px] uppercase tracking-wider font-semibold leading-tight mt-0.5"
                             style="color: {{ $raceTextA }};">
                            {{ $raceA }}
                        </div>
                    @endif
                </div>
                <div class="flex flex-col items-end shrink-0">
                    <span class="font-mono font-bold text-sm {{ $aOddsColor }} tabular-nums">
                        ×{{ number_format($oddsA, 2) }}
                    </span>
                    @if($aClickable)
                        <span class="text-[9px] text-amber-400/80 font-semibold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity hidden sm:block">
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
            <div class="flex-1 h-px bg-zinc-700/50 sm:hidden"></div>
            @if($isSettled)
                <span class="text-lg">🏆</span>
            @else
                <span class="text-[10px] font-black text-zinc-600 tracking-[0.2em]">VS</span>
            @endif
            <div class="flex-1 h-px bg-zinc-700/50 sm:hidden"></div>
        </div>

        @php
            // Side B wrapper — mirror of A. Gradient direction flipped (225deg)
            // so the race tint fades from the side B flag (right) instead.
            $bIsOpponent = $userPickedSide === 'a' && ! $isSettled;
            $bWrapClass = 'group relative flex items-stretch rounded-lg border overflow-hidden text-left transition-all min-w-0';
            $bWrapState = match(true) {
                $bIsWinner   => 'bg-emerald-500/10 border-emerald-500/40',
                $bIsLoser    => 'bg-zinc-900/40 border-zinc-800/60 opacity-40',
                $bIsMine     => 'bg-cyan-500/15 border-cyan-500/60 ring-1 ring-inset ring-cyan-500/30',
                $bIsOpponent => 'border-zinc-800/60 opacity-50',
                $bClickable  => 'border-zinc-700/60 hover:border-amber-500/50 cursor-pointer',
                default      => 'border-zinc-700/40',
            };
            $bUsesRaceTint = ! $bIsWinner && ! $bIsLoser && ! $bIsMine && ! $bIsOpponent;
            $bWrapStyle = $bUsesRaceTint
                ? 'background: linear-gradient(225deg, color-mix(in srgb, ' . $raceHexB . ' 22%, transparent) 0%, color-mix(in srgb, ' . $raceHexB . ' 6%, transparent) 60%, transparent 100%), rgba(39,39,42,0.40);'
                : '';
        @endphp

        {{-- ── Side B wrapper ── --}}
        @if($bClickable && auth()->check())
            <button wire:click="openBetModal({{ $match->id }}, 'b')" class="{{ $bWrapClass }} {{ $bWrapState }}" style="{{ $bWrapStyle }}">
        @elseif($bClickable)
            <button type="button" x-data x-on:click.stop.prevent="$dispatch('open-guest-wallet-modal')" class="{{ $bWrapClass }} {{ $bWrapState }}" style="{{ $bWrapStyle }}">
        @else
            <div class="{{ $bWrapClass }} {{ $bWrapState }}" style="{{ $bWrapStyle }}">
        @endif

            {{-- Name + race + odds (mirrored — text right-aligned, odds on the left) --}}
            <div class="flex-1 min-w-0 flex items-center gap-3 py-2.5 px-3 flex-row-reverse">
                <div class="flex-1 min-w-0 text-right">
                    <div class="font-bold text-sm text-zinc-100 truncate leading-tight">
                        @if($bIsWinner)
                            <span class="text-[9px] font-mono text-emerald-400 mr-1">WIN •</span>
                        @endif
                        @if($bIsMine)
                            <span class="mr-1.5 inline-block px-1.5 py-px rounded text-[9px] font-bold tracking-wider align-middle"
                                  style="background: #06b6d4; color: #022c33;">MY PICK</span>
                        @endif
                        {{ $nameB }}
                    </div>
                    @if($raceB && $raceB !== 'Unknown')
                        <div class="text-[10px] uppercase tracking-wider font-semibold leading-tight mt-0.5"
                             style="color: {{ $raceTextB }};">
                            {{ $raceB }}
                        </div>
                    @endif
                </div>
                <div class="flex flex-col items-start shrink-0">
                    <span class="font-mono font-bold text-sm {{ $bOddsColor }} tabular-nums">
                        ×{{ number_format($oddsB, 2) }}
                    </span>
                    @if($bClickable)
                        <span class="text-[9px] text-amber-400/80 font-semibold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity hidden sm:block">
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
                <div class="w-14 sm:w-16 shrink-0 self-stretch bg-zinc-800/60"></div>
            @endif

        @if($bClickable)
            </button>
        @else
            </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         Crowd split bar — community sentiment
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="px-4 pb-3">
        <div class="flex items-center gap-2">
            <span class="font-mono font-semibold text-[11px] tabular-nums min-w-[2.25rem]
                {{ $crowdEmpty ? 'text-zinc-700' : 'text-blue-400' }}">
                {{ $crowdEmpty ? '—' : $crowdA . '%' }}
            </span>

            <div class="flex-1 h-1.5 rounded-full overflow-hidden bg-zinc-800 flex">
                @if($crowdEmpty)
                    <div class="w-full h-full bg-[repeating-linear-gradient(45deg,#3f3f46,#3f3f46_4px,#27272a_4px,#27272a_8px)] opacity-50"></div>
                @else
                    <div class="h-full transition-all duration-500"
                        style="width: {{ $crowdA }}%; background: {{ $isSettled && $winningSide === 'a' ? '#10b981' : ($isSettled ? '#3f3f46' : '#3b82f6') }};"></div>
                    <div class="h-full transition-all duration-500"
                        style="width: {{ $crowdB }}%; background: {{ $isSettled && $winningSide === 'b' ? '#10b981' : ($isSettled ? '#3f3f46' : '#ef4444') }};"></div>
                @endif
            </div>

            <span class="font-mono font-semibold text-[11px] tabular-nums min-w-[2.25rem] text-right
                {{ $crowdEmpty ? 'text-zinc-700' : 'text-red-400' }}">
                {{ $crowdEmpty ? '—' : $crowdB . '%' }}
            </span>
        </div>

        <div class="text-center text-[10px] text-zinc-600 uppercase tracking-wider mt-1">
            @if($crowdEmpty)
                Be the first to pick
            @else
                {{ $totalPicks }} {{ Str::plural('pick', $totalPicks) }}
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         User's own prediction badge (skipped in compact/dashboard mode)
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $compact && $userPrediction && $pickedName)
        @php
            $predStyle = match($userPrediction->result) {
                'won'      => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300',
                'lost'     => 'bg-red-500/10 border-red-500/30 text-red-300',
                'refunded' => 'bg-zinc-800 border-zinc-700 text-zinc-400',
                default    => 'bg-amber-500/5 border-amber-500/20 text-amber-200',
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
                · {{ number_format($userPrediction->stake, 0) }} pts
                · ×{{ number_format($userPrediction->odds_at_time, 2) }}
                @if($userPrediction->bonus_multiplier > 1)
                    <span class="opacity-70">(×{{ number_format($userPrediction->bonus_multiplier, 2) }} perk)</span>
                @endif
            </span>
            @if($userPrediction->result === 'won')
                <span class="ml-auto font-bold font-mono shrink-0">+{{ number_format($userPrediction->actual_payout, 0) }} pts</span>
            @elseif($userPrediction->result === 'pending')
                <span class="ml-auto font-mono shrink-0 opacity-70">→ {{ number_format($userPrediction->potential_payout, 0) }} pts</span>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         Footer — full schedule details
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $compact)
        <div class="px-4 py-2 border-t border-zinc-800/60 flex items-center justify-between
                    font-mono text-[11px] text-zinc-500">
            @if($isOpen)
                <span class="flex items-center gap-1.5">
                    <span class="text-zinc-600">🔒</span>
                    <span>Locks</span>
                    <span class="text-zinc-300">{{ $match->locked_at->utc()->format('d M, H:i') }}</span>
                    <span class="text-zinc-600">CET</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="text-zinc-600">🗓</span>
                    <span>Match</span>
                    <span class="text-zinc-300">{{ $match->scheduled_at->utc()->format('d M, H:i') }}</span>
                    <span class="text-zinc-600">CET</span>
                </span>
            @elseif($isLocked)
                <span class="flex items-center gap-1.5">
                    <span class="text-zinc-600">🔒</span>
                    <span>Locked</span>
                    <span class="text-zinc-300">{{ $match->locked_at->utc()->format('d M, H:i') }}</span>
                    <span class="text-zinc-600">CET</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="text-zinc-600">🗓</span>
                    <span>Match</span>
                    <span class="text-zinc-300">{{ $match->scheduled_at->utc()->format('d M, H:i') }}</span>
                    <span class="text-zinc-600">CET</span>
                </span>
            @else
                <span class="flex items-center gap-1.5">
                    <span class="text-zinc-600">🏆</span>
                    @if($winnerName)
                        <span>Winner</span>
                        <span class="text-emerald-400 font-semibold">{{ $winnerName }}</span>
                    @else
                        <span class="text-zinc-400">Settled</span>
                    @endif
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="text-zinc-600">🗓</span>
                    <span class="text-zinc-300">{{ $match->scheduled_at->utc()->format('d M, H:i') }}</span>
                    <span class="text-zinc-600">CET</span>
                </span>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         Admin row — settle / edit / delete
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $compact && $canManageGames)
        <div class="px-4 py-2 border-t border-zinc-800/60 flex items-center gap-3 text-xs">
            @if($isOpen || $isLocked)
                <button wire:click="openSettleModal({{ $match->id }})"
                    class="text-emerald-400 hover:text-emerald-300 transition-colors">Settle</button>
                <span class="text-zinc-700">·</span>
            @endif
            <button wire:click="openEditMatchModal({{ $match->id }})"
                class="text-zinc-400 hover:text-zinc-200 transition-colors">Edit</button>
            <span class="text-zinc-700">·</span>
            <button wire:click="$set('confirmingDeleteId', {{ $match->id }})"
                class="text-zinc-500 hover:text-red-400 transition-colors">Delete</button>
            @if($isSettled && $match->settled_at)
                <span class="ml-auto text-zinc-600 font-mono text-[10px]">
                    settled {{ $match->settled_at->diffForHumans() }}
                </span>
            @endif
        </div>
    @endif
</div>