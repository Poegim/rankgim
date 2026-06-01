@php
    // Race colors use CSS vars (single source of truth in app.css)
    // Bar colors also use inline style with CSS vars — no hardcoded race hex
    $pairs = [['Terran', 'Zerg'], ['Terran', 'Protoss'], ['Zerg', 'Protoss']];

    $raceKey = [
        'Terran'  => 'terran',
        'Zerg'    => 'zerg',
        'Protoss' => 'protoss',
    ];
@endphp

<div class="rounded-xl border p-3 sm:p-5
    border-travertine-300 bg-travertine-50
    dark:border-zinc-700/60 dark:bg-zinc-800/40">

    <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
        ⚔️ Global race matchups
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach($pairs as [$r1, $r2])
        @php
            $r1wins  = $this->matchups->get($r1 . '-' . $r2)?->games ?? 0;
            $r2wins  = $this->matchups->get($r2 . '-' . $r1)?->games ?? 0;
            $total   = $r1wins + $r2wins;
            $r1ratio = $total > 0 ? round(($r1wins / $total) * 100) : 50;
            $r2ratio = 100 - $r1ratio;
            $k1 = $raceKey[$r1];
            $k2 = $raceKey[$r2];
        @endphp

        <div class="rounded-xl border p-4
            bg-travertine-75 border-travertine-300
            dark:bg-zinc-800/60 dark:border-zinc-700/40">

            {{-- Race name headers --}}
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold" style="color: var(--color-race-{{ $k1 }})">{{ $r1 }}</span>
                <span class="text-xs font-mono text-travertine-400 dark:text-zinc-600">
                    {{ number_format($total) }} games
                </span>
                <span class="text-sm font-bold" style="color: var(--color-race-{{ $k2 }})">{{ $r2 }}</span>
            </div>

            {{-- Win % + win count --}}
            <div class="flex items-end justify-between mb-2">
                <div>
                    <p class="font-mono text-xl font-black" style="color: var(--color-race-{{ $k1 }})">{{ $r1ratio }}%</p>
                    <p class="text-xs text-travertine-400 dark:text-zinc-600">{{ number_format($r1wins) }}W</p>
                </div>
                <div class="text-right">
                    <p class="font-mono text-xl font-black" style="color: var(--color-race-{{ $k2 }})">{{ $r2ratio }}%</p>
                    <p class="text-xs text-travertine-400 dark:text-zinc-600">{{ number_format($r2wins) }}W</p>
                </div>
            </div>

            {{-- Progress bar — two segments using CSS vars --}}
            <div class="h-1.5 rounded-full overflow-hidden flex
                bg-travertine-200 dark:bg-zinc-700">
                <div class="h-full" style="width: {{ $r1ratio }}%; background-color: var(--color-race-{{ $k1 }})"></div>
                <div class="h-full" style="width: {{ $r2ratio }}%; background-color: var(--color-race-{{ $k2 }})"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>