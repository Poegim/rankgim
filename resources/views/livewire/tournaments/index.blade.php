<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">Tournaments</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $this->tournaments->total() }} tournaments</p>
    </div>

    <div class="mb-4">
        <flux:input type="text" wire:model.live.debounce.300ms="search" placeholder="Search tournament..." />
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <flux:table :paginate="$this->tournaments">
            <flux:table.columns>
                <flux:table.column>Tournament</flux:table.column>
                <flux:table.column>Games</flux:table.column>
                <flux:table.column>First game</flux:table.column>
                <flux:table.column>Last game</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->tournaments as $tournament)
                <flux:table.row :key="$tournament->id" class="[&>td]:py-2">
                    <flux:table.cell>
                        <a href="{{ route('tournaments.show', $tournament->id) }}"
                           class="hover:underline font-medium text-zinc-800 dark:text-white">
                            {{ $tournament->name }}
                        </a>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-zinc-500 dark:text-zinc-400">{{ $tournament->games_count }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-xs text-zinc-400">{{ $tournament->first_game ? \Carbon\Carbon::parse($tournament->first_game)->format('Y-m-d') : '—' }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-xs text-zinc-400">{{ $tournament->last_game ? \Carbon\Carbon::parse($tournament->last_game)->format('Y-m-d') : '—' }}</span>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>