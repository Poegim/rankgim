@use('Illuminate\Support\Str')
<div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
    @php
        $raceColors = [
            'Terran'  => 'text-blue-400',
            'Zerg'    => 'text-purple-400',
            'Protoss' => 'text-yellow-400',
            'Random'  => 'text-orange-400',
            'Unknown' => 'text-zinc-400',
        ];
    @endphp

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400">🔝 Highest peaks</h2>
        <div class="flex items-center gap-1">
            <button wire:click="$set('region', '')"
                class="px-2 py-1 text-xs rounded-md transition-colors {{ $region === '' ? 'bg-indigo-500 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700' }}">All</button>
            @foreach(['Europe' => 'EU', 'North America' => 'NA', 'South America' => 'SA', 'Asia' => 'AS'] as $r => $label)
                <button wire:click="$set('region', '{{ $r }}')"
                    class="px-2 py-1 text-xs rounded-md transition-colors {{ $region === $r ? 'bg-indigo-500 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
        @foreach($this->peaks as $index => $row)
        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
           class="flex items-center gap-3 py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-2 px-2 rounded-lg transition-colors group">
            <span class="font-mono text-sm text-zinc-400 w-6 text-right">{{ $index + 1 }}</span>
            <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}" class="w-6 h-4 rounded-sm">
            <span class="font-semibold text-zinc-800 dark:text-white group-hover:underline flex-1">{{ $row->player->name }}</span>
            <span class="text-xs {{ $raceColors[$row->player->race] ?? 'text-zinc-400' }}">{{ $row->player->race }}</span>
            <span class="font-mono text-lg font-bold text-yellow-400">{{ $row->peak_rating }}</span>
        </a>
        @endforeach
    </div>
</div>