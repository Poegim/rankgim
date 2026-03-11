@use('Illuminate\Support\Str')
<div class="relative px-2 mb-2" x-data="{
    open: false,
    updatePosition() {
        const rect = this.$refs.input.getBoundingClientRect();
        this.$refs.dropdown.style.top = (rect.bottom + 4) + 'px';
        this.$refs.dropdown.style.left = rect.left + 'px';
    }
}">
    <div x-ref="input">
        <flux:input
            wire:model.live.debounce.300ms="query"
            autocomplete="off"
            placeholder="Search player..."
            icon="magnifying-glass"
            size="sm"
            x-on:focus="open = true; updatePosition()"
            x-on:click.outside="open = false"
        />
    </div>

    <div
        x-ref="dropdown"
        x-show="open"
        style="width: 300px; position: fixed; display: none;"
        class="z-50 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-lg overflow-hidden"
    >
        @if(strlen($query) >= 2)
            @forelse($this->results as $player)
                <a 
                href="{{ route('players.show', ['id' => $player->id, 'slug' => Str::slug($player->name)]) }}"
                wire:navigate
                class="flex items-center justify-between gap-2 px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800"
                @click="open = false"
                wire:click="$set('query', '')"
            >
                <div class="flex items-center gap-2 min-w-0">
                    <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
                         class="w-5 h-3 rounded-sm shrink-0">
                    <span class="text-sm font-medium text-zinc-800 dark:text-white truncate">{{ $player->name }}</span>
                    <span class="text-xs shrink-0 {{ $player->race === 'Terran' ? 'text-blue-500' : ($player->race === 'Zerg' ? 'text-purple-500' : 'text-yellow-500') }}">
                        {{ $player->race }}
                    </span>
                </div>
                @if($player->rating)
                <span class="text-xs font-mono text-zinc-400 shrink-0">{{ $player->rating->rating }}</span>
                @endif
            </a>
            @empty
            <div class="px-3 py-2 text-sm text-zinc-400">No players found</div>
            @endforelse
        @endif
    </div>
</div>