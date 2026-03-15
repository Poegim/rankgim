<div>
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('tournaments.show', $this->tournament->id) }}" wire:navigate
           class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <flux:heading size="xl">Import Games</flux:heading>
            <flux:text>{{ $this->tournament->name }}</flux:text>
        </div>
    </div>

    @if(!$isParsed)
    <div class="max-w-xl flex flex-col gap-4">
        <flux:textarea
            wire:model="rawInput"
            label="Paste games (winner,loser — one per line, date as header)"
            placeholder="2021-02-06 02:00&#10;Flash,Bonyth&#10;Jaedong,Fantasy&#10;&#10;2021-03-01&#10;Rain,TY"
            rows="10"
        />
        <div>
            <flux:button variant="primary" wire:click="parse" wire:loading.attr="disabled">
                Parse
            </flux:button>
        </div>
    </div>
    @else
    <div x-data="{ modalOpen: false }"
         x-on:open-pick-player.window="modalOpen = true; $nextTick(() => $refs.modalSearch.focus())"
         x-on:close-pick-player.window="modalOpen = false"
         class="flex flex-col gap-4">

        <div class="flex items-center justify-between">
            <div class="flex gap-4 text-sm">
                <span class="text-green-500 font-medium">✅ {{ collect($parsed)->where('status', 'ok')->count() }} ready</span>
                <span class="text-red-500 font-medium">❌ {{ collect($parsed)->where('status', 'unmatched')->count() }} unmatched</span>
                <span class="text-zinc-400">{{ collect($parsed)->where('status', 'error')->count() }} errors</span>
            </div>
            <flux:button variant="ghost" wire:click="$set('isParsed', false)">
                ← Back
            </flux:button>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="text-left px-4 py-2 text-zinc-500 font-medium">#</th>
                        <th class="text-left px-4 py-2 text-zinc-500 font-medium">Winner</th>
                        <th class="text-left px-4 py-2 text-zinc-500 font-medium">Loser</th>
                        <th class="text-left px-4 py-2 text-zinc-500 font-medium">Date</th>
                        <th class="text-left px-4 py-2 text-zinc-500 font-medium">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($parsed as $index => $row)
                    <tr class="{{
                        $row['status'] === 'ok' ? 'bg-white dark:bg-zinc-900' :
                        ($row['status'] === 'error' ? 'bg-red-50 dark:bg-red-900/10' : 'bg-yellow-50 dark:bg-yellow-900/10')
                    }}">
                        <td class="px-4 py-2">
                            <span class="text-sm font-mono text-zinc-500">#{{ $loop->iteration }}</span>
                        </td>
                        <td class="px-4 py-2">
                            @if($row['status'] === 'error')
                                <span class="text-red-500 text-xs">{{ $row['raw'] }}</span>
                            @else
                                <button type="button"
                                        wire:click="openModal({{ $index }}, 'winner')"
                                        class="flex items-center gap-2 hover:opacity-70 text-left">
                                    @if($row['winner'])
                                        <img src="{{ asset('images/country_flags/' . strtolower($row['winner']['country_code']) . '.svg') }}"
                                             class="w-5 h-3.5 rounded-sm shrink-0">
                                        <span class="font-medium text-zinc-800 dark:text-white">{{ $row['winner']['name'] }}</span>
                                    @else
                                        <span class="text-red-500 cursor-pointer" x-on:click.stop="navigator.clipboard.writeText('{{ $row['winner_name'] }}')">{{ $row['winner_name'] }} <span class="text-xs">(not found)</span></span>
                                    @endif
                                </button>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($row['status'] !== 'error')
                                <button type="button"
                                        wire:click="openModal({{ $index }}, 'loser')"
                                        class="flex items-center gap-2 hover:opacity-70 text-left">
                                    @if($row['loser'])
                                        <img src="{{ asset('images/country_flags/' . strtolower($row['loser']['country_code']) . '.svg') }}"
                                             class="w-5 h-3.5 rounded-sm shrink-0">
                                        <span class="font-medium text-zinc-800 dark:text-white">{{ $row['loser']['name'] }}</span>
                                    @else
                                        <span class="text-red-500 cursor-pointer" x-on:click.stop="navigator.clipboard.writeText('{{ $row['loser_name'] }}')">{{ $row['loser_name'] }} <span class="text-xs">(not found)</span></span>
                                    @endif
                                </button>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-xs text-zinc-400">{{ $row['date'] ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-2">
                            @if($row['status'] === 'ok')
                                <span class="text-green-500 text-xs font-medium">✅ Ready</span>
                            @elseif($row['status'] === 'error')
                                <span class="text-red-500 text-xs font-medium">❌ Error</span>
                            @else
                                <span class="text-yellow-500 text-xs font-medium">⚠️ Unmatched</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <flux:button size="sm" variant="ghost" wire:click="removeRow({{ $index }})">
                                Remove
                            </flux:button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(collect($parsed)->where('status', 'ok')->count() > 0)
        <div class="flex gap-3">
            <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled">
                Import {{ collect($parsed)->where('status', 'ok')->count() }} games
            </flux:button>
        </div>
        @endif

        {{-- Alpine Modal --}}
        <div x-show="modalOpen"
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             x-on:click.self="modalOpen = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-800 dark:text-white">
                        @if($editingSide === 'winner') Pick Winner
                        @elseif($editingSide === 'loser') Pick Loser
                        @else Pick Player
                        @endif
                    </h2>
                    <button type="button" x-on:click="modalOpen = false" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <input
                    type="text"
                    x-ref="modalSearch"
                    wire:model.live="modalSearch"
                    placeholder="Search player..."
                    autocomplete="off"
                    class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-800 text-zinc-800 dark:text-white outline-none focus:border-indigo-500"
                />

                @error('modalSearch')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror

                <div class="flex flex-col gap-1 max-h-72 overflow-y-auto">
                    @foreach($this->modalResults() as $player)
                    <button type="button"
                            wire:click="selectPlayer({{ $player->id }})"
                            class="w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                 class="w-5 h-3.5 rounded-sm shrink-0">
                            <span class="text-sm font-medium text-zinc-800 dark:text-white">{{ $player->name }}</span>
                        </div>
                        @if($player->aliases->isNotEmpty())
                        <div class="pl-7">
                            <span class="text-xs text-zinc-400">aka: {{ $player->aliases->pluck('name')->join(', ') }}</span>
                        </div>
                        @endif
                    </button>
                    @endforeach
                </div>

                @if(strlen($modalSearch) < 2)
                <p class="text-xs text-zinc-400">Type at least 2 characters to search</p>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>