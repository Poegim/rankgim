<div x-data="{ open: false }" class="relative flex items-center">
    {{-- Trigger --}}
    <button
        @mouseenter="open = true"
        @mouseleave="open = false"
        @click="open = !open"
        class="flex items-center gap-1 text-xs text-zinc-500 hover:text-zinc-300 transition-colors"
    >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        @if(array_sum($counts) > 0)
            <span>{{ array_sum($counts) }}</span>
        @endif
    </button>

    {{-- Reactions popup --}}
    <div
        x-show="open"
        @mouseenter="open = true"
        @mouseleave="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute bottom-full left-0 mb-2 z-20 flex items-center gap-1 bg-zinc-800 border border-zinc-700 rounded-full px-2 py-1.5 shadow-lg whitespace-nowrap"
        style="display: none;"
    >
        @foreach($types as $key => $reaction)
            <button
                wire:click="toggleReaction('{{ $key }}')"
                @class([
                    'flex items-center gap-1 px-2 py-1 rounded-full text-xs transition-colors',
                    'hover:bg-zinc-700 text-zinc-400 hover:text-white' => $userReaction !== $key,
                    'bg-zinc-600 text-white' => $userReaction === $key,
                ])
                @guest disabled @endguest
            >
                <span>{{ $reaction['emoji'] }}</span>
                @if(($counts[$key] ?? 0) > 0)
                    <span class="text-zinc-400">{{ $counts[$key] }}</span>
                @endif
            </button>
        @endforeach

        @guest
            <span class="text-xs text-zinc-500 px-1">Log in to react</span>
        @endguest
    </div>
</div>