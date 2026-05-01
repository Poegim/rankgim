<div class="px-4 sm:px-6 py-6 max-w-5xl mx-auto">

    {{-- Page header --}}
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">News & updates</h1>
            <p class="text-sm text-zinc-500 mt-1">
                Latest changes to the ranking, tournaments, and the site.
            </p>
        </div>

        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('articles.create') }}"
                   wire:navigate
                   class="shrink-0 px-3 py-1.5 rounded-lg bg-amber-500/20 border border-amber-500/30 text-amber-300 text-sm font-semibold
                          hover:bg-amber-500/30 transition-colors">
                    + New article
                </a>
            @endif
        @endauth
    </div>

    @if($this->articles->isEmpty())
        <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-8 text-center">
            <p class="text-zinc-400 text-sm">No articles yet.</p>
        </div>
    @else
        <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden divide-y divide-zinc-700/40">
            @foreach($this->articles as $article)
                <div class="flex items-center gap-3 px-4 py-3 group">
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
                    <a href="{{ route('articles.show', $article->slug) }}"
                       wire:navigate
                       class="text-sm text-zinc-200 hover:text-white transition-colors flex-1 min-w-0 truncate">
                        {{ $article->title }}
                    </a>

                    {{-- Date --}}
                    <span class="text-xs text-zinc-500 shrink-0 tabular-nums hidden sm:inline">
                        {{ $article->published_at?->format('M j, Y') ?? 'Draft' }}
                    </span>

                    {{-- Admin-only delete actions --}}
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('articles.edit', $article->slug) }}"
                               wire:navigate
                               class="text-xs px-2 py-1 rounded text-zinc-500 hover:text-zinc-200 transition-colors shrink-0">
                                Edit
                            </a>

                            @if($confirmingDeleteId === $article->id)
                                <div class="flex items-center gap-1 shrink-0">
                                    <button wire:click="delete({{ $article->id }})"
                                            class="text-xs font-semibold px-2 py-1 rounded bg-red-500/20 text-red-300 hover:bg-red-500/30 transition-colors">
                                        Confirm
                                    </button>
                                    <button wire:click="cancelDelete"
                                            class="text-xs px-2 py-1 rounded text-zinc-400 hover:text-zinc-200 transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            @else
                                <button wire:click="confirmDelete({{ $article->id }})"
                                        class="text-xs px-2 py-1 rounded text-zinc-500 hover:text-red-400 transition-colors shrink-0">
                                    Delete
                                </button>
                            @endif
                        @endif
                    @endauth
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $this->articles->links() }}
        </div>
    @endif
</div>