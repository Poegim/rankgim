@php
    // Race → pill background + text colors for the nickname pill.
    // Tailwind JIT needs full class names, so they live in a map (not interpolated).
    $racePill = fn($race) => match($race) {
        'Terran'  => 'bg-blue-500/20 text-blue-100 border border-blue-500/40',
        'Zerg'    => 'bg-purple-500/20 text-purple-100 border border-purple-500/40',
        'Protoss' => 'bg-yellow-500/20 text-yellow-100 border border-yellow-500/40',
        'Random'  => 'bg-orange-500/20 text-orange-100 border border-orange-500/40',
        default   => 'bg-zinc-700/50 text-zinc-100 border border-zinc-600/60',
    };

    // Matches the bar segment color to the race of the player on that side.
    $raceBarColor = fn($race) => match($race) {
        'Terran'  => '#3b82f6',
        'Zerg'    => '#a855f7',
        'Protoss' => '#eab308',
        'Random'  => '#f97316',
        default   => '#71717a',
    };

    $matchTypeBadge = fn($type) => match($type) {
        'foreigner' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'korean'    => 'bg-red-500/10 text-red-400 border-red-500/20',
        'clan'      => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'national'  => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        default     => 'bg-zinc-700/80 text-zinc-400 border-zinc-600',
    };
@endphp

