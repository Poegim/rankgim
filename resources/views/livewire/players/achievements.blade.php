@php
$categoryLabels = [
    'games'     => '🎮 Games',
    'activity'  => '📅 Activity',
    'ranking'   => '🏆 Ranking',
    'rating'    => '📈 Rating',
    'streaks'   => '🔥 Streaks',
    'rivalry'   => '⚔️ Rivalry',
    'community' => '🌍 Community',
    'history'   => '🕰️ History',
    'drama'     => '🎭 Drama',
    'calendar'  => '📆 Calendar',
    'precision' => '🎯 Precision',
    'prestige'  => '💀 Prestige',
    'secret'    => '🔒 Secret',
];
@endphp

<div class="flex flex-col gap-3">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">Achievements</p>
    </div>

    {{-- Per-category unlocked/total counters --}}
    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
        @foreach($this->categoryCounts as $cat => $count)
        <div class="flex items-center gap-1.5">
            <span class="text-xs text-zinc-500">{{ $categoryLabels[$cat] ?? $cat }}</span>
            <span class="text-xs font-mono {{ $count['unlocked'] === $count['total'] ? 'text-green-400' : 'text-zinc-400' }}">
                {{ $count['unlocked'] }}/{{ $count['total'] }}
            </span>
        </div>
        @endforeach
    </div>

    @if($this->achievements->isEmpty())
        <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-6 text-center">
            <p class="text-zinc-500 text-sm">No achievements yet.</p>
        </div>
    @else

    {{-- Achievement grid sorted S → D --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
        @foreach($this->achievements as $a)
            <x-achievement-card
                :achievement="$a"
                :unlocked-at="$a['unlocked_at']"
                :total-players="$this->totalPlayers"
            />
        @endforeach
    </div>

    @endif
</div>