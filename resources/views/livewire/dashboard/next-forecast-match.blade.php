@php
    $match = $this->match;
    $userPrediction = $this->userPrediction;

    $isForeigner = $match->match_type === 'foreigner';

    // Resolve display names for both sides — foreigner pulls from players table,
    // other types use the snapshot fields stored on the match itself.
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

    $matchTypeBadge = match($match->match_type) {
        'foreigner' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'korean'    => 'bg-red-500/10 text-red-400 border-red-500/20',
        'national'  => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        default     => 'bg-zinc-700/80 text-zinc-400 border-zinc-600',
    };
@endphp

<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-5">

    {{-- Header — same pattern as every other dashboard widget --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            ⚡ Next match to predict
            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 align-middle">BETA</span>
        </p>
        <a href="{{ route('forecast.index') }}"
           class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors"
           wire:navigate>
            All matches →
        </a>
    </div>

    {{-- Match meta: type + event + scheduled time --}}
    <div class="flex items-center gap-2 mb-3 flex-wrap">
        <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $matchTypeBadge }}">
            {{ ucfirst($match->match_type) }}
        </span>
        @if($match->event)
            <span class="text-xs text-zinc-500 truncate min-w-0">{{ $match->event->name }}</span>
        @endif
        <span class="ml-auto text-xs font-mono text-zinc-500 shrink-0">
            {{ $match->scheduled_at->format('d M H:i') }} CET
        </span>
    </div>

    {{-- Players vs --}}
    <div class="flex items-stretch gap-2 mb-4">

        {{-- Player A --}}
        <div class="flex-1 min-w-0 rounded-lg bg-zinc-900/50 border border-zinc-700/40 px-3 py-3 text-center">
            <div class="flex items-center justify-center gap-1.5 mb-1">
                @if($countryA)
                    <img src="{{ asset('images/country_flags/' . strtolower($countryA) . '.svg') }}"
                         class="w-4 h-3 rounded-sm shrink-0"
                         alt="{{ $countryA }}">
                @endif
                <p class="font-bold text-white text-sm leading-tight truncate">{{ $nameA }}</p>
            </div>
            @if($raceA && $raceA !== 'Unknown')
                <p class="text-xs {{ $raceColor($raceA) }}">{{ $raceA }}</p>
            @endif
            <p class="text-sm font-mono font-bold text-zinc-300 mt-1.5">{{ $match->odds_a }}x</p>
        </div>

        <div class="flex items-center text-zinc-600 font-bold text-xs self-center px-1">VS</div>

        {{-- Player B --}}
        <div class="flex-1 min-w-0 rounded-lg bg-zinc-900/50 border border-zinc-700/40 px-3 py-3 text-center">
            <div class="flex items-center justify-center gap-1.5 mb-1">
                @if($countryB)
                    <img src="{{ asset('images/country_flags/' . strtolower($countryB) . '.svg') }}"
                         class="w-4 h-3 rounded-sm shrink-0"
                         alt="{{ $countryB }}">
                @endif
                <p class="font-bold text-white text-sm leading-tight truncate">{{ $nameB }}</p>
            </div>
            @if($raceB && $raceB !== 'Unknown')
                <p class="text-xs {{ $raceColor($raceB) }}">{{ $raceB }}</p>
            @endif
            <p class="text-sm font-mono font-bold text-zinc-300 mt-1.5">{{ $match->odds_b }}x</p>
        </div>
    </div>

    {{-- CTA + lock countdown --}}
    <div class="flex items-center justify-between gap-3">
        @auth
            @if($userPrediction)
                @php
                    $pickedName = $userPrediction->pick_side === 'a' ? $nameA : $nameB;
                @endphp
                <span class="text-xs text-zinc-400">
                    ✓ You picked <strong class="text-zinc-200">{{ $pickedName }}</strong>
                    · {{ number_format($userPrediction->stake, 0) }} pts
                </span>
            @else
                <a href="{{ route('forecast.index') }}"
                   wire:navigate
                   class="px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                    Make your pick →
                </a>
            @endif
        @else
            <a href="{{ route('login') }}"
               wire:navigate
               class="text-xs text-amber-400 hover:text-amber-300 transition-colors">
                Log in to pick →
            </a>
        @endauth

        <span class="text-xs text-zinc-600 font-mono">
            Locks {{ $match->locked_at->diffForHumans() }}
        </span>
    </div>

</div>