<div class="mt-4 sm:mt-0">
    @if($this->articles->isNotEmpty())
        {{-- Section heading OUTSIDE the card, mirroring TopPlayers convention --}}
        <div class="flex items-center justify-between mb-3 px-1">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em]
                      text-oxblood dark:text-zinc-500">
                📰 News &amp; updates
            </p>
            <a href="{{ route('articles.index') }}"
               class="text-xs transition-colors
                      text-travertine-600 hover:text-oxblood
                      dark:text-zinc-400 dark:hover:text-zinc-200"
               wire:navigate>
                View all →
            </a>
        </div>

        {{-- Articles list card — minimal: bordered container, no row tints --}}
        <div class="rounded-lg overflow-hidden
                    border border-travertine-300 dark:border-zinc-700/60
                    bg-travertine-50 dark:bg-zinc-800/40">
            @foreach($this->articles as $article)
                <a href="{{ route('articles.show', $article->slug) }}"
                   wire:navigate
                   class="flex items-center gap-3 px-4 py-3 transition-colors group
                          border-b border-travertine-350 last:border-b-0
                          dark:border-zinc-700/40
                          hover:bg-oxblood/5 dark:hover:bg-white/5">
                    {{-- Type badge — uses race CSS vars which auto-adjust per theme. --}}
                    {{-- 18% tint reads correctly on both cream (vars retuned darker) --}}
                    {{-- and on near-black (original lighter race vars).              --}}
                    @if($article->type === \App\Models\Article::TYPE_UPDATE)
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shrink-0"
                              style="background: color-mix(in srgb, var(--color-race-protoss) 18%, transparent); color: var(--color-race-protoss);">
                            Update
                        </span>
                    @else
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shrink-0"
                              style="background: color-mix(in srgb, var(--color-race-terran) 18%, transparent); color: var(--color-race-terran);">
                            News
                        </span>
                    @endif

                    {{-- Title --}}
                    <span class="text-sm transition-colors flex-1 min-w-0 truncate
                                 text-travertine-800 group-hover:text-oxblood
                                 dark:text-zinc-200 dark:group-hover:text-white">
                        {{ $article->title }}
                    </span>

                    {{-- Date --}}
                    <span class="text-xs shrink-0 tabular-nums
                                 text-travertine-500 dark:text-zinc-500">
                        {{ $article->published_at->format('Y M j - H:i') }}
                    </span>

                    {{-- Comment count --}}
                    @if($article->commentCount() > 0)
                        <span class="text-xs shrink-0 inline-flex items-center gap-1
                                     text-travertine-500 dark:text-zinc-500">
                            💬 {{ $article->comments_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <div class="flex items-center justify-between mb-3 px-1">
            <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em]
                      text-oxblood dark:text-zinc-500">
                📰 News &amp; updates
            </p>
        </div>
        <p class="text-sm italic
                  text-travertine-500 dark:text-zinc-500">
            No news or updates yet. Check back here after the next season recalc!
        </p>
    @endif
</div>