<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden">

    {{-- Header — "let's check the forecast" because it's the forecast widget, ha ha --}}
    <div class="flex items-center justify-between px-5 pt-5 pb-3">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            ☀️ Let's check the forecast
            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 align-middle">BETA</span>
        </p>
        <a href="{{ route('forecast.index') }}"
           class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors"
           wire:navigate>
            All matches →
        </a>
    </div>

    {{-- List of matches --}}
    <div class="flex flex-col px-2 sm:px-4">
        @foreach($this->matches as $loopMatch)
            @php
                $isForeigner = $loopMatch->match_type === 'foreigner';

                $nameA    = $isForeigner ? ($loopMatch->playerA?->name ?? '?')          : ($loopMatch->player_a_name ?? '?');
                $nameB    = $isForeigner ? ($loopMatch->playerB?->name ?? '?')          : ($loopMatch->player_b_name ?? '?');
                $countryA = $isForeigner ? ($loopMatch->playerA?->country_code ?? null) : ($loopMatch->player_a_country ?? null);
                $countryB = $isForeigner ? ($loopMatch->playerB?->country_code ?? null) : ($loopMatch->player_b_country ?? null);
                $raceA    = $loopMatch->player_a_race ?? 'Unknown';
                $raceB    = $loopMatch->player_b_race ?? 'Unknown';

                $oddsA = (float) $loopMatch->odds_a;
                $oddsB = (float) $loopMatch->odds_b;

                // Lower odds = favorite. Equal odds = neither favored.
                $favoriteSide = $oddsA < $oddsB ? 'a' : ($oddsB < $oddsA ? 'b' : null);

                // Resolve current user's pick (if any) — predictions already eager-loaded.
                $userPrediction = auth()->check()
                    ? $loopMatch->predictions->firstWhere('user_id', auth()->id())
                    : null;

                $userPickedSide = null;
                if ($userPrediction) {
                    $userPickedSide = $isForeigner
                        ? ($userPrediction->pick_player_id === $loopMatch->player_a_id ? 'a' : 'b')
                        : $userPrediction->pick_side;
                }

                // Community sentiment was pre-computed on the model in NextForecastMatch.php.
                $pctA       = $loopMatch->stake_a_percent;
                $pctB       = $loopMatch->stake_b_percent;
                $picksCount = $loopMatch->picks_count;
                $hasPicks   = $picksCount > 0;
            @endphp

            <div class="border border-zinc-700 px-5 py-4 bg-zinc-900 mt-2 rounded-lg ">

                {{-- Meta strip --}}
                <div class="flex items-center gap-2 mb-3 flex-wrap">
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider border {{ $matchTypeBadge($loopMatch->match_type) }}">
                        {{ $loopMatch->match_type }}
                    </span>
                                        @auth
                    @if($userPrediction)
                        <span class="text-xs text-zinc-400">
                            <span class="font-mono">{{ number_format($userPrediction->stake, 0) }} pts</span> on the line
                        </span>
                    @else
                        <a href="{{ route('forecast.index') }}"
                           wire:navigate
                           class="px-2 py-0.5 text-xs rounded-md bg-amber-500/15 text-amber-300 border border-amber-500/30 hover:bg-amber-500/25 hover:text-amber-200 transition-colors uppercase tracking-wider">
                            <span>🎯</span> Make your call
                        </a>
                    @endif
                    @else
                        <a href="{{ route('login') }}"
                           wire:navigate
                           class="text-xs text-amber-400 hover:text-amber-300 transition-colors font-medium">
                            Log in to pick →
                        </a>
                    @endauth

                    @if($loopMatch->event)
                        <span class="text-xs text-zinc-500 truncate min-w-0">{{ $loopMatch->event->name }}</span>
                    @endif
                    <span class="ml-auto text-xs font-mono text-zinc-500 shrink-0">
                        {{ $loopMatch->scheduled_at->format('d M · H:i') }} CET
                    </span>
                </div>
                {{-- Players row (clean, single line) --}}
                <div class="flex items-center justify-between gap-3 mb-2">

                    {{-- Player A --}}
                    <div class="flex items-center gap-2 min-w-0">
                        @if($countryA)
                            <img src="{{ asset('images/country_flags/' . strtolower($countryA) . '.svg') }}"
                                 class="w-5 h-3.5 rounded-sm shrink-0"
                                 alt="{{ $countryA }}">
                        @endif

                        <span class="text-sm font-semibold text-zinc-100 truncate uppercase">
                            {{ $nameA }}
                        </span>

                        {{-- pick indicator --}}
                        @if($userPickedSide === 'a')
                            <span class="text-[10px] font-bold text-amber-400 uppercase">•</span>
                        @endif
                    </div>

                    {{-- VS --}}
                    <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest shrink-0">
                        vs
                    </span>

                    {{-- Player B --}}
                    <div class="flex items-center gap-2 min-w-0 justify-end">
                        @if($userPickedSide === 'b')
                            <span class="text-[10px] font-bold text-amber-400 uppercase">•</span>
                        @endif

                        <span class="text-sm font-semibold text-zinc-100 truncate text-right uppercase">
                            {{ $nameB }}
                        </span>

                        @if($countryB)
                            <img src="{{ asset('images/country_flags/' . strtolower($countryB) . '.svg') }}"
                                 class="w-5 h-3.5 rounded-sm shrink-0"
                                 alt="{{ $countryB }}">
                        @endif
                    </div>

                </div>

                {{-- Community sentiment bar — money share --}}
                <div class="my-2">
                    <div class="flex h-1.5 rounded-full overflow-hidden bg-zinc-900/60">
                        @if($hasPicks)
                            <div class="transition-all" style="width: {{ $pctA }}%; background: {{ $raceBarColor($raceA) }};"></div>
                            <div class="transition-all" style="width: {{ $pctB }}%; background: {{ $raceBarColor($raceB) }};"></div>
                        @else
                            {{-- No picks yet — neutral empty bar --}}
                            <div class="w-full h-full bg-[repeating-linear-gradient(45deg,#3f3f46,#3f3f46_6px,#27272a_6px,#27272a_12px)] opacity-60"></div>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-1 text-[10px] font-mono text-zinc-500 tabular-nums">
                        @if($hasPicks)
                            <span>{{ $pctA }}%</span>
                            <span class="text-zinc-600">{{ $picksCount }} {{ $picksCount === 1 ? 'pick' : 'picks' }} · {{ number_format($loopMatch->stake_total, 0) }} pts in play</span>
                            <span>{{ $pctB }}%</span>
                        @else
                            <span class="text-zinc-600 mx-auto italic">Be the first to pick</span>
                        @endif
                    </div>
                </div>


            </div>
        @endforeach
    </div>

</div>