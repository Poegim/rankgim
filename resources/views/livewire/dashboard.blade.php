<div class="w-full flex flex-col gap-6">

    {{-- ========== Row 1: Top 10 (full width) ========== --}}
    <livewire:dashboard.top-players />

    {{-- ========== Row 2: Events + Next match to predict ==========
         Layout adapts:
         - both present     → 50/50 grid
         - only one present → that one takes full width
         - none present     → row is hidden entirely
    --}}
    @php
        $hasEvents = $this->upcomingEvents->isNotEmpty();
        $hasNextMatch = $this->nextForecastMatches->isNotEmpty();
    @endphp

    @if($hasEvents || $hasNextMatch)
        <div @class([
            'grid gap-6',
            'grid-cols-1 xl:grid-cols-2' => $hasEvents && $hasNextMatch,
            'grid-cols-1'                => !($hasEvents && $hasNextMatch),
        ])>
            @if($hasEvents)
                <livewire:dashboard.upcoming-events />
            @endif

            @if($hasNextMatch)
                <livewire:dashboard.next-forecast-match />
            @endif
        </div>
    @endif

    {{-- ========== Row 3: Recent achievements (full width grid) ========== --}}
    <livewire:recent-achievements />

    {{-- Risers + Fallers + Hot streaks --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <livewire:dashboard.risers-and-fallers />
        </div>
        <livewire:dashboard.hot-streaks />
    </div>

    {{-- Most active + Biggest upsets + Most dominant --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <livewire:dashboard.most-active />
        <livewire:dashboard.biggest-upsets />
        <livewire:dashboard.most-dominant />
    </div>


    {{-- ========== Row 4: Reactions + Comments ========== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <livewire:dashboard.recent-reactions />
        <livewire:dashboard.recent-comments />
    </div>

</div>