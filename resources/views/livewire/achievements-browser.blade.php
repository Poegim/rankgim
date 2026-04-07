@use('Illuminate\Support\Str')
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

$categories = array_keys($categoryLabels);
$tiers      = ['s', 'a', 'b', 'c', 'd'];
@endphp

<div class="flex flex-col gap-6">

    {{-- Page header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Achievements</h1>
            <p class="text-sm text-zinc-500 mt-0.5">
                {{ count(config('achievements')) }} total
                @if($this->isAdmin)
                    <span class="ml-2 text-xs text-amber-400 font-semibold uppercase tracking-wider">· Admin view</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Filters + sort --}}
    <div class="flex flex-wrap items-center gap-2">

        {{-- Category filter --}}
        <div class="flex flex-wrap gap-1">
            <button
                wire:click="$set('filterCategory', '')"
                class="px-2.5 py-1 rounded-md text-xs font-semibold transition-colors
                    {{ $filterCategory === '' ? 'bg-indigo-500 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700' }}"
            >All</button>
            @foreach($categories as $cat)
            <button
                wire:click="$set('filterCategory', '{{ $cat }}')"
                class="px-2.5 py-1 rounded-md text-xs font-semibold transition-colors
                    {{ $filterCategory === $cat ? 'bg-indigo-500 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700' }}"
            >{{ $categoryLabels[$cat] }}</button>
            @endforeach
        </div>

        <div class="w-px h-5 bg-zinc-700 hidden sm:block"></div>

        {{-- Tier filter --}}
        <div class="flex gap-1">
            <button
                wire:click="$set('filterTier', '')"
                class="px-2.5 py-1 rounded-md text-xs font-bold transition-colors
                    {{ $filterTier === '' ? 'bg-zinc-600 text-white' : 'text-zinc-500 hover:text-zinc-200 hover:bg-zinc-700' }}"
            >All tiers</button>
            @foreach($tiers as $tier)
            @php $ts = $tierStyles[$tier]; @endphp
            <button
                wire:click="$set('filterTier', '{{ $tier }}')"
                class="w-7 h-7 rounded-md text-xs font-bold transition-opacity
                    {{ $filterTier === $tier ? 'opacity-100 ring-2 ring-white/30' : 'opacity-60 hover:opacity-100' }}"
                style="{{ $ts['tier'] }}"
            >{{ strtoupper($tier) }}</button>
            @endforeach
        </div>

        <div class="w-px h-5 bg-zinc-700 hidden sm:block"></div>

        {{-- Sort --}}
        <div class="flex gap-1">
            @foreach(['category' => 'By category', 'tier' => 'By tier', 'popularity' => 'By popularity'] as $val => $label)
            <button
                wire:click="$set('sortBy', '{{ $val }}')"
                class="px-2.5 py-1 rounded-md text-xs font-semibold transition-colors
                    {{ $sortBy === $val ? 'bg-zinc-600 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700' }}"
            >{{ $label }}</button>
            @endforeach
        </div>

    </div>

    {{-- Achievements grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2">

        @forelse($this->achievements as $a)
        @php
            $t      = $tierStyles[$a['tier']] ?? $tierStyles['d'];
            $border = $a['masked'] ? '#534AB7' : ($categoryBorders[$a['category']] ?? '#52525b');
        @endphp

        <div
            class="rounded-xl p-3 flex flex-col gap-1.5 relative overflow-hidden {{ $a['masked'] ? 'opacity-60' : '' }}"
            style="{{ $t['card'] }} border: 1.5px solid {{ $border }};"
        >
            {{-- Top bar --}}
            <div class="absolute top-0 left-0 right-0 h-1" style="{{ $t['bar'] }}"></div>

            {{-- Tier badge --}}
            <div class="flex items-center justify-between">
                <div class="w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold shrink-0"
                     style="{{ $t['tier'] }}">
                    {{ $t['label'] }}
                </div>
                @if($a['masked'])
                    <span class="text-xs" style="color: #534AB7;">🔒</span>
                @endif
            </div>

            {{-- Category --}}
            <span class="text-xs font-semibold uppercase tracking-widest" style="{{ $t['cat'] }}">
                {{ $categoryLabels[$a['category']] ?? $a['category'] }}
            </span>

            {{-- Name --}}
            <p class="text-xs font-semibold leading-tight" style="{{ $t['name'] }}">
                {{ $a['name'] }}
            </p>

            {{-- Description --}}
            @if($a['description'])
                <p class="text-xs leading-relaxed flex-1" style="{{ $t['desc'] }}">
                    {{ $a['description'] }}
                </p>
            @elseif($a['masked'])
                <p class="text-xs italic" style="color: #534AB7;">Hidden achievement</p>
            @endif

            {{-- Owners count + "who has it" button --}}
            <div class="mt-auto pt-1.5 border-t flex items-center justify-between gap-1"
                 style="{{ $t['date'] }}; border-color: {{ $border }}30;">
                <span class="text-xs font-mono">
                    {{ $a['owners_count'] }} {{ $a['owners_count'] === 1 ? 'player' : 'players' }}
                    @if($this->totalPlayers > 0)
                        · {{ $a['pct'] }}%
                    @endif
                </span>
                @if($a['owners_count'] > 0 && $this->isAdmin)
                <button
                    wire:click="openHolders('{{ $a['key'] }}')"
                    class="text-xs underline underline-offset-2 opacity-60 hover:opacity-100 transition-opacity shrink-0"
                    style="{{ $t['date'] }}"
                    title="See who has this"
                >who?</button>
                @endif
            </div>

        </div>

        @empty
        <div class="col-span-full rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-8 text-center">
            <p class="text-zinc-500">No achievements match the current filters.</p>
        </div>
        @endforelse

    </div>

    {{-- Holders modal --}}
    @if($holdersKey)
    @php
        $def = config('achievements')[$holdersKey] ?? null;
        $isSecret = $def['secret'] ?? false;
        $holdersList = $this->holders;
    @endphp
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-data
        x-on:keydown.escape.window="$wire.closeHolders()"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/70 backdrop-blur-sm"
            wire:click="closeHolders"
        ></div>

        {{-- Modal --}}
        <div class="relative z-10 w-full max-w-md rounded-2xl border border-zinc-700 bg-zinc-900 shadow-2xl overflow-hidden">

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-800">
                <div>
                    @if($def && (!$isSecret || $this->isAdmin))
                        <p class="font-bold text-white">{{ $def['name'] }}</p>
                        <p class="text-xs text-zinc-400 mt-0.5">{{ $def['description'] }}</p>
                    @else
                        <p class="font-bold text-purple-400">🔒 Hidden achievement</p>
                    @endif
                </div>
                <button
                    wire:click="closeHolders"
                    class="text-zinc-500 hover:text-white transition-colors ml-4 shrink-0"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Holders list with custom scrollbar and fade --}}
            <div class="relative">
                {{-- Fade at the bottom to hint there's more content --}}
                <div class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-zinc-900 to-transparent pointer-events-none z-10"></div>

                <div
                    class="max-h-72 overflow-y-auto divide-y divide-zinc-800/60"
                    style="scrollbar-width: thin; scrollbar-color: #3f3f46 transparent;"
                >
                    <style>
                        .holders-scroll::-webkit-scrollbar { width: 4px; }
                        .holders-scroll::-webkit-scrollbar-track { background: transparent; }
                        .holders-scroll::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 99px; }
                        .holders-scroll::-webkit-scrollbar-thumb:hover { background: #52525b; }
                    </style>
                    @forelse($holdersList as $i => $holder)
                    <a
                        href="{{ route('players.show', ['id' => $holder['id'], 'slug' => $holder['slug']]) }}"
                        wire:navigate
                        wire:click="closeHolders"
                        class="holders-scroll flex items-center gap-3 px-5 py-2.5 hover:bg-zinc-800/60 transition-colors"
                    >
                        {{-- Position number --}}
                        <span class="text-xs font-mono text-zinc-600 w-5 text-right shrink-0">{{ $i + 1 }}</span>
                        <img
                            src="{{ asset('images/country_flags/' . strtolower($holder['country_code']) . '.svg') }}"
                            class="w-7 h-5 rounded-sm shrink-0"
                        >
                        <span class="font-semibold text-sm text-white flex-1">{{ $holder['name'] }}</span>
                        <span class="text-xs font-mono text-zinc-500 shrink-0">
                            {{ \Carbon\Carbon::parse($holder['unlocked_at'])->format('M Y') }}
                        </span>
                    </a>
                    @empty
                    <div class="px-5 py-6 text-center text-zinc-500 text-sm">No one has this yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Modal footer --}}
            <div class="px-5 py-3 border-t border-zinc-800 text-xs text-zinc-500">
                {{ $holdersList->count() }} {{ $holdersList->count() === 1 ? 'player' : 'players' }} unlocked this
            </div>

        </div>
    </div>
    @endif

</div>