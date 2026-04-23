<div>
    @php $stats = $this->stats; @endphp

    {{-- ══════════════════════════════════════════════════════════════════
         Summary stat cards
         ══════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        {{-- Total profit --}}
        <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 p-4">
            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Net profit</p>
            <p class="text-2xl font-mono font-bold mt-1 {{ $stats['profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $stats['profit'] >= 0 ? '+' : '' }}{{ number_format($stats['profit'], 0) }}
            </p>
            <p class="text-[11px] text-zinc-600 mt-0.5">
                staked {{ number_format($stats['total_stake'], 0) }} · earned {{ number_format($stats['total_payout'], 0) }}
            </p>
        </div>

        {{-- Accuracy --}}
        <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 p-4">
            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Accuracy</p>
            <p class="text-2xl font-mono font-bold text-white mt-1">{{ $stats['accuracy'] }}%</p>
            <p class="text-[11px] text-zinc-600 mt-0.5">
                {{ $stats['won'] }}W / {{ $stats['lost'] }}L
                @if($stats['pending'] > 0) · {{ $stats['pending'] }} pending @endif
            </p>
        </div>

        {{-- Current streak --}}
        <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 p-4">
            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Current streak</p>
            @if($stats['streak_type'] === 'won')
                <p class="text-2xl font-mono font-bold text-emerald-400 mt-1">🔥 {{ $stats['streak_len'] }}</p>
                <p class="text-[11px] text-zinc-600 mt-0.5">on a winning run</p>
            @elseif($stats['streak_type'] === 'lost')
                <p class="text-2xl font-mono font-bold text-red-400 mt-1">❄️ {{ $stats['streak_len'] }}</p>
                <p class="text-[11px] text-zinc-600 mt-0.5">cold streak</p>
            @else
                <p class="text-2xl font-mono font-bold text-zinc-600 mt-1">—</p>
                <p class="text-[11px] text-zinc-600 mt-0.5">no settled picks yet</p>
            @endif
        </div>

        {{-- Longest streak --}}
        <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 p-4">
            <p class="text-[10px] uppercase tracking-wider text-zinc-500">Longest win streak</p>
            <p class="text-2xl font-mono font-bold text-white mt-1">{{ $stats['longest_win'] }}</p>
            <p class="text-[11px] text-zinc-600 mt-0.5">in a row this season</p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         Profit over time chart (ApexCharts, matches the rest of the app)
         ══════════════════════════════════════════════════════════════════ --}}
    {{-- @if(count($this->chartData) >= 2)
    <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 p-4 mb-6">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-zinc-300">Profit over time</p>
            <p class="text-[11px] text-zinc-600">{{ count($this->chartData) }} settled predictions</p>
        </div>

        <script>
            // Scoped to this element via ref — avoid leaking to window
            window._forecastHistoryData = @json($this->chartData);
        </script>

        <div x-data="{
                init() {
                    new ApexCharts(this.$refs.chart, {
                        chart: {
                            type: 'area',
                            height: 260,
                            background: 'transparent',
                            toolbar: { show: false },
                            animations: { enabled: false },
                            zoom: { enabled: false },
                        },
                        theme: {
                            mode: 'dark',
                        },
                        series: [{
                            name: 'Net profit',
                            data: window._forecastHistoryData,
                        }],
                        stroke: {
                            curve: 'smooth',
                            width: 2,
                            colors: ['#10b981'],
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.05,
                                stops: [0, 100],
                                colorStops: [
                                    { offset: 0,   color: '#10b981', opacity: 0.3 },
                                    { offset: 100, color: '#10b981', opacity: 0.0 },
                                ],
                            },
                        },
                        xaxis: {
                            type: 'category',
                            labels: {
                                style: { colors: '#71717a', fontSize: '11px' },
                                rotate: 0,
                                hideOverlappingLabels: true,
                            },
                            axisBorder: { show: false },
                            axisTicks:  { show: false },
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#71717a', fontSize: '11px' },
                                formatter: v => (v >= 0 ? '+' : '') + Math.round(v),
                            },
                        },
                        grid: {
                            borderColor: '#27272a',
                            strokeDashArray: 4,
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: v => (v >= 0 ? '+' : '') + v.toFixed(2) + ' pts',
                            },
                        },
                        markers: {
                            size: 3,
                            colors: ['#10b981'],
                            strokeWidth: 0,
                            hover: { size: 5 },
                        },
                    }).render();
                }
            }" x-init="init()">
            <div x-ref="chart"></div>
        </div>
    </div>
    @elseif(count($this->chartData) === 1)
    <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 p-6 mb-6 text-center">
        <p class="text-xs text-zinc-600">Need at least 2 settled predictions to draw a chart.</p>
    </div>
    @endif --}}

    {{-- ══════════════════════════════════════════════════════════════════
         Predictions table
         ══════════════════════════════════════════════════════════════════ --}}
    @if($this->predictions->isEmpty())
        <div class="text-center py-16 text-zinc-600">
            <p class="text-5xl mb-3">📜</p>
            <p class="text-sm">You haven't made any forecasts yet this season.</p>
        </div>
    @else
        <div class="rounded-xl border border-zinc-700/40 bg-zinc-900/50 overflow-hidden">

            {{-- Header row --}}
            <div class="hidden md:grid grid-cols-[1fr_1.5fr_80px_90px_90px_90px] gap-3 px-4 py-2.5 border-b border-zinc-800 bg-zinc-900/80">
                <span class="text-[10px] uppercase tracking-wider text-zinc-500">When</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500">Match · Pick</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Stake</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Odds × Perk</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Payout</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500 text-right">Net</span>
            </div>

            @foreach($this->predictions as $p)
                @php
                    $match = $p->match;
                    $isForeigner = $match->match_type === 'foreigner';

                    $nameA = $isForeigner ? ($match->playerA?->name ?? '?') : ($match->player_a_name ?? '?');
                    $nameB = $isForeigner ? ($match->playerB?->name ?? '?') : ($match->player_b_name ?? '?');

                    // Resolve picked name
                    if ($p->pick_player_id) {
                        $pickedName = $p->pickedPlayer?->name ?? '?';
                    } elseif ($p->pick_side === 'a') {
                        $pickedName = $nameA;
                    } else {
                        $pickedName = $nameB;
                    }

                    // Row styling by result
                    $rowTint = match($p->result) {
                        'won'      => 'bg-emerald-500/[0.03] hover:bg-emerald-500/[0.06]',
                        'lost'     => 'bg-red-500/[0.03] hover:bg-red-500/[0.06]',
                        'refunded' => 'bg-zinc-800/30 hover:bg-zinc-800/50',
                        default    => 'bg-amber-500/[0.03] hover:bg-amber-500/[0.06]',
                    };

                    $resultIcon = match($p->result) {
                        'won'      => '✓',
                        'lost'     => '✗',
                        'refunded' => '↩',
                        default    => '⏳',
                    };

                    $resultColor = match($p->result) {
                        'won'      => 'text-emerald-400',
                        'lost'     => 'text-red-400',
                        'refunded' => 'text-zinc-500',
                        default    => 'text-amber-400',
                    };

                    $net = match($p->result) {
                        'won'      => ((float) $p->actual_payout) - ((float) $p->stake),
                        'lost'     => -((float) $p->stake),
                        'refunded' => 0.0,
                        default    => null,
                    };
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-[1fr_1.5fr_80px_90px_90px_90px] gap-3 px-4 py-3 border-b border-zinc-800/60 last:border-b-0 transition-colors {{ $rowTint }}">

                    {{-- When / result icon --}}
                    <div class="flex items-center gap-2">
                        <span class="text-base {{ $resultColor }} shrink-0">{{ $resultIcon }}</span>
                        <div class="min-w-0">
                            <p class="text-xs text-zinc-400 font-mono">{{ $p->created_at->format('d M · H:i') }}</p>
                            <p class="text-[10px] text-zinc-600 uppercase tracking-wider">
                                {{ $match->match_type }}
                                @if($p->result !== 'pending')
                                    · {{ $p->updated_at->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Match + pick --}}
                    <div class="min-w-0">
                        <p class="text-sm text-zinc-300 truncate">
                            <span class="text-zinc-500">{{ $nameA }}</span>
                            <span class="text-zinc-700 mx-1">vs</span>
                            <span class="text-zinc-500">{{ $nameB }}</span>
                        </p>
                        <p class="text-xs mt-0.5">
                            <span class="text-zinc-600">called:</span>
                            <strong class="{{ $resultColor }}">{{ $pickedName }}</strong>
                        </p>
                    </div>

                    {{-- Stake --}}
                    <div class="text-right">
                        <p class="text-sm font-mono text-zinc-300">{{ number_format($p->stake, 0) }}</p>
                        <p class="text-[10px] text-zinc-600 md:hidden">stake</p>
                    </div>

                    {{-- Odds × perk --}}
                    <div class="text-right">
                        <p class="text-sm font-mono text-zinc-400">
                            ×{{ number_format($p->odds_at_time, 2) }}
                        </p>
                        <p class="text-[10px] text-zinc-600">
                            @if($p->bonus_multiplier > 1)
                                perk ×{{ number_format($p->bonus_multiplier, 2) }}
                            @else
                                no perk
                            @endif
                        </p>
                    </div>

                    {{-- Payout --}}
                    <div class="text-right">
                        @if($p->result === 'won')
                            <p class="text-sm font-mono text-emerald-400 font-semibold">
                                +{{ number_format($p->actual_payout, 0) }}
                            </p>
                        @elseif($p->result === 'pending')
                            <p class="text-sm font-mono text-amber-400/70">
                                {{ number_format($p->potential_payout, 0) }}
                            </p>
                            <p class="text-[10px] text-zinc-600">potential</p>
                        @else
                            <p class="text-sm font-mono text-zinc-600">—</p>
                        @endif
                    </div>

                    {{-- Net --}}
                    <div class="text-right">
                        @if($net === null)
                            <p class="text-sm font-mono text-zinc-600">—</p>
                            <p class="text-[10px] text-zinc-600">pending</p>
                        @elseif($p->result === 'refunded')
                            <p class="text-sm font-mono text-zinc-500">0</p>
                            <p class="text-[10px] text-zinc-600">refunded</p>
                        @else
                            <p class="text-sm font-mono font-bold {{ $net >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 0) }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>