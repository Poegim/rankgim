<div class="flex flex-col gap-4">
    <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Most time in top 10</h2>

    <div class="rounded-xl overflow-hidden relative" style="background: linear-gradient(160deg, #0a1f18 0%, #060f0c 100%); border: 0.5px solid #1d9e7550;">

        {{-- Glow --}}
        <div class="absolute pointer-events-none" style="top: -40px; right: -40px; width: 120px; height: 120px; border-radius: 50%; background: #1d9e7520; filter: blur(40px);"></div>

        {{-- Header row --}}
        <div class="grid px-4 py-2.5" style="grid-template-columns: 2rem 1fr 4.5rem 4rem; border-bottom: 0.5px solid #1d9e7530;">
            <span class="text-xs font-semibold uppercase tracking-widest" style="color: #1d9e75">#</span>
            <span class="text-xs font-semibold uppercase tracking-widest" style="color: #1d9e75">Player</span>
            <span class="text-xs font-semibold uppercase tracking-widest text-right" style="color: #1d9e75">Race</span>
            <span class="text-xs font-semibold uppercase tracking-widest text-right" style="color: #1d9e75">Months</span>
        </div>

        {{-- Rows --}}
        @foreach ($this->leaders as $i => $row)
        <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
           class="grid items-center px-4 py-2.5 group transition-colors hover:bg-white/5"
           style="grid-template-columns: 2rem 1fr 4.5rem 4rem; {{ !$loop->last ? 'border-bottom: 0.5px solid #1d9e7515;' : '' }}">

            {{-- Rank number --}}
            <span class="text-sm font-mono" style="color: #1d9e7560">{{ $i + 1 }}</span>

            {{-- Flag + name --}}
            <div class="flex items-center gap-2.5 min-w-0">
                <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                     class="rounded-sm shrink-0" style="width: 22px; height: 15px;">
                <span class="text-sm font-medium truncate group-hover:text-white transition-colors"
                      style="color: #5dcaa5">{{ $row->name }}</span>
            </div>

            {{-- Race --}}
            <span class="text-xs text-right shrink-0" style="color: {{
                match($row->race) {
                    'Terran'  => '#3b82f6',
                    'Zerg'    => '#a855f7',
                    'Protoss' => '#eab308',
                    'Random'  => '#f97316',
                    default   => '#52525b',
                }
            }}">{{ $row->race }}</span>

            {{-- Months pill --}}
            <div class="flex justify-end">
                <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                      style="background: #1d9e7520; color: #5dcaa5; border: 0.5px solid #1d9e7540">
                    {{ $row->months_count }}mo
                </span>
            </div>

        </a>
        @endforeach

    </div>
</div>