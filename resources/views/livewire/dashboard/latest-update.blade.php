<div>
    @if($this->articles->isNotEmpty())
        {{-- Section heading --}}
        <div class="flex items-center justify-between mb-3 px-1">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
                📰 News & updates
            </p>
            <a href="{{ route('articles.index') }}"
               class="text-xs transition-colors text-travertine-500 hover:text-travertine-800 dark:text-zinc-400 dark:hover:text-zinc-200"
               wire:navigate>
                View all →
            </a>
        </div>

        {{-- Articles list --}}
        <div class="rounded-xl border overflow-hidden divide-y
            border-travertine-300 bg-travertine-50 divide-travertine-350
            dark:border-zinc-700/60 dark:bg-zinc-800/40 dark:divide-zinc-700/40">
            @foreach($this->articles as $article)
                <a href="{{ route('articles.show', $article->slug) }}"
                   wire:navigate
                   class="flex items-center gap-3 px-4 py-3 transition-colors group
                       hover:bg-oxblood/5 dark:hover:bg-white/5">

                    {{-- Type badge — CSS vars, brand color --}}
                    @if($article->type === \App\Models\Article::TYPE_UPDATE)
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shrink-0"
                              style="background: color-mix(in srgb, var(--color-race-protoss) 20%, transparent); color: var(--color-race-protoss);">
                            Update
                        </span>
                    @else
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shrink-0"
                              style="background: color-mix(in srgb, var(--color-race-terran) 20%, transparent); color: var(--color-race-terran);">
                            News
                        </span>
                    @endif

                    {{-- Title --}}
                    <span class="text-sm flex-1 min-w-0 truncate transition-colors
                        text-travertine-800 group-hover:text-travertine-900
                        dark:text-zinc-200 dark:group-hover:text-white">
                        {{ $article->title }}
                    </span>

                    {{-- Date --}}
                    <span class="text-xs shrink-0 tabular-nums text-travertine-400 dark:text-zinc-500">
                        {{ $article->published_at->format('M j') }}
                    </span>

                    {{-- Comment count --}}
                    @if($article->commentCount() > 0)
                        <span class="text-xs shrink-0 inline-flex items-center gap-1 text-travertine-400 dark:text-zinc-500">
                            💬 {{ $article->comments_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <div class="flex items-center justify-between mb-3 px-1">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500">
                📰 News & updates
            </p>
        </div>
        <p class="text-sm italic text-travertine-500 dark:text-zinc-500">
            No news or updates yet. Check back here after the next season recalc!
        </p>
    @endif
</div>