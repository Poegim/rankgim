@php
    $raceColors = [
        'Terran'  => 'text-blue-400',
        'Zerg'    => 'text-purple-400',
        'Protoss' => 'text-yellow-400',
    ];
    $raceBars = [
        'Terran'  => 'bg-blue-500',
        'Zerg'    => 'bg-purple-500',
        'Protoss' => 'bg-yellow-500',
    ];
    $pairs = [['Terran', 'Zerg'], ['Terran', 'Protoss'], ['Zerg', 'Protoss']];
@endphp

<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-3 sm:p-5">
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">⚔️ Global race matchups</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach($pairs as [$r1, $r2])
        @php
            $r1wins  = $this->matchups->get($r1 . '-' . $r2)?->games ?? 0;
            $r2wins  = $this->matchups->get($r2 . '-' . $r1)?->games ?? 0;
            $total   = $r1wins + $r2wins;
            $r1ratio = $total > 0 ? round(($r1wins / $total) * 100) : 50;
            $r2ratio = 100 - $r1ratio;
        @endphp
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-700/40 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold {{ $raceColors[$r1] }}">{{ $r1 }}</span>
                <span class="text-xs text-zinc-600 font-mono">{{ number_format($total) }} games</span>
                <span class="text-sm font-bold {{ $raceColors[$r2] }}">{{ $r2 }}</span>
            </div>
            <div class="flex items-end justify-between mb-2">
                <div>
                    <p class="font-mono text-xl font-black {{ $raceColors[$r1] }}">{{ $r1ratio }}%</p>
                    <p class="text-xs text-zinc-600">{{ number_format($r1wins) }}W</p>
                </div>
                <div class="text-right">
                    <p class="font-mono text-xl font-black {{ $raceColors[$r2] }}">{{ $r2ratio }}%</p>
                    <p class="text-xs text-zinc-600">{{ number_format($r2wins) }}W</p>
                </div>
            </div>
            <div class="h-1.5 rounded-full bg-zinc-700 overflow-hidden flex">
                <div class="h-full {{ $raceBars[$r1] }}" style="width: {{ $r1ratio }}%"></div>
                <div class="h-full {{ $raceBars[$r2] }}" style="width: {{ $r2ratio }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>