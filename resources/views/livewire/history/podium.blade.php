<div class="flex flex-col gap-4">
    <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Most time in top 3</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ([
            1 => ['🥇', 'border-amber-500/30',  'from-amber-500/10',  'text-amber-400'],
            2 => ['🥈', 'border-zinc-400/30',   'from-zinc-400/10',   'text-zinc-300'],
            3 => ['🥉', 'border-orange-700/30', 'from-orange-700/10', 'text-orange-500'],
        ] as $rank => [$medal, $border, $gradient, $color])

        <div class="rounded-xl border {{ $border }} bg-gradient-to-b {{ $gradient }} to-transparent p-4 flex flex-col gap-3">

            {{-- Header --}}
            <div class="flex items-center gap-2">
                <span class="text-lg">{{ $medal }}</span>
                <span class="text-xs font-semibold text-zinc-400 uppercase tracking-widest">Rank #{{ $rank }}</span>
            </div>

            {{-- Players --}}
            @forelse ($this->podium->get($rank, collect())->take(5) as $i => $row)
            <div class="flex items-center gap-3 {{ $i > 0 ? 'opacity-50' : '' }}">
                <img
                    src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                    class="w-7 h-5 rounded-sm shrink-0"
                >
                <span class="font-semibold text-sm text-zinc-100 truncate flex-1">{{ $row->name }}</span>
                <span class="font-mono text-xs {{ $color }} shrink-0">{{ $row->months_count }}mo</span>
            </div>
            @empty
            <p class="text-xs text-zinc-500">No data yet</p>
            @endforelse

        </div>
        @endforeach
    </div>
</div>