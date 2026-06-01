<x-layouts::app :title="$article->title">
    <div class="w-full">
        <div class="max-w-3xl mx-auto px-4 py-6">

            {{-- Article header --}}
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-3 flex-wrap">

                    {{-- Type badge — CSS vars, brand color --}}
                    @if($article->type === \App\Models\Article::TYPE_UPDATE)
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded"
                              style="background: color-mix(in srgb, var(--color-race-protoss) 20%, transparent); color: var(--color-race-protoss);">
                            Update
                        </span>
                    @else
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded"
                              style="background: color-mix(in srgb, var(--color-race-terran) 20%, transparent); color: var(--color-race-terran);">
                            News
                        </span>
                    @endif

                    <span class="text-xs text-travertine-500 dark:text-zinc-500">
                        {{ $article->published_at->format('F j, Y') }}
                    </span>

                    {{-- Admin actions --}}
                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="ml-auto flex items-center gap-2">
                                <a href="{{ route('articles.edit', $article->slug) }}"
                                   wire:navigate
                                   class="text-xs px-2 py-1 rounded transition-colors
                                       text-travertine-500 hover:text-travertine-800
                                       dark:text-zinc-500 dark:hover:text-zinc-200">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('articles.destroy', $article->slug) }}"
                                      onsubmit="return confirm('Delete this article?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs px-2 py-1 rounded transition-colors
                                                text-travertine-500 hover:text-red-700
                                                dark:text-zinc-500 dark:hover:text-red-400">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-travertine-900 dark:text-white">
                    {{ $article->title }}
                </h1>
            </div>

            {{-- Rendered markdown --}}
            <article class="article-content">
                {!! $article->body_html !!}
            </article>

            {{-- Engagement strip — reactions + comments --}}
            <div class="mt-8 pt-4 border-t flex items-center gap-4 flex-wrap
                border-travertine-300 dark:border-zinc-700/40">
                <livewire:reactions.reaction-bar :model="$article" :key="'reactions-article-' . $article->id" />

                @php $commentCount = $article->commentCount(); @endphp
                <button
                    type="button"
                    onclick="Livewire.dispatch('open-comments', { modelType: 'App\\Models\\Article', modelId: {{ $article->id }} })"
                    class="ml-auto inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                        bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100
                        dark:bg-blue-500/10 dark:border-blue-500/30 dark:text-blue-300 dark:hover:bg-blue-500/15">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>
                        <strong>{{ $commentCount }}</strong>
                        {{ \Illuminate\Support\Str::plural('comment', $commentCount) }}
                    </span>
                </button>
            </div>

            {{-- Back link --}}
            <div class="mt-10 pt-6 border-t
                border-travertine-300 dark:border-zinc-700/40">
                <a href="{{ route('articles.index') }}"
                   wire:navigate
                   class="text-sm transition-colors
                       text-travertine-500 hover:text-travertine-800
                       dark:text-zinc-400 dark:hover:text-zinc-200">
                    ← Back to all news
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>