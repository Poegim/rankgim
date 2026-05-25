@props([
    'achievement',
    'unlockedAt'     => null,
    'totalPlayers'   => 0,
    'showHoldersBtn' => false,
])
@php
    // ─── Design decision ──────────────────────────────────────────────────
    // Achievement cards intentionally KEEP THEIR DARK AESTHETIC in both
    // light and dark modes. They are display artifacts (like Steam achievements,
    // PS trophies, MTG cards) — dramatic gradients, tier glow, and text-shadow
    // only work on dark backgrounds. The recent-achievements widget that wraps
    // these cards is themed normally (cream in light, zinc in dark) so the
    // cards read as "windows into artwork" rather than UI elements.
    //
    // Only addition in light mode: a subtle outer ring + drop shadow that
    // visually frames the card on parchment, so it doesn't look like a
    // misplaced fragment from another design.
    // ──────────────────────────────────────────────────────────────────────

    // Tier visual styles — kept inline to mirror AchievementCard::TIER_STYLES.
    // Identical in both themes (this is the artwork).
    $tierStyles = [
        's' => [
            'card'   => 'background: linear-gradient(145deg, #3d2200 0%, #1e1000 70%, #0a0500 100%);',
            'bar'    => 'background: linear-gradient(90deg, #854F0B, #FFD700, #EF9F27, #FFD700, #854F0B);',
            'tier'   => 'background: linear-gradient(145deg, #FFD700 0%, #EF9F27 100%); color: #1e1000;',
            'glow'   => 'box-shadow: 0 0 24px -4px #EF9F2780, inset 0 1px 0 0 #FFD70060;',
            'date'   => 'color: #EF9F27;',
            'cat'    => 'background: #EF9F2722; color: #FFD700; border: 1px solid #EF9F2755;',
            'name'   => 'color: #FFE56B; text-shadow: 0 0 18px #EF9F2755;',
            'desc'   => 'color: #F0C870;',
            'label'  => 'S',
        ],
        'a' => [
            'card'   => 'background: linear-gradient(145deg, #2e1000 0%, #180800 70%, #080300 100%);',
            'bar'    => 'background: linear-gradient(90deg, #712B13, #F0997B, #D85A30, #F0997B, #712B13);',
            'tier'   => 'background: linear-gradient(145deg, #F0997B 0%, #D85A30 100%); color: #180800;',
            'glow'   => 'box-shadow: 0 0 24px -4px #D85A3080, inset 0 1px 0 0 #F0997B60;',
            'date'   => 'color: #D85A30;',
            'cat'    => 'background: #D85A3022; color: #FF8C55; border: 1px solid #D85A3055;',
            'name'   => 'color: #FFB890; text-shadow: 0 0 18px #D85A3055;',
            'desc'   => 'color: #F0997B;',
            'label'  => 'A',
        ],
        'b' => [
            'card'   => 'background: linear-gradient(145deg, #231a4a 0%, #130e2a 70%, #080610 100%);',
            'bar'    => 'background: linear-gradient(90deg, #3C3489, #CECBF6, #7F77DD, #CECBF6, #3C3489);',
            'tier'   => 'background: linear-gradient(145deg, #CECBF6 0%, #7F77DD 100%); color: #130e2a;',
            'glow'   => 'box-shadow: 0 0 24px -4px #7F77DD80, inset 0 1px 0 0 #CECBF660;',
            'date'   => 'color: #AFA9EC;',
            'cat'    => 'background: #7F77DD22; color: #CECBF6; border: 1px solid #7F77DD55;',
            'name'   => 'color: #F0EDFF; text-shadow: 0 0 18px #7F77DD55;',
            'desc'   => 'color: #CECBF6;',
            'label'  => 'B',
        ],
        'c' => [
            'card'   => 'background: linear-gradient(145deg, #042240 0%, #021525 70%, #010810 100%);',
            'bar'    => 'background: linear-gradient(90deg, #0C447C, #B5D4F4, #378ADD, #B5D4F4, #0C447C);',
            'tier'   => 'background: linear-gradient(145deg, #B5D4F4 0%, #378ADD 100%); color: #021525;',
            'glow'   => 'box-shadow: 0 0 24px -4px #378ADD80, inset 0 1px 0 0 #B5D4F460;',
            'date'   => 'color: #85B7EB;',
            'cat'    => 'background: #378ADD22; color: #B5D4F4; border: 1px solid #378ADD55;',
            'name'   => 'color: #E5F0FF; text-shadow: 0 0 18px #378ADD55;',
            'desc'   => 'color: #B5D4F4;',
            'label'  => 'C',
        ],
        'd' => [
            'card'   => 'background: linear-gradient(145deg, #122808 0%, #091803 70%, #040c02 100%);',
            'bar'    => 'background: linear-gradient(90deg, #27500A, #C0DD97, #639922, #C0DD97, #27500A);',
            'tier'   => 'background: linear-gradient(145deg, #C0DD97 0%, #639922 100%); color: #091803;',
            'glow'   => 'box-shadow: 0 0 24px -4px #63992280, inset 0 1px 0 0 #C0DD9760;',
            'date'   => 'color: #97C459;',
            'cat'    => 'background: #63992222; color: #C0DD97; border: 1px solid #63992255;',
            'name'   => 'color: #E5F5C0; text-shadow: 0 0 18px #63992255;',
            'desc'   => 'color: #C0DD97;',
            'label'  => 'D',
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

{{-- Card wrapper.                                                              --}}
{{-- The achievement-card class on the outer div lets us add a subtle outer     --}}
{{-- frame in light mode via a sibling stylesheet at the end of this file.      --}}
{{-- This is the ONLY theme-aware bit — everything else stays dark on purpose.  --}}
<div
    class="achievement-card group rounded-xl p-3.5 pt-4 flex flex-col gap-2.5 relative overflow-visible h-full w-full transition-transform duration-200 hover:-translate-y-0.5 {{ $masked ? 'opacity-60' : '' }}"
    style="{{ $t['card'] }} border: 1.5px solid {{ $border }}; box-shadow: 0 4px 14px -6px {{ $border }}40;"
>
    {{-- Top gradient bar in tier color --}}
    <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl" style="{{ $t['bar'] }}"></div>

    {{-- Header row: big tier badge (visual anchor) + category pill / date / lock --}}
    <div class="flex items-start justify-between gap-2">

        {{-- Big tier badge — primary visual element of the card --}}
        <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0 font-mono"
             style="{{ $t['tier'] }} {{ $t['glow'] }} font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em;">
            {{ $t['label'] }}
        </div>

        {{-- Right column: category pill on top, date / lock below --}}
        <div class="flex flex-col items-end gap-1.5 min-w-0 flex-1">
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full whitespace-nowrap truncate max-w-full"
                  style="{{ $t['cat'] }}">
                {{ $catLabel }}
            </span>

            @if($unlockedAt)
                <span class="text-[11px] font-mono font-semibold" style="{{ $t['date'] }}">
                    {{ \Carbon\Carbon::parse($unlockedAt)->format('M Y') }}
                </span>
            @elseif($masked)
                <span class="text-base leading-none" style="color: #534AB7;">🔒</span>
            @endif
        </div>
    </div>

    {{-- Achievement name — bigger, bolder, with subtle tier-colored glow --}}
    <p class="text-base font-extrabold leading-snug mt-1" style="{{ $t['name'] }} letter-spacing: -0.015em;">
        {{ $a['name'] }}
    </p>

    {{-- Description — uses dedicated desc color for readability (no opacity hack) --}}
    @if(!empty($a['description']))
        <p class="text-[13px] leading-relaxed flex-1" style="{{ $t['desc'] }}">
            {{ $a['description'] }}
        </p>
    @elseif($masked)
        <p class="text-[13px] italic flex-1" style="color: #534AB7;">Hidden achievement</p>
    @endif

    {{-- Footer: owners count + lore badge + optional "who?" button --}}
    <div class="mt-auto pt-2 border-t flex items-center justify-between gap-1.5"
         style="border-color: {{ $border }}40;">

        <span class="text-[11px] font-mono font-semibold" style="{{ $t['date'] }}">
            <span class="font-bold">{{ $a['owners_count'] }}</span>
            <span class="opacity-75">{{ $a['owners_count'] === 1 ? 'player' : 'players' }}</span>
            @if($totalPlayers > 0)
                <span class="opacity-50">·</span>
                <span>{{ number_format(($a['owners_count'] / $totalPlayers) * 100, 2) }}%</span>
            @endif
        </span>

        <div class="flex items-center gap-1.5">
            {{-- Lore badge + modal — all inline, x-teleport renders modal on body --}}
            @if(!empty($a['lore']) && !$masked)
            <div x-data="{ open: false }">

                <button
                    @click.stop="open = true"
                    class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold cursor-pointer hover:opacity-100 transition-opacity"
                    style="{{ $t['tier'] }} opacity: 0.9;"
                >📖 lore</button>

                {{-- Modal teleported to body so it's not clipped by card overflow. --}}
                {{-- Modal backdrop is always black/70 — works in both themes since   --}}
                {{-- it's a "spotlight" effect, not a surface.                        --}}
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
                            class="relative z-10 w-full max-w-md rounded-2xl overflow-hidden"
                            style="{{ $t['card'] }} border: 1.5px solid {{ $border }}; box-shadow: 0 20px 60px -10px {{ $border }}60;"
                        >
                            {{-- Top bar --}}
                            <div class="absolute top-0 left-0 right-0 h-1" style="{{ $t['bar'] }}"></div>

                            <div class="p-6 pt-7 flex flex-col gap-4">

                                {{-- Header: big tier badge + category + close --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-14 rounded-lg flex items-center justify-center shrink-0 font-mono"
                                             style="{{ $t['tier'] }} {{ $t['glow'] }} font-size: 1.75rem; font-weight: 800;">
                                            {{ $t['label'] }}
                                        </div>
                                        <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full"
                                              style="{{ $t['cat'] }}">
                                            {{ $catLabel }}
                                        </span>
                                    </div>
                                    <button @click="open = false"
                                            class="w-7 h-7 flex items-center justify-center rounded-full text-xs font-bold cursor-pointer shrink-0"
                                            style="{{ $t['tier'] }}">✕</button>
                                </div>

                                {{-- Name --}}
                                <p class="text-xl font-extrabold leading-tight" style="{{ $t['name'] }} letter-spacing: -0.02em;">{{ $a['name'] }}</p>

                                {{-- Description --}}
                                @if(!empty($a['description']))
                                    <p class="text-sm leading-relaxed" style="{{ $t['desc'] }}">{{ $a['description'] }}</p>
                                @endif

                                {{-- Lore --}}
                                <div class="border-t pt-4" style="border-color: {{ $border }}40;">
                                    <p class="text-[11px] font-bold uppercase tracking-widest mb-2" style="{{ $t['date'] }}">📖 Lore</p>
                                    <p class="text-sm italic leading-relaxed" style="{{ $t['name'] }}">{{ $a['lore'] }}</p>
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

{{-- ────────────────────────────────────────────────────────────────────────
     Light-mode visual integration.
     Adds a subtle outer ring + softer drop shadow so cards visually attach
     to the parchment widget background instead of floating as misfit dark
     rectangles. Dark mode keeps the original neon-glow shadow.
     ──────────────────────────────────────────────────────────────────────── --}}
<style>
    :root:not(.dark) .achievement-card {
        /* Soft warm shadow that grounds the dark card on cream bg.            */
        /* Without this the card looks pasted-on rather than integrated.       */
        box-shadow:
            0 1px 0 0 rgba(212, 202, 176, 0.8),   /* travertine-300 hairline   */
            0 4px 16px -4px rgba(74, 64, 41, 0.18); /* warm umber drop shadow */
    }
</style>