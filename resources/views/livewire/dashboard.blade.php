<div class="w-full flex flex-col gap-6 px-4 sm:px-6 lg:px-8">

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
            'grid-cols-1 md:grid-cols-2' => $hasEvents && $hasNextMatch,
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

    {{-- ========== Row 4: Reactions + Comments ========== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <livewire:dashboard.recent-reactions />
        <livewire:dashboard.recent-comments />
    </div>

</div>