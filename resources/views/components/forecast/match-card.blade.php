@props([
    'match',                       // ForecastMatch instance with predictions loaded
    'userPrediction' => null,      // ForecastPrediction|null — current user's pick
    'canManageGames' => false,     // bool — show admin actions
    'compact' => false,            // bool — dashboard-variant (hides admin row + prediction badge)
])

@php
    // ─── Derive everything for this one card ─────────────────────────────
    $isLocked    = $match->isLocked();
    $isSettled   = $match->isSettled();
    $isForeigner = $match->match_type === 'foreigner';
    $isOpen      = ! $isSettled && ! $isLocked;

    $nameA = $isForeigner ? ($match->playerA?->name ?? '?') : ($match->player_a_name ?? '?');
    $nameB = $isForeigner ? ($match->playerB?->name ?? '?') : ($match->player_b_name ?? '?');

    $raceA = $match->player_a_race;
    $raceB = $match->player_b_race;

    $countryA = $isForeigner
        ? ($match->playerA?->country_code ?? null)
        : ($match->match_type === 'korean' ? 'kr' : ($match->player_a_country ?? null));
    $countryB = $isForeigner
        ? ($match->playerB?->country_code ?? null)
        : ($match->match_type === 'korean' ? 'kr' : ($match->player_b_country ?? null));

    // Race hex — inline-style only, Tailwind JIT can't see dynamic classes.
    $raceHex = fn($race) => match($race) {
        'Terran'  => '#3b82f6',
        'Zerg'    => '#a855f7',
        'Protoss' => '#eab308',
        'Random'  => '#f97316',
        default   => '#71717a',
    };

    // Odds — lower odds = favorite. Equal odds = no favorite.
    $oddsA = round((float) $match->odds_a * (float) $match->multiplier, 2);
    $oddsB = round((float) $match->odds_b * (float) $match->multiplier, 2);
    $favoriteSide = $oddsA < $oddsB ? 'a' : ($oddsB < $oddsA ? 'b' : null);

    // Winner for settled matches — local variable name ≠ Livewire property name.
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

    // Resolve picked side + name for the user's prediction
    $pickedName = null;
    $userPickedSide = null;
    if ($userPrediction) {
        if ($userPrediction->pick_player_id) {
            $pickedName     = $userPrediction->pickedPlayer?->name ?? $nameA;
            $userPickedSide = $userPrediction->pick_player_id === $match->player_a_id ? 'a' : 'b';
        } elseif ($userPrediction->pick_side === 'a') {
            $pickedName     = $nameA;
            $userPickedSide = 'a';
        } elseif ($userPrediction->pick_side === 'b') {
            $pickedName     = $nameB;
            $userPickedSide = 'b';
        }
    }

    // Crowd split — from predictions already loaded, no extra query.
    $totalPicks = $match->predictions->count();
    $picksA = $match->predictions->filter(function ($p) use ($match, $isForeigner) {
        if ($isForeigner) {
            return $p->pick_player_id === $match->player_a_id;
        }
        return $p->pick_side === 'a';
    })->count();
    $picksB     = $totalPicks - $picksA;
    $crowdA     = $totalPicks > 0 ? round(($picksA / $totalPicks) * 100) : 50;
    $crowdB     = 100 - $crowdA;
    $crowdEmpty = $totalPicks === 0;

    // Match-type badge colors
    $typeBadge = match($match->match_type) {
        'foreigner' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'korean'    => 'bg-red-500/10 text-red-400 border-red-500/20',
        'national'  => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        default     => 'bg-zinc-800 text-zinc-400 border-zinc-700',
    };
@endphp

