@props([
    'name',
    'country'    => null,
    'race'       => 'Unknown',
    'odds',
    'isFavorite' => false,
    'isMine'     => false,
    'isWinner'   => false,
    'showCta'    => false,   // show the "Pick →" hover CTA
    'side'       => 'a',     // 'a' | 'b' — controls alignment
])

@php
    // Odds color — favorite green, underdog amber, equal/neutral zinc.
    $oddsColor = $isFavorite ? 'text-emerald-400' : 'text-amber-400';

    $isSideA = $side === 'a';
@endphp
{{-- Side A: flag → name → odds (left-aligned)
     Side B: odds → name → flag (right-aligned)
     The parent button/div already handles alignment — we just order children. --}}

@if($isSideA)
    {{-- Flag --}}
    @if($country)
        <img src="{{ asset('images/country_flags/' . strtolower($country) . '.svg') }}"
             class="w-5 h-3.5 rounded-sm shrink-0" alt="{{ $country }}">
    @endif

    {{-- Name + race --}}
    <div class="flex flex-col min-w-0 flex-1">
        <span class="text-sm font-bold text-zinc-100 truncate leading-tight">
            {{ $name }}
            @if($isMine)
                <span class="text-[9px] font-mono text-amber-400 ml-1">• YOURS</span>
            @endif
            @if($isWinner)
                <span class="text-[9px] font-mono text-emerald-400 ml-1">• WIN</span>
            @endif
        </span>
        @if($race !== 'Unknown' && $race !== null)
            <span class="text-[10px] uppercase tracking-wider font-semibold" style="color: {{ match($race) { 'Terran' => '#60a5fa', 'Zerg' => '#c084fc', 'Protoss' => '#facc15', 'Random' => '#fb923c', default => '#71717a' } }};">
                {{ $race }}
            </span>
        @endif
    </div>

    {{-- Odds / CTA --}}
    <div class="flex flex-col items-end shrink-0">
        <span class="font-mono font-bold text-sm {{ $oddsColor }} tabular-nums">
            ×{{ number_format((float) $odds, 2) }}
        </span>
        @if($showCta)
            <span class="text-[10px] text-amber-400/80 font-semibold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity">
                Pick →
            </span>
        @endif
    </div>
@else
    {{-- Side B: reverse order --}}
    {{-- Odds / CTA --}}
    <div class="flex flex-col items-start shrink-0 order-3">
        <span class="font-mono font-bold text-sm {{ $oddsColor }} tabular-nums">
            ×{{ number_format((float) $odds, 2) }}
        </span>
        @if($showCta)
            <span class="text-[10px] text-amber-400/80 font-semibold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity">
                ← Pick
            </span>
        @endif
    </div>

    {{-- Name + race --}}
    <div class="flex flex-col min-w-0 flex-1 items-end order-2">
        <span class="text-sm font-bold text-zinc-100 truncate leading-tight text-right">
            @if($isWinner)
                <span class="text-[9px] font-mono text-emerald-400 mr-1">WIN •</span>
            @endif
            @if($isMine)
                <span class="text-[9px] font-mono text-amber-400 mr-1">YOURS •</span>
            @endif
            {{ $name }}
        </span>
        @if($race !== 'Unknown' && $race !== null)
            <span class="text-[10px] uppercase tracking-wider font-semibold" style="color: {{ match($race) { 'Terran' => '#60a5fa', 'Zerg' => '#c084fc', 'Protoss' => '#facc15', 'Random' => '#fb923c', default => '#71717a' } }};">
                {{ $race }}
            </span>
        @endif
    </div>

    {{-- Flag --}}
    @if($country)
        <img src="{{ asset('images/country_flags/' . strtolower($country) . '.svg') }}"
             class="w-5 h-3.5 rounded-sm shrink-0 order-1" alt="{{ $country }}">
    @endif
@endif