<div>
    <flux:table :paginate="$this->rankings">
        <flux:table.columns>
            <flux:table.column class="w-8">#</flux:table.column>
            <flux:table.column>Player</flux:table.column>
            <flux:table.column>Race</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'rating'"
                :direction="$sortDirection"
                wire:click="sort('rating')"
            >Rating</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'wins'"
                :direction="$sortDirection"
                wire:click="sort('wins')"
            >W</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'losses'"
                :direction="$sortDirection"
                wire:click="sort('losses')"
            >L</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'games_played'"
                :direction="$sortDirection"
                wire:click="sort('games_played')"
            >Games</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($this->rankings as $index => $row)
            <flux:table.row :key="$row->id" class="[&>td]:py-2">
                <flux:table.cell>
                    <span class="text-zinc-400 font-mono text-sm">{{ $this->rankings->firstItem() + $index }}</span>
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <img
                            src="{{ asset('storage/images/country_flags/' . strtolower($row->player->country_code) . '.svg') }}"
                            alt="{{ $row->player->country }}"
                            class="w-6 h-4 rounded-sm"
                            title="{{ $row->player->country }}"
                        >
                        <span class="font-medium text-zinc-800 dark:text-white">{{ $row->player->name }}</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-zinc-500 dark:text-zinc-400">{{ $row->player->race }}</span>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="font-bold text-zinc-800 dark:text-white">{{ $row->rating }}</span>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="font-medium text-green-500">{{ $row->wins }}</span>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="font-medium text-red-500">{{ $row->losses }}</span>
                </flux:table.cell>
                <flux:table.cell>
                    <span class="text-zinc-500 dark:text-zinc-400">{{ $row->games_played }}</span>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>