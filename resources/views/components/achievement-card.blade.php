@props([
    'achievement',
    'unlockedAt'     => null,
    'totalPlayers'   => 0,
    'showHoldersBtn' => false,
])
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

    $a        = $achievement;
    $masked   = $a['masked'] ?? false;
    $t        = $tierStyles[$a['tier']] ?? $tierStyles['d'];
    $border   = $masked
        ? '#534AB7'
        : ($categoryBorders[$a['category'] ?? ''] ?? '#52525b');
    $catLabel = $categoryLabels[$a['category'] ?? ''] ?? ($a['category'] ?? '');
@endphp

<div
    class="rounded-xl p-3 flex flex-col gap-2 relative overflow-hidden {{ $masked ? 'opacity-60' : '' }}"
    style="{{ $t['card'] }} border: 1.5px solid {{ $border }};"
>
    {{-- Top gradient bar in tier color --}}
    <div class="absolute top-0 left-0 right-0 h-1" style="{{ $t['bar'] }}"></div>

    {{-- Tier badge + optional date (player profile) or lock icon (masked) --}}
    <div class="flex items-center justify-between">
        <div class="w-7 h-7 rounded-md flex items-center justify-center text-sm font-bold shrink-0"
             style="{{ $t['tier'] }}">
            {{ $t['label'] }}
        </div>

        @if($unlockedAt)
            <span class="text-xs font-mono" style="{{ $t['date'] }}">
                {{ \Carbon\Carbon::parse($unlockedAt)->format('M Y') }}
            </span>
        @elseif($masked)
            <span class="text-xs" style="color: #534AB7;">🔒</span>
        @endif
    </div>

    {{-- Category label --}}
    <span class="text-xs font-semibold uppercase tracking-widest" style="{{ $t['cat'] }}">
        {{ $catLabel }}
    </span>

    {{-- Achievement name --}}
    <p class="text-sm font-bold leading-tight" style="{{ $t['name'] }}">
        {{ $a['name'] }}
    </p>

    {{-- Description — hidden for masked secrets --}}
    @if(!empty($a['description']))
        <p class="text-xs leading-relaxed flex-1 opacity-80" style="{{ $t['name'] }}">
            {{ $a['description'] }}
        </p>
    @elseif($masked)
        <p class="text-xs italic" style="color: #534AB7;">Hidden achievement</p>
    @endif

    {{-- Footer: owners count + lore badge + optional "who?" button --}}
    <div class="mt-auto pt-1.5 border-t flex items-center justify-between gap-1"
         style="{{ $t['date'] }}; border-color: {{ $border }}30;">

        <span class="text-xs font-mono">
            {{ $a['owners_count'] }} {{ $a['owners_count'] === 1 ? 'player' : 'players' }}
            @if($totalPlayers > 0)
                · {{ number_format(($a['owners_count'] / $totalPlayers) * 100, 2) }}%
            @endif
        </span>

        <div class="flex items-center gap-1.5">
            {{-- Lore badge + modal — all inline, x-teleport renders modal on body --}}
            @if(!empty($a['lore']) && !$masked)
            <div x-data="{ open: false }">

                <button
                    @click.stop="open = true"
                    class="flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-semibold cursor-pointer hover:opacity-100 transition-opacity"
                    style="{{ $t['tier'] }}; opacity: 0.85;"
                >📖 lore</button>

                {{-- Modal teleported to body so it's not clipped by card overflow:hidden --}}
                <template x-teleport="body">
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="open = false"
                        @keydown.escape.window="open = false"
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        style="display:none;"
                    >
                        {{-- Backdrop --}}
                        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

                        {{-- Panel --}}
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                            @click.stop
                            class="relative z-10 w-full max-w-sm rounded-2xl overflow-hidden"
                            style="{{ $t['card'] }} border: 1.5px solid {{ $border }};"
                        >
                            {{-- Top bar --}}
                            <div class="absolute top-0 left-0 right-0 h-1" style="{{ $t['bar'] }}"></div>

                            <div class="p-5 pt-6 flex flex-col gap-3">

                                {{-- Category + close --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold uppercase tracking-widest" style="{{ $t['cat'] }}">{{ $catLabel }}</span>
                                    <button @click="open = false" class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold cursor-pointer" style="{{ $t['tier'] }}">✕</button>
                                </div>

                                {{-- Name --}}
                                <p class="text-base font-bold leading-tight" style="{{ $t['name'] }}">{{ $a['name'] }}</p>

                                {{-- Description --}}
                                @if(!empty($a['description']))
                                    <p class="text-sm leading-relaxed" style="{{ $t['name'] }}">{{ $a['description'] }}</p>
                                @endif

                                {{-- Lore --}}
                                <div class="border-t pt-3" style="border-color: {{ $border }}40;">
                                    <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="{{ $t['name'] }}">📖 Lore</p>
                                    <p class="text-sm italic leading-relaxed opacity-80" style="{{ $t['name'] }}">{{ $a['lore'] }}</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </template>
            </div>
            @endif

            {{-- "who?" button slot — only rendered in browser admin view --}}
            @if($showHoldersBtn && ($a['owners_count'] ?? 0) > 0)
                {{ $slot }}
            @endif
        </div>

    </div>

</div>