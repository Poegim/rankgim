<div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🎮 Games by country <span
            class="text-xs">(all-time)</span></p>
    <flux:table>
        <flux:table.columns>
            <flux:table.column class="w-8">#</flux:table.column>
            <flux:table.column>Country</flux:table.column>
            <flux:table.column>Games</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($this->gamesAllTimeByCountry as $index => $row)
            <flux:table.row :key="'alltime-'.$row->country_code" class="[&>td]:py-2">
                <flux:table.cell>
            <span class="text-zinc-400 font-mono text-sm">
                {{ $this->gamesAllTimeByCountry->firstItem() + $index }}
            </span>
        </flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}"
                            class="w-7 h-5 rounded-sm">
                        <span
                            class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">{{ $row->country }}</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="font-bold text-indigo-500">{{ number_format($row->games_count) }}</span>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{  $this->gamesAllTimeByCountry->links(data: ['scrollTo' => false]) }}

</div>