{{-- ═══════════════════════════════════════════════════════════════════════
     Card shell
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border overflow-hidden transition-colors
    {{ $isSettled ? 'border-zinc-800/60 bg-zinc-900/30' : 'border-zinc-700/60 bg-zinc-900/50 hover:border-zinc-600/80' }}">

{{-- Meta strip: type · event · schedule + lock timer --}}
<div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-2 px-4 pt-3 pb-2">

    {{-- Top row: type badge + event name --}}
    <div class="flex items-center gap-2 text-xs">
        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider border {{ $typeBadge }}">
            {{ ucfirst($match->match_type) }}
        </span>

        @if($match->event)
            <span class="text-zinc-500 truncate min-w-0">{{ $match->event->name }}</span>
        @endif
    </div>

    {{-- Bottom row: timer — full width, wraps naturally --}}
    @if($isOpen)
        <div class="font-mono text-xs flex flex-wrap items-center gap-x-1.5 gap-y-1 text-amber-400 sm:ml-auto"
             x-data="{
                 target: {{ $match->locked_at->timestamp }},
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
            <span class="text-zinc-400 text-[10px] uppercase tracking-wide">locks</span>
            <span class="text-amber-300">
    {{ $match->locked_at->format('d M, H:i') }}
</span>
            <span class="text-zinc-600">·</span>
            <span class="tabular-nums text-amber-400/70"
                  x-text="(d > 0 ? d + 'd ' : '') + String(h).padStart(2,'0') + 'h ' + String(m).padStart(2,'0') + 'm ' + String(s).padStart(2,'0') + 's'"></span>
            <span class="text-zinc-700 mx-0.5">|</span>
            <span>🗓</span>
            <span class="text-zinc-400 text-[10px] uppercase tracking-wide">match</span>
            <span class="text-zinc-200 font-semibold">{{ $match->scheduled_at->format('d M · H:i') }}</span>
            <span class="text-zinc-500 text-[10px]">CET</span>
        </div>
    @elseif($isLocked && ! $isSettled)
        <div class="font-mono text-xs flex flex-wrap items-center gap-x-1.5 gap-y-1 sm:ml-auto">
            <span>🗓</span>
            <span class="text-zinc-400 text-[10px] uppercase tracking-wide">match</span>
            <span class="text-zinc-200 font-semibold">{{ $match->scheduled_at->format('d M · H:i') }}</span>
            <span class="text-zinc-500 text-[10px]">CET</span>
            <span class="text-zinc-700 mx-0.5">|</span>
            <span>🔒</span>
            <span class="text-zinc-400 font-semibold">picks locked</span>
        </div>
    @else
        <div class="font-mono text-xs text-zinc-500 sm:ml-auto">
            {{ $match->scheduled_at->format('d M · H:i') }}
            <span class="text-zinc-700 text-[10px]">CET</span>
        </div>
    @endif

</div>

    {{-- ═══════════════════════════════════════════════════════════════════
         Pick row — the hero of the card.
         Single line: [Pick A  Odds] vs [Odds  Pick B]
         Both sides are full-width buttons when open (main CTA).
         When locked/settled, they render as static pills keeping the shape.
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-[1fr_auto_1fr] items-stretch gap-2 px-4 pb-3">

        {{-- ── Side A ───────────────────────────────────────────────── --}}
        @php
            $aIsWinner = $isSettled && $winningSide === 'a';
            $aIsLoser  = $isSettled && $winningSide === 'b';
            $aIsMine   = $userPickedSide === 'a';

            // Can the current viewer click this side?
            //   - open match
            //   - not compact (dashboard wraps whole card in a link instead)
            //   - user either has no prediction yet, OR is a guest (guests get routed to login)
            $aClickable = $isOpen && ! $compact && ! $userPrediction;

            $aBaseClass = 'group relative flex items-center gap-2 px-3 py-3 rounded-lg border text-left transition-all min-w-0';
            $aStateClass = match(true) {
                $aIsWinner   => 'bg-emerald-500/10 border-emerald-500/40',
                $aIsLoser    => 'bg-zinc-900/40 border-zinc-800/60 opacity-40',
                $aIsMine     => 'bg-amber-500/10 border-amber-500/40',
                $aClickable  => 'bg-zinc-800/40 border-zinc-700/60 hover:bg-zinc-800 hover:border-amber-500/50 cursor-pointer',
                default      => 'bg-zinc-800/30 border-zinc-700/40',
            };
        @endphp

        @if($aClickable && auth()->check())
            {{-- Logged-in viewer: Livewire handles wallet check + bet modal. --}}
            <button wire:click="openBetModal({{ $match->id }}, 'a')"
                class="{{ $aBaseClass }} {{ $aStateClass }}"
                style="border-left: 3px solid {{ $raceHex($raceA) }};">
                <x-forecast.match-card-side
                    :name="$nameA"
                    :country="$countryA"
                    :race="$raceA"
                    :odds="$oddsA"
                    :is-favorite="$favoriteSide === 'a'"
                    :is-mine="false"
                    :is-winner="false"
                    :show-cta="true" />
            </button>
        @elseif($aClickable)
            {{-- Guest: fire the honeytrap modal. It looks like the real currency
                 picker but every interaction funnels to /login. --}}
            <button type="button"
                x-data
                x-on:click.stop.prevent="$dispatch('open-guest-wallet-modal')"
                class="{{ $aBaseClass }} {{ $aStateClass }}"
                style="border-left: 3px solid {{ $raceHex($raceA) }};">
                <x-forecast.match-card-side
                    :name="$nameA"
                    :country="$countryA"
                    :race="$raceA"
                    :odds="$oddsA"
                    :is-favorite="$favoriteSide === 'a'"
                    :is-mine="false"
                    :is-winner="false"
                    :show-cta="true" />
            </button>
        @else
            <div class="{{ $aBaseClass }} {{ $aStateClass }}"
                 style="border-left: 3px solid {{ $raceHex($raceA) }};">
                <x-forecast.match-card-side
                    :name="$nameA"
                    :country="$countryA"
                    :race="$raceA"
                    :odds="$oddsA"
                    :is-favorite="$favoriteSide === 'a'"
                    :is-mine="$aIsMine"
                    :is-winner="$aIsWinner"
                    :show-cta="false" />
            </div>
        @endif

        {{-- ── Divider ─────────────────────────────────────────────── --}}
        <div class="flex items-center justify-center px-1">
            @if($isSettled)
                <span class="text-lg">🏆</span>
            @else
                <span class="text-[10px] font-black text-zinc-600 tracking-[0.2em]">VS</span>
            @endif
        </div>

        {{-- ── Side B ───────────────────────────────────────────────── --}}
        @php
            $bIsWinner = $isSettled && $winningSide === 'b';
            $bIsLoser  = $isSettled && $winningSide === 'a';
            $bIsMine   = $userPickedSide === 'b';

            $bClickable = $isOpen && ! $compact && ! $userPrediction;

            $bBaseClass = 'group relative flex items-center gap-2 px-3 py-3 rounded-lg border text-right transition-all min-w-0 justify-end';
            $bStateClass = match(true) {
                $bIsWinner   => 'bg-emerald-500/10 border-emerald-500/40',
                $bIsLoser    => 'bg-zinc-900/40 border-zinc-800/60 opacity-40',
                $bIsMine     => 'bg-amber-500/10 border-amber-500/40',
                $bClickable  => 'bg-zinc-800/40 border-zinc-700/60 hover:bg-zinc-800 hover:border-amber-500/50 cursor-pointer',
                default      => 'bg-zinc-800/30 border-zinc-700/40',
            };
        @endphp

        @if($bClickable && auth()->check())
            <button wire:click="openBetModal({{ $match->id }}, 'b')"
                class="{{ $bBaseClass }} {{ $bStateClass }}"
                style="border-right: 3px solid {{ $raceHex($raceB) }};">
                <x-forecast.match-card-side
                    :name="$nameB"
                    :country="$countryB"
                    :race="$raceB"
                    :odds="$oddsB"
                    :is-favorite="$favoriteSide === 'b'"
                    :is-mine="false"
                    :is-winner="false"
                    :show-cta="true"
                    side="b" />
            </button>
        @elseif($bClickable)
            <button type="button"
                x-data
                x-on:click.stop.prevent="$dispatch('open-guest-wallet-modal')"
                class="{{ $bBaseClass }} {{ $bStateClass }}"
                style="border-right: 3px solid {{ $raceHex($raceB) }};">
                <x-forecast.match-card-side
                    :name="$nameB"
                    :country="$countryB"
                    :race="$raceB"
                    :odds="$oddsB"
                    :is-favorite="$favoriteSide === 'b'"
                    :is-mine="false"
                    :is-winner="false"
                    :show-cta="true"
                    side="b" />
            </button>
        @else
            <div class="{{ $bBaseClass }} {{ $bStateClass }}"
                 style="border-right: 3px solid {{ $raceHex($raceB) }};">
                <x-forecast.match-card-side
                    :name="$nameB"
                    :country="$countryB"
                    :race="$raceB"
                    :odds="$oddsB"
                    :is-favorite="$favoriteSide === 'b'"
                    :is-mine="$bIsMine"
                    :is-winner="$bIsWinner"
                    :show-cta="false"
                    side="b" />
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         Unauthenticated / no-pick-yet CTA — inline, subtle
         ═══════════════════════════════════════════════════════════════════ --}}
    {{-- Guest CTA removed — the per-side pick buttons themselves route to login
         for guests, so an extra "Log in to pick" link below would be redundant. --}}

    {{-- ═══════════════════════════════════════════════════════════════════
         Crowd split bar — community sentiment
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="px-4 pb-3">
        <div class="flex items-center justify-between text-[10px] mb-1">
            <span class="font-mono font-semibold {{ $crowdEmpty ? 'text-zinc-700' : 'text-zinc-500' }} tabular-nums">
                {{ $crowdEmpty ? '—' : $crowdA . '%' }}
            </span>
            <span class="text-zinc-600 uppercase tracking-wider">
                @if($crowdEmpty)
                    Be the first to pick
                @else
                    {{ $totalPicks }} {{ Str::plural('pick', $totalPicks) }}
                @endif
            </span>
            <span class="font-mono font-semibold {{ $crowdEmpty ? 'text-zinc-700' : 'text-zinc-500' }} tabular-nums">
                {{ $crowdEmpty ? '—' : $crowdB . '%' }}
            </span>
        </div>

        <div class="h-1.5 rounded-full overflow-hidden bg-zinc-800 flex">
            @if($crowdEmpty)
                <div class="w-full h-full bg-[repeating-linear-gradient(45deg,#3f3f46,#3f3f46_4px,#27272a_4px,#27272a_8px)] opacity-50"></div>
            @else
                <div class="h-full transition-all duration-500"
                    style="width: {{ $crowdA }}%; background: {{ $isSettled && $winningSide === 'a' ? '#10b981' : ($isSettled ? '#3f3f46' : $raceHex($raceA)) }};"></div>
                <div class="h-full transition-all duration-500"
                    style="width: {{ $crowdB }}%; background: {{ $isSettled && $winningSide === 'b' ? '#10b981' : ($isSettled ? '#3f3f46' : $raceHex($raceB)) }};"></div>
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
            @elseif(! $isSettled)
                <span class="ml-auto text-[11px] opacity-80 shrink-0">
                    if right: <strong>{{ number_format($userPrediction->potential_payout, 0) }}</strong>
                </span>
            @endif
        </div>
    @endif

    {{-- Compact-mode user prediction — single-liner, no ceremony --}}
    @if($compact && $userPrediction && $pickedName)
        <div class="mx-4 mb-3 text-[11px] text-amber-300/80 flex items-center gap-1.5">
            <span>⏳</span>
            <span class="truncate">Your pick: <strong class="text-amber-200">{{ $pickedName }}</strong> · {{ number_format($userPrediction->stake, 0) }} pts</span>
        </div>
    @endif

    {{-- Settled result banner --}}
    @if($isSettled && $winnerName)
        <div class="mx-4 mb-3 text-center">
            <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                <span>🏆</span>
                <strong>{{ $winnerName }}</strong> took it
            </span>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         Admin actions row — hidden in compact mode
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $compact && $canManageGames && ! $isSettled)
        <div class="flex items-center gap-2 flex-wrap px-4 pb-3 pt-1 border-t border-zinc-800/60">
            @if($isLocked)
                <button wire:click="openSettleModal({{ $match->id }})"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                    Settle result
                </button>
            @endif
            <button wire:click="openEditMatchModal({{ $match->id }})"
                class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">Edit</button>
            <span class="text-zinc-800">·</span>
            <button wire:click="$set('confirmingDeleteId', {{ $match->id }})"
                class="text-xs text-zinc-500 hover:text-red-400 transition-colors">Delete</button>
        </div>
    @endif
</div>