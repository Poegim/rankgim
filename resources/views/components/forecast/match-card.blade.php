@props([
    'match',                       // ForecastMatch instance with predictions loaded
    'userPrediction' => null,      // ForecastPrediction|null — current user's pick
    'canManageGames' => false,     // bool — show admin actions
])

@php
    // ─── Derive everything for this one card ─────────────────────────────
    $isLocked    = $match->isLocked();
    $isSettled   = $match->isSettled();
    $isForeigner = $match->match_type === 'foreigner';

    $nameA = $isForeigner ? ($match->playerA?->name ?? '?') : ($match->player_a_name ?? '?');
    $nameB = $isForeigner ? ($match->playerB?->name ?? '?') : ($match->player_b_name ?? '?');

    $raceA = $match->player_a_race;
    $raceB = $match->player_b_race;

    $raceColor = fn($race) => match($race) {
        'Terran'  => 'text-blue-400',
        'Zerg'    => 'text-purple-400',
        'Protoss' => 'text-yellow-400',
        default   => 'text-zinc-500',
    };

    $countryA = $isForeigner ? ($match->playerA?->country_code ?? null) : ($match->player_a_country ?? null);
    $countryB = $isForeigner ? ($match->playerB?->country_code ?? null) : ($match->player_b_country ?? null);

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

    // Resolve picked name for the user's prediction
    $pickedName = null;
    if ($userPrediction) {
        if ($userPrediction->pick_player_id) {
            $pickedName = $userPrediction->pickedPlayer?->name;
        } elseif ($userPrediction->pick_side === 'a') {
            $pickedName = $nameA;
        } elseif ($userPrediction->pick_side === 'b') {
            $pickedName = $nameB;
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

    // Reward pill styling — highlighted amber for open matches, muted for settled
    $rewardPillClass = $isSettled
        ? 'bg-zinc-800/50 border-zinc-700/50 text-zinc-500'
        : 'bg-zinc-500/10 border-zinc-500/30 text-zinc-300';
@endphp

<div class="rounded-2xl border {{ $isSettled ? 'border-zinc-800/60 bg-zinc-900/30' : 'border-zinc-700/50 bg-zinc-900/50' }} overflow-hidden">

    {{-- Top ribbon: type / event / schedule --}}
    <div class="flex items-center gap-2 px-4 pt-3 pb-2 flex-wrap">
        <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-full font-semibold
            {{ $match->match_type === 'foreigner' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' :
               ($match->match_type === 'korean'   ? 'bg-red-500/10 text-red-400 border border-red-500/20' :
               ($match->match_type === 'national' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' :
                                                    'bg-zinc-800 text-zinc-400 border border-zinc-700')) }}">
            {{ ucfirst($match->match_type) }}
        </span>
        @if($match->event)
            <span class="text-xs text-zinc-500 truncate">{{ $match->event->name }}</span>
        @endif
        <span class="ml-auto text-[11px] font-mono text-zinc-600 shrink-0">
            {{ $match->scheduled_at->format('d M · H:i') }} CET
        </span>
    </div>

    <div class="px-5 pb-4">

        {{-- ═══════════════════════════════════════════════════════════════
             Players face-off — names are the star of the card
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-4 mb-2 ">

            {{-- Side A --}}
            <div class="flex flex-col items-end gap-2 {{ $isSettled && $winningSide !== 'a' ? 'opacity-40' : '' }}">
                <div class="flex items-center justify-end gap-2">
                    @if($raceA !== 'Unknown')
                        <span class="text-[10px] uppercase tracking-widest font-semibold {{ $raceColor($raceA) }}">{{ $raceA }}</span>
                    @endif
                    @if($countryA)
                        <img src="{{ asset('images/country_flags/' . strtolower($countryA) . '.svg') }}"
                            class="w-5 h-3.5 rounded-sm shrink-0" alt="">
                    @endif
                    <p class="font-bold text-white text-xl sm:text-2xl leading-tight truncate tracking-tight text-right">
                        {{ $nameA }}
                    </p>
                </div>
                {{-- <span class="text-xs font-mono font-semibold px-1 py-1 rounded-md {{ $rewardPillClass }}">
                    ×{{ $match->odds_a }}
                </span> --}}
            </div>

            {{-- VS divider --}}
            <div class="flex flex-col items-center gap-1 px-2">
                @if($isSettled)
                    <span class="text-2xl">🏆</span>
                @else
                    <span class="text-zinc-600 font-black text-sm tracking-[0.3em]">VS</span>
                @endif
            </div>

            {{-- Side B --}}
            <div class="flex flex-col items-start gap-2 {{ $isSettled && $winningSide !== 'b' ? 'opacity-40' : '' }}">
                <div class="flex items-center justify-start gap-2">
                    <p class="font-bold text-white text-xl sm:text-2xl leading-tight truncate tracking-tight text-left">
                        {{ $nameB }}
                    </p>
                    @if($countryB)
                        <img src="{{ asset('images/country_flags/' . strtolower($countryB) . '.svg') }}"
                            class="w-5 h-3.5 rounded-sm shrink-0" alt="">
                    @endif
                    @if($raceB !== 'Unknown')
                        <span class="text-[10px] uppercase tracking-widest font-semibold {{ $raceColor($raceB) }}">{{ $raceB }}</span>
                    @endif
                </div>
                {{-- <span class="text-xs font-mono font-semibold px-1 py-1 rounded-md {{ $rewardPillClass }}">
                    ×{{ $match->odds_b }}
                </span> --}}
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             Crowd split bar — slimmer, less dominant
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="mb-3">
            <div class="flex items-center justify-between text-[10px] mb-1">
                <span class="font-mono font-semibold {{ $crowdEmpty ? 'text-zinc-700' : 'text-zinc-500' }}">
                    {{ $crowdEmpty ? '—' : $crowdA . '%' }}
                </span>
                <span class="text-zinc-600 uppercase tracking-wider">
                    @if($crowdEmpty)
                        No forecasts yet
                    @else
                        {{ $totalPicks }} {{ Str::plural('forecast', $totalPicks) }}
                    @endif
                </span>
                <span class="font-mono font-semibold {{ $crowdEmpty ? 'text-zinc-700' : 'text-zinc-500' }}">
                    {{ $crowdEmpty ? '—' : $crowdB . '%' }}
                </span>
            </div>

            <div class="h-1.5 rounded-full overflow-hidden bg-zinc-800 flex">
                @if($crowdEmpty)
                    <div class="w-full h-full bg-zinc-800"></div>
                @else
                    <div class="h-full transition-all duration-500
                        {{ $isSettled && $winningSide === 'a' ? 'bg-emerald-500/70' : ($isSettled ? 'bg-zinc-700' : 'bg-blue-500/60') }}"
                        style="width: {{ $crowdA }}%"></div>
                    <div class="h-full transition-all duration-500
                        {{ $isSettled && $winningSide === 'b' ? 'bg-emerald-500/70' : ($isSettled ? 'bg-zinc-700' : 'bg-rose-500/60') }}"
                        style="width: {{ $crowdB }}%"></div>
                @endif
            </div>
        </div>

        {{-- Settled result banner --}}
        @if($isSettled && $winnerName)
            <div class="text-center mb-3">
                <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                    <span>🏆</span>
                    <strong>{{ $winnerName }}</strong> took it
                </span>
            </div>
        @endif

        {{-- User's own prediction badge --}}
        @if($userPrediction && $pickedName)
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
            <div class="rounded-lg px-3 py-2 text-xs border flex items-center gap-2 flex-wrap {{ $predStyle }}">
                <span class="font-bold">{{ $predIcon }}</span>
                <span>
                    You called <strong>{{ $pickedName }}</strong>
                    · {{ number_format($userPrediction->stake, 0) }} pts
                    @if($userPrediction->bonus_multiplier > 1)
                        <span class="opacity-70">(×{{ $userPrediction->bonus_multiplier }} perk)</span>
                    @endif
                </span>
                @if($userPrediction->result === 'won')
                    <span class="ml-auto font-bold font-mono">+{{ number_format($userPrediction->actual_payout, 0) }} pts</span>
                @elseif(! $isSettled)
                    <span class="ml-auto text-[11px] opacity-80">
                        if right: <strong>{{ number_format($userPrediction->potential_payout, 0) }}</strong>
                    </span>
                @endif
            </div>
        @endif

        {{-- Actions row --}}
        <div class="flex items-center gap-2 flex-wrap mt-3">
            @auth
                @if(! $isSettled && ! $isLocked && ! $userPrediction)
                    <button wire:click="openBetModal({{ $match->id }})"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg bg-gradient-to-r from-amber-500/15 to-amber-500/5 text-amber-300 border border-amber-500/30 hover:from-amber-500/25 hover:to-amber-500/10 transition-all">
                        <span>🎯</span> Make your call
                    </button>
                @elseif(! $isSettled && $isLocked && ! $userPrediction)
                    <span class="text-xs text-zinc-600 italic flex items-center gap-1">
                        <span>🔒</span> Forecasts closed
                    </span>
                @endif
            @else
                @if(! $isSettled && ! $isLocked)
                    <a href="{{ route('login') }}" class="text-xs text-amber-400 hover:text-amber-300">
                        Log in to forecast →
                    </a>
                @endif
            @endauth

            @if($canManageGames)
                @if(! $isSettled && $isLocked)
                    <button wire:click="openSettleModal({{ $match->id }})"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                        Settle result
                    </button>
                @endif
                @if(! $isSettled)
                    <button wire:click="openEditMatchModal({{ $match->id }})"
                        class="text-xs text-zinc-600 hover:text-zinc-300 transition-colors">Edit</button>
                    <span class="text-zinc-800">·</span>
                    <button wire:click="$set('confirmingDeleteId', {{ $match->id }})"
                        class="text-xs text-zinc-600 hover:text-red-400 transition-colors">Delete</button>
                @endif
            @endif

            @if(! $isSettled && ! $isLocked)
                <span class="ml-auto text-[11px] text-zinc-600 font-mono flex items-center gap-1">
                    <span>⏱</span> locks {{ $match->locked_at->diffForHumans() }}
                </span>
            @endif
        </div>
    </div>
</div>