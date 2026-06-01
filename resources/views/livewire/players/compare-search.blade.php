<div class="flex flex-col gap-6 mb-2">

    <div class="grid grid-cols-[1fr_auto_1fr_auto] items-end gap-3">

        {{-- Player 1 --}}
        <div>
            <label class="block text-sm font-medium mb-1
                text-travertine-600 dark:text-zinc-400">Player 1</label>
            <div class="relative">
                <input
                    type="text"
                    wire:model.live="search1"
                    x-on:focus="$wire.set('open1', true)"
                    @click.outside="$wire.set('open1', false)"
                    placeholder="Search player..."
                    class="w-full rounded-lg px-4 py-2 focus:outline-none
                        bg-travertine-100 border border-travertine-300 text-travertine-900 placeholder-travertine-400 focus:border-amber-600
                        dark:bg-zinc-800 dark:border-zinc-700 dark:text-white dark:placeholder-zinc-500 dark:focus:border-blue-500"
                />
                @if($player1Id)
                    <button wire:click="clearPlayer1"
                        class="absolute right-3 top-2.5
                            text-travertine-400 hover:text-travertine-800
                            dark:text-zinc-400 dark:hover:text-white">✕</button>
                @endif
                @if($open1 && strlen($search1) >= 2)
                    <div class="absolute z-50 mt-1 w-full rounded-lg shadow-xl overflow-hidden max-h-64 overflow-y-auto
                        bg-travertine-50 border border-travertine-300
                        dark:bg-zinc-800 dark:border-zinc-700">
                        @forelse($this->results1 as $player)
                        @php
                            $raceKey1 = match($player->race) {
                                'Terran' => 'terran', 'Zerg' => 'zerg', 'Protoss' => 'protoss',
                                'Random' => 'random', default => 'unknown',
                            };
                        @endphp
                            <button
                                wire:click="selectPlayer1({{ $player->id }}, '{{ addslashes($player->name) }}')"
                                class="w-full text-left px-4 py-2 text-sm flex items-center gap-2 transition-colors
                                    text-travertine-800 hover:bg-travertine-100
                                    dark:text-white dark:hover:bg-zinc-700">
                                @if($player->country_code && $player->country_code !== 'XX')
                                    <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                         class="w-7 h-5 rounded-sm shrink-0">
                                @endif
                                <span>{{ $player->name }}</span>
                                <span class="ml-auto text-xs"
                                      style="color: var(--color-race-{{ $raceKey1 }})">{{ $player->race }}</span>
                            </button>
                        @empty
                            <div class="px-4 py-2 text-sm text-travertine-400 dark:text-zinc-500">No players found</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        {{-- VS --}}
        <div class="pb-2 px-1">
            <span class="font-bold text-sm tracking-widest text-travertine-300 dark:text-zinc-500">VS</span>
        </div>

        {{-- Player 2 --}}
        <div>
            <label class="block text-sm font-medium mb-1
                text-travertine-600 dark:text-zinc-400">Player 2</label>
            <div class="relative">
                <input
                    type="text"
                    wire:model.live="search2"
                    x-on:focus="$wire.set('open2', true)"
                    @click.outside="$wire.set('open2', false)"
                    placeholder="Search player..."
                    class="w-full rounded-lg px-4 py-2 focus:outline-none
                        bg-travertine-100 border border-travertine-300 text-travertine-900 placeholder-travertine-400 focus:border-amber-600
                        dark:bg-zinc-800 dark:border-zinc-700 dark:text-white dark:placeholder-zinc-500 dark:focus:border-blue-500"
                />
                @if($player2Id)
                    <button wire:click="clearPlayer2"
                        class="absolute right-3 top-2.5
                            text-travertine-400 hover:text-travertine-800
                            dark:text-zinc-400 dark:hover:text-white">✕</button>
                @endif
                @if($open2 && strlen($search2) >= 2)
                    <div class="absolute z-50 mt-1 w-full rounded-lg shadow-xl overflow-hidden max-h-64 overflow-y-auto
                        bg-travertine-50 border border-travertine-300
                        dark:bg-zinc-800 dark:border-zinc-700">
                        @forelse($this->results2 as $player)
                        @php
                            $raceKey2 = match($player->race) {
                                'Terran' => 'terran', 'Zerg' => 'zerg', 'Protoss' => 'protoss',
                                'Random' => 'random', default => 'unknown',
                            };
                        @endphp
                            <button
                                wire:click="selectPlayer2({{ $player->id }}, '{{ addslashes($player->name) }}')"
                                class="w-full text-left px-4 py-2 text-sm flex items-center gap-2 transition-colors
                                    text-travertine-800 hover:bg-travertine-100
                                    dark:text-white dark:hover:bg-zinc-700">
                                @if($player->country_code && $player->country_code !== 'XX')
                                    <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                                         class="w-7 h-5 rounded-sm shrink-0">
                                @endif
                                <span>{{ $player->name }}</span>
                                <span class="ml-auto text-xs"
                                      style="color: var(--color-race-{{ $raceKey2 }})">{{ $player->race }}</span>
                            </button>
                        @empty
                            <div class="px-4 py-2 text-sm text-travertine-400 dark:text-zinc-500">No players found</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        {{-- Compare button --}}
        <div class="pb-0">
            <button
                wire:click="compare"
                @disabled(!$player1Id || !$player2Id || $player1Id === $player2Id)
                class="px-5 py-2 rounded-lg font-semibold transition whitespace-nowrap
                    {{ ($player1Id && $player2Id && $player1Id !== $player2Id)
                        ? 'bg-blue-600 hover:bg-blue-500 text-white cursor-pointer'
                        : 'bg-travertine-200 text-travertine-400 cursor-not-allowed opacity-50 dark:bg-zinc-700 dark:text-zinc-400' }}"
            >
                Compare
            </button>
        </div>

    </div>

    @if($player1Id && $player2Id && $player1Id === $player2Id)
        <p class="text-center text-sm -mt-4 text-red-700 dark:text-red-400">
            Select two different players
        </p>
    @endif

</div>