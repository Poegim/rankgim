<div class="flex flex-col gap-4">
    <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Most time in top 3</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach ([
            1 => [
                'medal'       => '🥇',
                'cardBg'      => 'background: linear-gradient(160deg, #2a1f00 0%, #1a1207 100%)',
                'border'      => 'border-color: #ef9f27',
                'glow'        => '#ef9f2730',
                'rankColor'   => '#ef9f27',
                'divider'     => '#ef9f2740',
                'heroColor'   => '#fac775',
                'pillBg'      => '#ef9f2720',
                'pillColor'   => '#ef9f27',
                'pillBorder'  => '#ef9f2750',
            ],
            2 => [
                'medal'       => '🥈',
                'cardBg'      => 'background: linear-gradient(160deg, #1e1e1e 0%, #111111 100%)',
                'border'      => 'border-color: #888780',
                'glow'        => '#88878030',
                'rankColor'   => '#b4b2a9',
                'divider'     => '#88878040',
                'heroColor'   => '#d3d1c7',
                'pillBg'      => '#88878020',
                'pillColor'   => '#b4b2a9',
                'pillBorder'  => '#88878050',
            ],
            3 => [
                'medal'       => '🥉',
                'cardBg'      => 'background: linear-gradient(160deg, #1f0e05 0%, #120800 100%)',
                'border'      => 'border-color: #d85a30',
                'glow'        => '#d85a3030',
                'rankColor'   => '#d85a30',
                'divider'     => '#d85a3040',
                'heroColor'   => '#f0997b',
                'pillBg'      => '#d85a3020',
                'pillColor'   => '#f0997b',
                'pillBorder'  => '#d85a3050',
            ],
        ] as $rank => $s)

        @php $players = $this->podium->get($rank, collect()); @endphp

        <div class="rounded-xl border overflow-hidden relative"
             style="{{ $s['cardBg'] }}; {{ $s['border'] }}">

            {{-- Glow --}}
            <div class="absolute top-0 right-0 w-24 h-24 rounded-full pointer-events-none"
                 style="background: {{ $s['glow'] }}; filter: blur(30px); transform: translate(30%, -30%);"></div>

            {{-- Header --}}
            <div class="flex items-center gap-2 px-4 py-3 relative">
                <span class="text-xl leading-none">{{ $s['medal'] }}</span>
                <span class="text-xs font-semibold uppercase tracking-widest"
                      style="color: {{ $s['rankColor'] }}">Rank #{{ $rank }}</span>
            </div>

            <div style="height: 0.5px; background: {{ $s['divider'] }}; margin: 0 14px;"></div>

            {{-- Hero player (#1 in this column) --}}
            @if ($players->isNotEmpty())
            @php $hero = $players->first(); @endphp
            <div class="flex items-center gap-3 px-4 py-3 relative">
                <img src="{{ asset('images/country_flags/' . strtolower($hero->country_code) . '.svg') }}"
                     class="w-7 h-5 rounded-sm shrink-0">
                <a href="{{ route('players.show', ['id' => $hero->id, 'slug' => Str::slug($hero->name)]) }}"
                   class="text-base font-semibold flex-1 min-w-0 truncate hover:underline"
                   style="color: {{ $s['heroColor'] }}">{{ $hero->name }}</a>
                <span class="text-xs font-medium px-2 py-1 rounded-full shrink-0"
                      style="background: {{ $s['pillBg'] }}; color: {{ $s['pillColor'] }}; border: 0.5px solid {{ $s['pillBorder'] }}">
                    {{ $hero->months_count }}mo
                </span>
            </div>

            <div style="height: 0.5px; background: {{ $s['divider'] }}; margin: 0 14px;"></div>

            {{-- Rest --}}
            <div class="py-2">
                @foreach ($players->skip(1) as $row)
                <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
                   class="flex items-center gap-2 px-4 py-1.5 hover:bg-white/5 transition-colors group">
                    <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                         class="rounded-sm shrink-0" style="width: 18px; height: 12px;">
                    <span class="text-sm flex-1 min-w-0 truncate group-hover:text-zinc-200 transition-colors"
                          style="color: #a0a09a">{{ $row->name }}</span>
                    <span class="text-xs shrink-0" style="color: #606060">{{ $row->months_count }}mo</span>
                </a>
                @endforeach
            </div>
            @endif

        </div>
        @endforeach
    </div>
</div>