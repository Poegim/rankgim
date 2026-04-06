@php
$tierColors = [
    's' => ['bg' => '#FFD70020', 'border' => '#FFD70060', 'text' => '#FFD700', 'label' => 'S'],
    'a' => ['bg' => '#FF8C0020', 'border' => '#FF8C0060', 'text' => '#FF8C00', 'label' => 'A'],
    'b' => ['bg' => '#CC44CC20', 'border' => '#CC44CC60', 'text' => '#CC44CC', 'label' => 'B'],
    'c' => ['bg' => '#4488FF20', 'border' => '#4488FF60', 'text' => '#4488FF', 'label' => 'C'],
    'd' => ['bg' => '#44BB4420', 'border' => '#44BB4460', 'text' => '#44BB44', 'label' => 'D'],
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

<div class="flex flex-col gap-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">Achievements</p>
        <span class="text-xs text-zinc-500 font-mono">
            {{ $this->achievements->flatten(1)->count() }} / {{ $this->totalCount }}
        </span>
    </div>

    @if($this->achievements->isEmpty())
        <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-6 text-center">
            <p class="text-zinc-500 text-sm">No achievements yet.</p>
        </div>
    @else

    {{-- Categories --}}
    @foreach($this->achievements as $category => $items)
    <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden">

        {{-- Category header --}}
        <div class="px-4 py-2.5 border-b border-zinc-700/40">
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                {{ $categoryLabels[$category] ?? $category }}
            </p>
        </div>

        {{-- Achievements grid --}}
        <div class="divide-y divide-zinc-700/30">
            @foreach($items as $a)
            @php $t = $tierColors[$a['tier']]; @endphp
            <div class="flex items-center gap-3 px-4 py-3">

                {{-- Tier badge --}}
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 font-black text-sm"
                     style="background: {{ $t['bg'] }}; border: 1px solid {{ $t['border'] }}; color: {{ $t['text'] }}">
                    {{ $t['label'] }}
                </div>

                {{-- Name + description --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-zinc-100 truncate">
                        @if($a['secret'])
                            🔒 {{ $a['name'] }}
                        @else
                            {{ $a['name'] }}
                        @endif
                    </p>
                    <p class="text-xs text-zinc-500 truncate">{{ $a['description'] }}</p>
                </div>

                {{-- Unlocked date --}}
                <span class="text-xs text-zinc-600 shrink-0 font-mono">
                    {{ \Carbon\Carbon::parse($a['unlocked_at'])->format('M Y') }}
                </span>

            </div>
            @endforeach
        </div>

    </div>
    @endforeach

    @endif
</div>