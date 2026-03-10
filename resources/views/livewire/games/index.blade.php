@use('Illuminate\Support\Str')
<div>
    @php
        $raceColors = [
            'Terran'  => 'text-blue-500',
            'Zerg'    => 'text-purple-500',
            'Protoss' => 'text-yellow-500',
            'Random'  => 'text-orange-400',
            'Unknown' => 'text-zinc-400',
        ];
        $raceLabels = ['Terran' => 'T', 'Zerg' => 'Z', 'Protoss' => 'P', 'Random' => 'R', 'Unknown' => '?'];
    @endphp

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">All Games</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $this->games->total() }} games</p>
    </div>

    <div class="flex gap-3 mb-4">
        <flux:input type="date" wire:model.live="dateFrom" label="From" />
        <flux:input type="date" wire:model.live="dateTo" label="To" />
        <flux:input type="text" wire:model.live.debounce.300ms="filterTournament" label="Tournament" placeholder="Search..." />
    </div>


    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <flux:table :paginate="$this->games">
            <flux:table.columns>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Tournament</flux:table.column>
                <flux:table.column>Winner</flux:table.column>
                <flux:table.column>Loser</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->games as $game)
                <flux:table.row :key="$game->id" class="[&>td]:py-2">
                    <flux:table.cell>
                        {{ \Carbon\Carbon::parse($game->date_time)->format('Y-m-d') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($game->tournament)
                            <a href="{{ route('tournaments.show', $game->tournament->id) }}"
                               class="hover:underline text-zinc-500 dark:text-zinc-400 text-sm">
                                {{ $game->tournament->name }}
                            </a>
                        @else
                            <span class="text-zinc-400 text-sm">—</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/country_flags/' . strtolower($game->winner->country_code) . '.svg') }}"
                                 class="w-5 h-3.5 rounded-sm shrink-0">
                            <a href="{{ route('players.show', ['id' => $game->winner->id, 'slug' => Str::slug($game->winner->name)]) }}"
                               class="hover:underline font-medium text-green-500">
                                {{ $game->winner->name }}
                            </a>
                            <span class="text-xs font-bold {{ $raceColors[$game->winner->race] ?? 'text-zinc-400' }}">
                                {{ $raceLabels[$game->winner->race] ?? '?' }}
                            </span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/country_flags/' . strtolower($game->loser->country_code) . '.svg') }}"
                                 class="w-5 h-3.5 rounded-sm shrink-0">
                            <a href="{{ route('players.show', ['id' => $game->loser->id, 'slug' => Str::slug($game->loser->name)]) }}"
                               class="hover:underline text-zinc-500 dark:text-zinc-400">
                                {{ $game->loser->name }}
                            </a>
                            <span class="text-xs font-bold {{ $raceColors[$game->loser->race] ?? 'text-zinc-400' }}">
                                {{ $raceLabels[$game->loser->race] ?? '?' }}
                            </span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>