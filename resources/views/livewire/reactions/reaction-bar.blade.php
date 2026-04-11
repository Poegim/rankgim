<div class="flex items-center gap-2">
    @foreach($types as $key => $reaction)
        <button
            wire:click="toggleReaction('{{ $key }}')"
            @class([
                'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm transition-colors',
                'bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-400' => $userReaction !== $key,
                'bg-zinc-800 dark:bg-zinc-100 text-white dark:text-zinc-900' => $userReaction === $key,
            ])
            @guest disabled @endguest
        >
            <span>{{ $reaction['emoji'] }}</span>
            @if(($counts[$key] ?? 0) > 0)
                <span>{{ $counts[$key] }}</span>
            @endif
        </button>
    @endforeach

    @guest
        <span class="text-xs text-zinc-400 dark:text-zinc-500">
            Log in to react
        </span>
    @endguest
</div>