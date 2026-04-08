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

    {{-- Filters + sort bar --}}
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

            {{-- Pass show-holders-btn=true so the slot (who? button) is rendered --}}
            <x-achievement-card
                :achievement="$a"
                :total-players="$this->totalPlayers"
                :show-holders-btn="$this->isAdmin"
            >
                {{-- "who?" button lives here as a slot — wire:click stays in the Livewire context --}}
                <button
                    wire:click="openHolders('{{ $a['key'] }}')"
                    class="text-xs underline underline-offset-2 opacity-60 hover:opacity-100 transition-opacity shrink-0"
                    style="color: inherit;"
                    title="See who has this"
                >who?</button>
            </x-achievement-card>

        @empty
            <div class="col-span-full rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-8 text-center">
                <p class="text-zinc-500">No achievements match the current filters.</p>
            </div>
        @endforelse

    </div>

    {{-- Holders modal --}}
    @if($holdersKey)
    @php
        $def      = config('achievements')[$holdersKey] ?? null;
        $isSecret = $def['secret'] ?? false;
        $defName  = ($isSecret && !$this->isAdmin) ? '???' : ($def['name'] ?? $holdersKey);
        $modalTier = $def['tier'] ?? 'd';
        $mt       = $tierStyles[$modalTier] ?? $tierStyles['d'];
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

        {{-- Modal panel --}}
        <div class="relative z-10 w-full max-w-md rounded-2xl p-5 flex flex-col gap-4 overflow-hidden"
             style="{{ $mt['card'] }} border: 1.5px solid {{ $categoryBorders[$def['category'] ?? ''] ?? '#52525b' }};">

            {{-- Top bar --}}
            <div class="absolute top-0 left-0 right-0 h-1" style="{{ $mt['bar'] }}"></div>

            {{-- Modal header --}}
            <div class="flex items-start justify-between gap-2 pt-1">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold"
                              style="{{ $mt['tier'] }}">{{ strtoupper($modalTier) }}</span>
                        <span class="text-xs font-semibold uppercase tracking-widest" style="{{ $mt['cat'] }}">
                            {{ $categoryLabels[$def['category'] ?? ''] ?? '' }}
                        </span>
                    </div>
                    <p class="font-bold text-base" style="{{ $mt['name'] }}">{{ $defName }}</p>
                    @if($def['description'] ?? false)
                        <p class="text-xs mt-0.5" style="{{ $mt['desc'] }}">{{ $def['description'] }}</p>
                    @endif
                </div>
                <button wire:click="closeHolders"
                        class="text-zinc-500 hover:text-zinc-200 transition-colors shrink-0 mt-0.5"
                        title="Close">✕</button>
            </div>

            {{-- Holders list --}}
            <div class="flex flex-col gap-1 max-h-72 overflow-y-auto pr-1">
                @forelse($this->holders as $h)
                <a href="{{ route('players.show', ['id' => $h['id'], 'slug' => $h['slug']]) }}"
                   wire:navigate
                   class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-white/5 transition-colors">
                    <div class="flex items-center gap-2">
                        @if($h['country_code'])
                            <img src="https://flagcdn.com/w40/{{ strtolower($h['country_code']) }}.png"
                                 class="w-5 h-3.5 rounded-sm object-cover"
                                 alt="{{ $h['country_code'] }}">
                        @endif
                        <span class="text-sm font-medium text-white">{{ $h['name'] }}</span>
                    </div>
                    <span class="text-xs font-mono" style="{{ $mt['date'] }}">
                        {{ \Carbon\Carbon::parse($h['unlocked_at'])->format('M Y') }}
                    </span>
                </a>
                @empty
                    <p class="text-sm text-zinc-500 text-center py-4">No holders yet.</p>
                @endforelse
            </div>

        </div>
    </div>
    @endif

</div>