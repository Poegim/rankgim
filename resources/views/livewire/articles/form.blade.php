<div class="px-4 sm:px-6 py-6 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">
            {{ $article ? 'Edit article' : 'New article' }}
        </h1>
        @if($article)
            <p class="text-sm text-zinc-500 mt-1">
                Editing <span class="font-mono text-zinc-400">{{ $article->slug }}</span>
            </p>
        @endif
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Type --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">
                Type
            </label>
            <div class="flex gap-2">
                <label class="flex-1">
                    <input type="radio" wire:model="type" value="news" class="sr-only peer">
                    <div class="cursor-pointer text-center px-4 py-2 rounded-lg border border-zinc-700/60 bg-zinc-800/40 text-sm text-zinc-400
                                peer-checked:border-zinc-500 peer-checked:bg-zinc-700/40 peer-checked:text-white transition-colors">
                        📰 News
                    </div>
                </label>
                <label class="flex-1">
                    <input type="radio" wire:model="type" value="update" class="sr-only peer">
                    <div class="cursor-pointer text-center px-4 py-2 rounded-lg border border-zinc-700/60 bg-zinc-800/40 text-sm text-zinc-400
                                peer-checked:border-zinc-500 peer-checked:bg-zinc-700/40 peer-checked:text-white transition-colors">
                        🔄 Update
                    </div>
                </label>
            </div>
            @error('type') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Title --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">
                Title
            </label>
            <input type="text"
                   wire:model.live.debounce.500ms="title"
                   class="w-full px-3 py-2 rounded-lg border border-zinc-700/60 bg-zinc-900 text-white text-sm
                          focus:outline-none focus:border-zinc-500 transition-colors">
            @error('title') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">
                Slug
                <span class="font-normal text-zinc-600 normal-case tracking-normal">(URL: /news/{slug})</span>
            </label>
            <input type="text"
                   wire:model="slug"
                   class="w-full px-3 py-2 rounded-lg border border-zinc-700/60 bg-zinc-900 text-zinc-300 text-sm font-mono
                          focus:outline-none focus:border-zinc-500 transition-colors">
            @error('slug') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Published at --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">
                Published at
                <span class="font-normal text-zinc-600 normal-case tracking-normal">(blank = draft, hidden from public)</span>
            </label>
            <input type="datetime-local"
                   wire:model="publishedAt"
                   class="px-3 py-2 rounded-lg border border-zinc-700/60 bg-zinc-900 text-zinc-300 text-sm
                          focus:outline-none focus:border-zinc-500 transition-colors">
            @error('publishedAt') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Body (markdown) --}}
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">
                Body
                <span class="font-normal text-zinc-600 normal-case tracking-normal">(markdown — supports # headings, **bold**, [links](url), images, raw HTML)</span>
            </label>
            <textarea wire:model="body"
                      rows="20"
                      class="w-full px-3 py-2 rounded-lg border border-zinc-700/60 bg-zinc-900 text-zinc-200 text-sm font-mono
                             focus:outline-none focus:border-zinc-500 transition-colors resize-y"
                      placeholder="# Section heading

Lorem ipsum **bold text** and [a link](https://example.com)."></textarea>
            @error('body') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-4 border-t border-zinc-700/40">
            <a href="{{ route('articles.index') }}"
               wire:navigate
               class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                ← Cancel
            </a>
            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-amber-500/20 border border-amber-500/30 text-amber-300 text-sm font-semibold
                           hover:bg-amber-500/30 transition-colors">
                {{ $article ? 'Save changes' : 'Create article' }}
            </button>
        </div>
    </form>
</div>