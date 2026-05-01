<div>
    @if($this->articles->isNotEmpty())
        {{-- Section heading OUTSIDE the card, mirroring TopPlayers convention --}}
        <div class="flex items-center justify-between mb-3 px-1">
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                📰 News & updates
            </p>
            <a href="{{ route('articles.index') }}"
               class="text-xs text-zinc-400 hover:text-zinc-200"
               wire:navigate>
                View all →
            </a>
        </div>

        {{-- Articles list card --}}
        <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden divide-y divide-zinc-700/40">
            @foreach($this->articles as $article)
                <a href="{{ route('articles.show', $article->slug) }}"
                   wire:navigate
                   class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                    {{-- Type badge --}}
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
                    <span class="text-sm text-zinc-200 group-hover:text-white transition-colors flex-1 min-w-0 truncate">
                        {{ $article->title }}
                    </span>

                    {{-- Date --}}
                    <span class="text-xs text-zinc-500 shrink-0 tabular-nums">
                        {{ $article->published_at->format('Y M j - H:i') }}
                    </span>

                    {{-- New: comment count --}}
                    @if($article->commentCount() > 0)
                        <span class="text-xs text-zinc-500 shrink-0 inline-flex items-center gap-1">
                            💬 {{ $article->comments_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    @else
    <div class="flex items-center justify-between mb-3 px-1">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            📰 News & updates
        </p>
    </div>
    <p class="text-sm text-zinc-500 italic">
        No news or updates yet. Check back here after the next season recalc!
    </p>
    @endif
</div>