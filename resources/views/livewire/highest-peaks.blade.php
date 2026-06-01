@use('Illuminate\Support\Str')
<div class="rounded-xl border p-5
    border-travertine-300 bg-travertine-50
    dark:border-zinc-700 dark:bg-transparent">

    <div class="flex items-center justify-between mb-4">
        <h2 class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
            🔝 Highest peaks
        </h2>

        {{-- Region filter --}}
        <div class="flex items-center gap-1">
            <button wire:click="$set('region', '')"
                class="px-2 py-1 text-xs rounded-md transition-colors
                    {{ $region === ''
                        ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                        : 'text-travertine-600 hover:text-travertine-900 hover:bg-travertine-200 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700' }}">
                All
            </button>
            @foreach(['Europe' => 'EU', 'North America' => 'NA', 'South America' => 'SA', 'Asia' => 'AS'] as $r => $label)
                <button wire:click="$set('region', '{{ $r }}')"
                    class="px-2 py-1 text-xs rounded-md transition-colors
                        {{ $region === $r
                            ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                            : 'text-travertine-600 hover:text-travertine-900 hover:bg-travertine-200 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="divide-y divide-travertine-350 dark:divide-zinc-700">
        @foreach($this->peaks as $index => $row)
        @php
            $raceKey = match($row->player->race) {
                'Terran'  => 'terran',
                'Zerg'    => 'zerg',
                'Protoss' => 'protoss',
                'Random'  => 'random',
                default   => 'unknown',
            };
        @endphp
        <a href="{{ route('players.show', ['id' => $row->player->id, 'slug' => Str::slug($row->player->name)]) }}"
           class="flex items-center gap-3 py-2.5 -mx-2 px-2 rounded-lg transition-colors group
               hover:bg-oxblood/5 dark:hover:bg-zinc-800/50">
            <span class="font-mono text-sm w-6 text-right text-travertine-400 dark:text-zinc-400">
                {{ $index + 1 }}
            </span>
            <img src="{{ asset('images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                 class="w-6 h-4 rounded-sm shrink-0">
            <span class="font-semibold flex-1 group-hover:underline
                text-travertine-900 dark:text-white">
                {{ $row->player->name }}
            </span>
            <span class="text-xs" style="color: var(--color-race-{{ $raceKey }})">
                {{ $row->player->race }}
            </span>
            <span class="font-mono text-lg font-bold text-amber-700 dark:text-amber-400">
                {{ $row->peak_rating }}
            </span>
        </a>
        @endforeach
    </div>
</div>