@use('Illuminate\Support\Str')

<div>
    <flux:table :paginate="$this->games">
        <flux:table.columns>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Tournament</flux:table.column>
            <flux:table.column>Opponent</flux:table.column>
            <flux:table.column>Result</flux:table.column>
            <flux:table.column>Rating</flux:table.column>
            <flux:table.column>Change</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($this->games as $entry)
            <flux:table.row :key="$entry->id" class="[&>td]:py-2">
                <flux:table.cell>{{ \Carbon\Carbon::parse($entry->played_at)->format('Y-m-d') }}</flux:table.cell>
                <flux:table.cell>
                    @if($entry->game->tournament)
                        <a href="{{ route('tournaments.show', $entry->game->tournament->id) }}"
                           class="hover:underline text-zinc-500 dark:text-zinc-400 text-sm">
                            {{ $entry->game->tournament->name }}
                        </a>
                    @else
                        <span class="text-zinc-400 text-sm">—</span>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    @php
                        $opponent = $entry->result === 'win' ? $entry->game->loser : $entry->game->winner;
                    @endphp
                    @if($opponent)
                        <a href="{{ route('players.show', ['id' => $opponent->id, 'slug' => Str::slug($opponent->name)]) }}"
                           class="hover:underline">
                            {{ $opponent->name }}
                        </a>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    @if($entry->result === 'win')
                        <flux:badge color="green">W</flux:badge>
                    @else
                        <flux:badge color="red">L</flux:badge>
                    @endif
                </flux:table.cell>
                <flux:table.cell>{{ $entry->rating_after }}</flux:table.cell>
                <flux:table.cell>
                    <span class="{{ $entry->rating_change >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $entry->rating_change > 0 ? '+' : '' }}{{ $entry->rating_change }}
                    </span>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>