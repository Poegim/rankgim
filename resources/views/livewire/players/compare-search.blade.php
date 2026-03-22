<div class="max-w-xl mx-auto space-y-6">

    {{-- Player 1 --}}
    <div>
        <label class="block text-sm font-medium text-zinc-400 mb-1">Player 1</label>
        <div class="relative">
            <input
                type="text"
                wire:model.live="search1"
                wire:focus="$set('open1', true)"
                wire:click.away="$set('open1', false)"
                placeholder="Search player..."
                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500"
            />
            @if($player1Id)
                <button wire:click="clearPlayer1" class="absolute right-3 top-2.5 text-zinc-400 hover:text-white">✕</button>
            @endif
            @if($open1 && strlen($search1) >= 2)
                <div class="absolute z-50 mt-1 w-full bg-zinc-800 border border-zinc-700 rounded-lg shadow-xl overflow-hidden max-h-64 overflow-y-auto">
                    @forelse($this->results1 as $player)
                        <button
                            wire:click="selectPlayer1({{ $player->id }}, '{{ addslashes($player->name) }}')"
                            class="w-full text-left px-4 py-2 hover:bg-zinc-700 text-white text-sm flex items-center gap-2"
                        >
                            @if($player->country_code && $player->country_code !== 'XX')
                                <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                     class="w-7 h-5 rounded-sm shrink-0">
                            @endif
                            <span>{{ $player->name }}</span>
                            <span class="ml-auto text-xs text-zinc-500">{{ $player->race }}</span>
                        </button>
                    @empty
                        <div class="px-4 py-2 text-zinc-500 text-sm">No players found</div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    {{-- VS divider --}}
    <div class="flex items-center gap-3">
        <div class="flex-1 h-px bg-zinc-700"></div>
        <span class="text-zinc-500 font-bold text-sm tracking-widest">VS</span>
        <div class="flex-1 h-px bg-zinc-700"></div>
    </div>

    {{-- Player 2 --}}
    <div>
        <label class="block text-sm font-medium text-zinc-400 mb-1">Player 2</label>
        <div class="relative">
            <input
                type="text"
                wire:model.live="search2"
                wire:focus="$set('open2', true)"
                wire:click.away="$set('open2', false)"
                placeholder="Search player..."
                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500"
            />
            @if($player2Id)
                <button wire:click="clearPlayer2" class="absolute right-3 top-2.5 text-zinc-400 hover:text-white">✕</button>
            @endif
            @if($open2 && strlen($search2) >= 2)
                <div class="absolute z-50 mt-1 w-full bg-zinc-800 border border-zinc-700 rounded-lg shadow-xl overflow-hidden max-h-64 overflow-y-auto">
                    @forelse($this->results2 as $player)
                        <button
                            wire:click="selectPlayer2({{ $player->id }}, '{{ addslashes($player->name) }}')"
                            class="w-full text-left px-4 py-2 hover:bg-zinc-700 text-white text-sm flex items-center gap-2"
                        >
                            @if($player->country_code && $player->country_code !== 'XX')
                                <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                     class="w-7 h-5 rounded-sm shrink-0">
                            @endif
                            <span>{{ $player->name }}</span>
                            <span class="ml-auto text-xs text-zinc-500">{{ $player->race }}</span>
                        </button>
                    @empty
                        <div class="px-4 py-2 text-zinc-500 text-sm">No players found</div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    {{-- Compare button --}}
    <button
        wire:click="compare"
        @disabled(!$player1Id || !$player2Id || $player1Id === $player2Id)
        class="w-full py-3 rounded-lg font-semibold text-white transition
            {{ ($player1Id && $player2Id && $player1Id !== $player2Id)
                ? 'bg-blue-600 hover:bg-blue-500 cursor-pointer'
                : 'bg-zinc-700 opacity-50 cursor-not-allowed' }}"
    >
        Compare Players
    </button>

    @if($player1Id && $player2Id && $player1Id === $player2Id)
        <p class="text-center text-red-400 text-sm">Select two different players</p>
    @endif

</div>