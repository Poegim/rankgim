@php
$tierStyles = [
    's' => [
        'card'  => 'background: linear-gradient(145deg, #3d2200 0%, #1e1000 70%, #0a0500 100%);',
        'bar'   => 'background: linear-gradient(90deg, #854F0B, #FFD700, #EF9F27, #FFD700, #854F0B);',
        'tier'  => 'background: #EF9F27; color: #1e1000;',
        'date'  => 'color: #BA7517;',
        'cat'   => 'color: #EF9F27;',
        'name'  => 'color: #FFD700;',
        'desc'  => 'color: #EF9F27;',
        'label' => 'S',
    ],
    'a' => [
        'card'  => 'background: linear-gradient(145deg, #2e1000 0%, #180800 70%, #080300 100%);',
        'bar'   => 'background: linear-gradient(90deg, #712B13, #F0997B, #D85A30, #F0997B, #712B13);',
        'tier'  => 'background: #D85A30; color: #180800;',
        'date'  => 'color: #993C1D;',
        'cat'   => 'color: #F0997B;',
        'name'  => 'color: #FF8C55;',
        'desc'  => 'color: #D85A30;',
        'label' => 'A',
    ],
    'b' => [
        'card'  => 'background: linear-gradient(145deg, #231a4a 0%, #130e2a 70%, #080610 100%);',
        'bar'   => 'background: linear-gradient(90deg, #3C3489, #CECBF6, #7F77DD, #CECBF6, #3C3489);',
        'tier'  => 'background: #7F77DD; color: #130e2a;',
        'date'  => 'color: #534AB7;',
        'cat'   => 'color: #CECBF6;',
        'name'  => 'color: #E8E4FF;',
        'desc'  => 'color: #AFA9EC;',
        'label' => 'B',
    ],
    'c' => [
        'card'  => 'background: linear-gradient(145deg, #042240 0%, #021525 70%, #010810 100%);',
        'bar'   => 'background: linear-gradient(90deg, #0C447C, #B5D4F4, #378ADD, #B5D4F4, #0C447C);',
        'tier'  => 'background: #378ADD; color: #021525;',
        'date'  => 'color: #185FA5;',
        'cat'   => 'color: #B5D4F4;',
        'name'  => 'color: #D4EAFF;',
        'desc'  => 'color: #85B7EB;',
        'label' => 'C',
    ],
    'd' => [
        'card'  => 'background: linear-gradient(145deg, #122808 0%, #091803 70%, #040c02 100%);',
        'bar'   => 'background: linear-gradient(90deg, #27500A, #C0DD97, #639922, #C0DD97, #27500A);',
        'tier'  => 'background: #639922; color: #091803;',
        'date'  => 'color: #3B6D11;',
        'cat'   => 'color: #C0DD97;',
        'name'  => 'color: #D8F0A0;',
        'desc'  => 'color: #97C459;',
        'label' => 'D',
    ],
];

$categoryBorders = [
    'games'     => '#e24b4a',
    'activity'  => '#378ADD',
    'ranking'   => '#EF9F27',
    'rating'    => '#1D9E75',
    'streaks'   => '#D85A30',
    'rivalry'   => '#7F77DD',
    'community' => '#D4537E',
    'history'   => '#888780',
    'drama'     => '#F0997B',
    'calendar'  => '#85B7EB',
    'precision' => '#97C459',
    'prestige'  => '#FAC775',
    'secret'    => '#534AB7',
];

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

    @php
$ratingGroup = $this->achievements->filter(fn($a) => $a['group'] === 'rating_milestone')->values();
@endphp
<pre style="font-size:10px">{{ json_encode($ratingGroup->map(fn($a) => ['key' => $a['key'], 'tier' => $a['tier']])) }}</pre>

    @if($this->achievements->isEmpty())
        <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-6 text-center">
            <p class="text-zinc-500 text-sm">No achievements yet.</p>
        </div>
    @else

    {{-- Grid sorted S→D --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">

        @foreach($this->achievements as $a)
        @php
            $t      = $tierStyles[$a['tier']] ?? $tierStyles['d'];
            $border = $categoryBorders[$a['category']] ?? '#52525b';
            $pct    = $this->totalPlayers > 0
                ? round(($a['owners_count'] / $this->totalPlayers) * 100)
                : 0;
        @endphp

        <div class="rounded-xl p-3 flex flex-col gap-1.5 relative overflow-hidden"
             style="{{ $t['card'] }} border: 1.5px solid {{ $border }};">

            {{-- Top bar in tier color --}}
            <div class="absolute top-0 left-0 right-0 h-1"
                 style="{{ $t['bar'] }}"></div>

            {{-- Tier badge + date --}}
            <div class="flex items-center justify-between">
                <div class="w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold shrink-0"
                     style="{{ $t['tier'] }}">
                    {{ $t['label'] }}
                </div>
                <span class="text-xs font-mono" style="{{ $t['date'] }}">
                    {{ \Carbon\Carbon::parse($a['unlocked_at'])->format('M Y') }}
                </span>
            </div>

            {{-- Category --}}
            <span class="text-xs font-semibold uppercase tracking-widest"
                  style="{{ $t['cat'] }}">
                {{ $categoryLabels[$a['category']] ?? $a['category'] }}
            </span>

            {{-- Name --}}
            <p class="text-xs font-semibold leading-tight" style="{{ $t['name'] }}">
                {{ $a['name'] }}
            </p>

            {{-- Description --}}
            <p class="text-xs leading-relaxed" style="{{ $t['desc'] }}">
                {{ $a['description'] }}
            </p>

            {{-- Owners --}}
            <p class="text-xs mt-auto pt-1.5 border-t"
               style="{{ $t['date'] }}; border-color: {{ $border }}30;">
                {{ $a['owners_count'] }} {{ $a['owners_count'] === 1 ? 'player' : 'players' }} · {{ $pct }}%
            </p>

        </div>
        @endforeach

    </div>

    @endif
</div>