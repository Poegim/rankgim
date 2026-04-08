@use('Illuminate\Support\Str')
@php
    $raceColors = [
        'Terran'  => 'text-blue-400',
        'Zerg'    => 'text-purple-400',
        'Protoss' => 'text-yellow-400',
        'Random'  => 'text-orange-400',
        'Unknown' => 'text-zinc-400',
    ];
    $raceBorders = [
        'Terran'  => 'border-l-blue-500',
        'Zerg'    => 'border-l-purple-500',
        'Protoss' => 'border-l-yellow-500',
        'Random'  => 'border-l-orange-500',
        'Unknown' => 'border-l-zinc-600',
    ];
@endphp

<div>
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">🏆 Top 10</p>
        <a href="{{ route('rankings.index') }}" class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors">View full rankings →</a>
    </div>

    {{-- Top 3 podium cards --}}
    <div class="flex flex-col gap-2 mb-2">
        @foreach($this->players->take(3) as $index => $row)
        @php
            $winRatio = $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0;
            $change   = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
            $medals   = ['🥇', '🥈', '🥉'];
            $gradients = [
                'from-amber-500/10 to-transparent',
                'from-zinc-400/8 to-transparent',
                'from-orange-700/10 to-transparent',
            ];
            $accent = match($row->player->race) {
                'Terran'  => '#3b82f6',
                'Zerg'    => '#a855f7',
                'Protoss' => '#eab308',
                'Random'  => '#f97316',
                default   => '#52525b',
            };
        @endphp
        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
           class="group relative flex items-center px-4 py-3 rounded-xl border border-zinc-700/60 bg-gradient-to-r {{ $gradients[$index] }} hover:bg-zinc-800/60 transition-all duration-150 overflow-hidden"
           style="border-left: 3px solid {{ $accent }};">
            {{-- Flag watermark --}}
            <div class="absolute inset-0 overflow-hidden rounded-xl pointer-events-none">
                <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                     class="absolute right-0 top-0 h-full w-auto opacity-[0.06] object-cover"
                     style="-webkit-mask-image: linear-gradient(to left, black 20%, transparent 80%); mask-image: linear-gradient(to left, black 20%, transparent 80%);">
            </div>
            <div class="relative z-10 flex items-center justify-between w-full gap-3">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <span class="text-lg sm:text-xl shrink-0">{{ $medals[$index] }}</span>
                    <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-7 h-5 rounded-sm shrink-0">
                    <span class="font-bold text-sm sm:text-base text-white group-hover:underline truncate">{{ $row->player->name }}</span>
                    <span class="text-xs shrink-0 hidden sm:inline {{ $raceColors[$row->player->race] ?? 'text-zinc-400' }}">{{ $row->player->race }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="font-mono text-lg sm:text-xl font-black text-white">{{ $row->rating }}</span>
                    @if($change !== null && $change != 0)
                        <span class="hidden sm:inline font-mono text-xs font-semibold {{ $change > 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $change > 0 ? '▲' : '▼' }}{{ abs($change) }}
                        </span>
                    @endif
                    <span class="text-xs font-semibold w-10 text-right {{ $winRatio >= 50 ? 'text-green-400' : 'text-red-400' }}">{{ $winRatio }}%</span>
                    <div class="hidden md:flex items-center gap-1 w-20 justify-end text-xs">
                        <span class="text-green-400">{{ $row->wins }}W</span>
                        <span class="text-zinc-600">/</span>
                        <span class="text-red-400">{{ $row->losses }}L</span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- 4–10 compact list --}}
    <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/20 divide-y divide-zinc-700/40 overflow-hidden">
        @foreach($this->players->slice(3) as $row)
        @php
            $winRatio = $row->games_played > 0 ? round(($row->wins / $row->games_played) * 100) : 0;
            $change   = $row->prev_rating !== null ? $row->rating - $row->prev_rating : null;
        @endphp
        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
           class="flex items-center px-3 sm:px-4 py-2.5 hover:bg-zinc-800/60 transition-colors group border-l-4 {{ $raceBorders[$row->player->race] ?? 'border-l-zinc-600' }}">
            <div class="flex items-center justify-between w-full gap-2">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <span class="font-mono text-xs text-zinc-500 w-5 text-right shrink-0">{{ $loop->iteration + 3 }}</span>
                    <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm shrink-0">
                    <span class="font-semibold text-sm text-zinc-100 group-hover:underline truncate">{{ $row->player->name }}</span>
                    <span class="text-xs shrink-0 hidden sm:inline {{ $raceColors[$row->player->race] ?? 'text-zinc-400' }}">{{ $row->player->race }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="font-mono text-sm font-bold text-zinc-100 w-14 text-right">{{ $row->rating }}</span>
                    @if($change !== null && $change != 0)
                        <span class="hidden sm:inline font-mono text-xs font-medium w-12 text-right {{ $change > 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $change > 0 ? '▲' : '▼' }}{{ abs($change) }}
                        </span>
                    @endif
                    <span class="text-xs font-semibold w-10 text-right {{ $winRatio >= 50 ? 'text-green-400' : 'text-red-400' }}">{{ $winRatio }}%</span>
                    <div class="hidden md:flex items-center gap-1 w-20 justify-end text-xs">
                        <span class="text-green-400">{{ $row->wins }}W</span>
                        <span class="text-zinc-600">/</span>
                        <span class="text-red-400">{{ $row->losses }}L</span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>